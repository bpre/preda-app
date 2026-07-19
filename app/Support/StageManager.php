<?php

namespace App\Support;

use App\Models\Matter;
use App\Models\Stage;
use App\Models\TemplateStage;
use App\Services\Crm\PotentialMatterNextActionService;
use App\Services\Website\LeadPotentialMatterService;
use Illuminate\Support\Carbon;

class StageManager
{
    public static function templateCategoryForMatter(Matter $matter): ?string
    {
        if (($matter->category === 'CHF') && (! $matter->is_matter)) {
            return 'Potencjalna';
        }

        if (in_array($matter->category, ['CHF', 'O zapłatę'], true)) {
            return $matter->category;
        }

        return null;
    }

    public static function defaultTemplateStageForMatter(Matter $matter): ?TemplateStage
    {
        $category = self::templateCategoryForMatter($matter);

        if (! $category) {
            return null;
        }

        return TemplateStage::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->where('is_chf_default', true)
            ->orderBy('sort')
            ->first()
            ?? TemplateStage::query()
                ->where('category', $category)
                ->where('is_active', true)
                ->orderBy('sort')
                ->first();
    }

    public static function ensureDefaultStage(Matter $matter, Carbon | string | null $date = null): ?Stage
    {
        $templateStage = self::defaultTemplateStageForMatter($matter);

        if (! $templateStage) {
            return null;
        }

        return self::setCurrentStage($matter, $templateStage, $date ?? now());
    }

    public static function setCurrentStage(Matter $matter, TemplateStage | Stage | string | null $stage, Carbon | string | null $date = null): ?Stage
    {
        $templateStage = self::resolveTemplateStage($stage);

        if ($templateStage && ! $templateStage->is_active) {
            return null;
        }

        if (! $templateStage) {
            self::markStagesNotCurrent($matter);
            $matter->forceFill(self::clearedCurrentStageAttributes())->save();
            self::refreshPotentialMatterNextAction($matter->refresh());

            return null;
        }

        $stageRecord = self::stageFor($matter, $templateStage);

        if (! $stageRecord) {
            $stageRecord = new Stage([
                'matter_id' => $matter->getKey(),
                'stage_id' => $templateStage->getKey(),
            ]);

            self::syncStageTemplateData($stageRecord, $templateStage);
        }

        $shouldAuditCurrentSet = self::shouldAuditCurrentSet($matter, $templateStage, $stageRecord);

        self::markStagesNotCurrent(
            $matter,
            $stageRecord->exists ? $stageRecord->getKey() : null,
        );

        $stageRecord->is_current = true;
        $stageRecord->date = $date ? Carbon::parse($date)->toDateString() : ($stageRecord->date ?? now()->toDateString());

        if ($shouldAuditCurrentSet) {
            self::fillCurrentStageAudit($stageRecord);
        }

        $stageRecord->save();

        $matter->forceFill(self::currentStageAttributes($templateStage, $shouldAuditCurrentSet))->save();
        app(LeadPotentialMatterService::class)->syncStatusFromPotentialMatter($matter->refresh());
        self::refreshPotentialMatterNextAction($matter->refresh());

        return $stageRecord;
    }

    public static function saveStageDetails(Matter $matter, TemplateStage $templateStage, array $data): ?Stage
    {
        $stageRecord = self::stageFor($matter, $templateStage);

        if (! $templateStage->is_active && ! $stageRecord) {
            return null;
        }

        if (! $stageRecord) {
            $stageRecord = new Stage([
                'matter_id' => $matter->getKey(),
                'stage_id' => $templateStage->getKey(),
            ]);

            self::syncStageTemplateData($stageRecord, $templateStage);
        }

        foreach (['description', 'files', 'files_names'] as $field) {
            if (array_key_exists($field, $data)) {
                $stageRecord->{$field} = $data[$field];
            }
        }

        if (array_key_exists('date', $data)) {
            $stageRecord->date = filled($data['date'])
                ? Carbon::parse($data['date'])->toDateString()
                : null;
        }

        $isCurrent = (bool) ($data['is_current'] ?? false);
        $wasCurrentForMatter = self::stageIsCurrentForMatter($matter, $templateStage, $stageRecord);

        if ($isCurrent && ($templateStage->is_active || $stageRecord->is_current)) {
            self::markStagesNotCurrent(
                $matter,
                $stageRecord->exists ? $stageRecord->getKey() : null,
            );

            $stageRecord->is_current = true;
            $stageRecord->date ??= now()->toDateString();

            $shouldAuditCurrentSet = ! $wasCurrentForMatter
                || blank($matter->current_stage_set_at)
                || blank($stageRecord->current_stage_set_at);

            if ($shouldAuditCurrentSet) {
                self::fillCurrentStageAudit($stageRecord);
            }

            $matter->forceFill(self::currentStageAttributes($templateStage, $shouldAuditCurrentSet))->save();
            app(LeadPotentialMatterService::class)->syncStatusFromPotentialMatter($matter->refresh());
        } else {
            $stageRecord->is_current = false;

            if ($matter->current_template_stage_id === $templateStage->getKey()) {
                $matter->forceFill(self::clearedCurrentStageAttributes())->save();
            }
        }

        if (! self::shouldKeepStageRecord($stageRecord)) {
            if ($stageRecord->exists) {
                $stageRecord->delete();
            }

            self::refreshPotentialMatterNextAction($matter->refresh());

            return null;
        }

        $stageRecord->save();
        self::refreshPotentialMatterNextAction($matter->refresh());

        return $stageRecord;
    }

    public static function clearCurrentStage(Matter $matter, TemplateStage | Stage | string | null $stage = null): void
    {
        $stageId = null;

        if ($stage instanceof TemplateStage) {
            $stageId = $stage->getKey();
        } elseif ($stage instanceof Stage) {
            $stageId = $stage->stage_id;
        } elseif (is_string($stage) && filled($stage)) {
            $stageId = $stage;
        }

        self::markStagesNotCurrent($matter, stageId: $stageId);

        if (! $stageId || $matter->current_template_stage_id === $stageId) {
            $matter->forceFill(self::clearedCurrentStageAttributes())->save();
            app(LeadPotentialMatterService::class)->syncStatusFromPotentialMatter($matter->refresh());
        }

        self::refreshPotentialMatterNextAction($matter->refresh());
    }

    public static function stageFor(Matter $matter, TemplateStage | string $templateStage): ?Stage
    {
        $templateStageId = $templateStage instanceof TemplateStage ? $templateStage->getKey() : $templateStage;

        return Stage::query()
            ->where('matter_id', $matter->getKey())
            ->where('stage_id', $templateStageId)
            ->first();
    }

    protected static function resolveTemplateStage(TemplateStage | Stage | string | null $stage): ?TemplateStage
    {
        if ($stage instanceof TemplateStage) {
            return $stage;
        }

        if ($stage instanceof Stage) {
            return $stage->templateStage;
        }

        if (is_string($stage) && filled($stage)) {
            return TemplateStage::query()->find($stage);
        }

        return null;
    }

    protected static function syncStageTemplateData(Stage $stage, TemplateStage $templateStage): void
    {
        $stage->stage_id = $templateStage->getKey();
        $stage->label = $templateStage->label;
        $stage->parent = $templateStage->parent;
        $stage->sort = $templateStage->sort;
    }

    protected static function shouldKeepStageRecord(Stage $stage): bool
    {
        return filled($stage->date)
            || $stage->is_current
            || filled(strip_tags((string) $stage->description))
            || filled($stage->files)
            || filled($stage->files_names);
    }

    protected static function shouldAuditCurrentSet(Matter $matter, TemplateStage $templateStage, Stage $stageRecord): bool
    {
        return ! self::stageIsCurrentForMatter($matter, $templateStage, $stageRecord)
            || blank($matter->current_stage_set_at)
            || blank($stageRecord->current_stage_set_at);
    }

    protected static function stageIsCurrentForMatter(Matter $matter, TemplateStage $templateStage, Stage $stageRecord): bool
    {
        return $stageRecord->exists
            && $stageRecord->is_current
            && ((string) $matter->current_template_stage_id === (string) $templateStage->getKey());
    }

    protected static function markStagesNotCurrent(
        Matter $matter,
        int | string | null $exceptStageRecordId = null,
        string | null $stageId = null,
    ): void
    {
        Stage::query()
            ->where('matter_id', $matter->getKey())
            ->where('is_current', true)
            ->when(
                filled($exceptStageRecordId),
                fn ($query) => $query->whereKeyNot($exceptStageRecordId),
            )
            ->when(
                filled($stageId),
                fn ($query) => $query->where('stage_id', $stageId),
            )
            ->update(self::stageNotCurrentAttributes());
    }

    protected static function fillCurrentStageAudit(Stage $stage): void
    {
        $stage->current_stage_set_by = auth()->id();
        $stage->current_stage_set_at = now();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function currentStageAttributes(TemplateStage $templateStage, bool $includeAudit): array
    {
        $attributes = [
            'current_template_stage_id' => $templateStage->getKey(),
        ];

        if ($includeAudit) {
            $attributes['current_stage_set_by'] = auth()->id();
            $attributes['current_stage_set_at'] = now();
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function clearedCurrentStageAttributes(): array
    {
        return [
            'current_template_stage_id' => null,
            'current_stage_set_by' => null,
            'current_stage_set_at' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function stageNotCurrentAttributes(): array
    {
        return [
            'is_current' => false,
            'last_edited_by' => auth()->id(),
            'last_edited_at' => now(),
        ];
    }

    protected static function refreshPotentialMatterNextAction(Matter $matter): void
    {
        app(PotentialMatterNextActionService::class)->refresh($matter);
    }
}
