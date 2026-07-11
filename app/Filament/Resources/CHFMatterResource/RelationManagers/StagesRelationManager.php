<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use App\Models\Stage;
use App\Models\TemplateStage;
use App\Support\StageManager;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    protected static ?string $title = 'Etapy';

    protected static ?string $modelLabel = 'Etap';

    protected static ?string $pluralModelLabel = 'Etapy';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->templateStagesQuery())
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')->label('Status'),
                ToggleColumn::make('is_current_for_matter')
                    ->label('Aktualny etap?')
                    ->state(fn (TemplateStage $record): bool => $this->ownerRecord->current_template_stage_id === $record->getKey())
                    ->updateStateUsing(function (TemplateStage $record, bool $state): bool {
                        if ($state) {
                            StageManager::setCurrentStage($this->ownerRecord, $record);
                        } else {
                            StageManager::saveStageDetails($this->ownerRecord, $record, ['is_current' => false]);
                        }

                        $this->ownerRecord->refresh();

                        return $state;
                    }),
                TextColumn::make('stage_date')
                    ->label('Data')
                    ->state(fn (TemplateStage $record) => StageManager::stageFor($this->ownerRecord, $record)?->date)
                    ->date()
                    ->placeholder('-'),
            ])
            ->defaultGroup('parent')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('parent')
                    ->label('')
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query): Builder => $query
                        ->orderBy(
                            Stage::query()
                                ->selectRaw('MAX(date)')
                                ->join('template_stages as grouped_template_stages', 'grouped_template_stages.id', '=', 'stages.stage_id')
                                ->whereColumn('grouped_template_stages.parent', 'template_stages.parent')
                                ->where('stages.matter_id', $this->ownerRecord->getKey())
                                ->whereNotNull('stages.date'),
                            'desc',
                        )),
            ])
            ->headerActions([
                Action::make('addStage')
                    ->label('Dodaj etap')
                    ->icon('heroicon-m-plus')
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading('Dodaj etap')
                    ->disabled(fn (): bool => $this->addableTemplateStagesQuery()->doesntExist())
                    ->form($this->stageDetailsForm(includeTemplateStageSelect: true))
                    ->action(function (array $data): void {
                        $templateStage = TemplateStage::query()->find($data['stage_id']);

                        if (! $templateStage) {
                            return;
                        }

                        StageManager::saveStageDetails($this->ownerRecord, $templateStage, $data);

                        $this->ownerRecord->refresh();
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edytuj')
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton()
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (TemplateStage $record): string => $record->label)
                    ->fillForm(function (TemplateStage $record): array {
                        $stage = StageManager::stageFor($this->ownerRecord, $record);

                        return [
                            'date' => $stage?->date?->format('Y-m-d'),
                            'is_current' => $this->ownerRecord->current_template_stage_id === $record->getKey(),
                            'description' => $stage?->description,
                            'files' => $stage?->files ?? [],
                            'files_names' => $stage?->files_names ?? [],
                        ];
                    })
                    ->form($this->stageDetailsForm())
                    ->action(function (TemplateStage $record, array $data): void {
                        StageManager::saveStageDetails($this->ownerRecord, $record, $data);

                        $this->ownerRecord->refresh();
                    }),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy(
                    Stage::query()
                        ->select('date')
                        ->whereColumn('stages.stage_id', 'template_stages.id')
                        ->where('stages.matter_id', $this->ownerRecord->getKey())
                        ->whereNotNull('stages.date')
                        ->latest('date')
                        ->limit(1),
                    'desc',
                )
                ->orderByDesc('sort')
                ->orderByDesc('id'))
            ->paginated(false);
    }

    protected function templateStagesQuery(): Builder
    {
        return TemplateStage::query()
            ->whereIn('id', $this->datedStageIdsQuery());
    }

    protected function datedStageIdsQuery(): Builder
    {
        return Stage::query()
            ->select('stage_id')
            ->where('matter_id', $this->ownerRecord->getKey())
            ->whereNotNull('stage_id')
            ->whereNotNull('date');
    }

    protected function addableTemplateStagesQuery(): Builder
    {
        $category = StageManager::templateCategoryForMatter($this->ownerRecord);

        return TemplateStage::query()
            ->where('is_active', true)
            ->when(filled($category), fn (Builder $query): Builder => $query->where('category', $category))
            ->whereNotIn('id', $this->datedStageIdsQuery())
            ->orderBy('sort');
    }

    protected function addableStageParentOptions(): array
    {
        return $this->addableTemplateStagesQuery()
            ->get()
            ->pluck('parent')
            ->filter()
            ->unique()
            ->mapWithKeys(fn (string $parent): array => [$parent => $parent])
            ->all();
    }

    protected function addableTemplateStageOptions(?string $parent): array
    {
        if (blank($parent)) {
            return [];
        }

        return $this->addableTemplateStagesQuery()
            ->where('parent', $parent)
            ->pluck('label', 'id')
            ->all();
    }

    protected function defaultStageParent(): ?string
    {
        $parent = $this->ownerRecord->currentStage?->parent
            ?? $this->ownerRecord->currentStageRecord?->templateStage?->parent;

        if (blank($parent)) {
            return null;
        }

        return $this->addableTemplateStagesQuery()
            ->where('parent', $parent)
            ->exists()
                ? $parent
                : null;
    }

    protected function stageDetailsForm(bool $includeTemplateStageSelect = false): array
    {
        $fieldsetSchema = [];

        if ($includeTemplateStageSelect) {
            $fieldsetSchema[] = Select::make('stage_parent')
                ->label('Kategoria')
                ->options(fn (): array => $this->addableStageParentOptions())
                ->searchable()
                ->native(false)
                ->live()
                ->default(fn (): ?string => $this->defaultStageParent())
                ->afterStateUpdated(fn (Set $set) => $set('stage_id', null))
                ->required();

            $fieldsetSchema[] = Select::make('stage_id')
                ->label('Etap')
                ->disabled(fn (Get $get): bool => blank($get('stage_parent')))
                ->placeholder(fn (Get $get): string => blank($get('stage_parent'))
                    ? 'Wybierz najpierw kategorię'
                    : 'Wybierz etap')
                ->options(fn (Get $get): array => $this->addableTemplateStageOptions($get('stage_parent')))
                ->searchable()
                ->native(false)
                ->required();
        }

        $datePicker = DatePicker::make('date')
            ->label('Data')
            ->required();

        if ($includeTemplateStageSelect) {
            $datePicker->default(now()->toDateString());
        }

        $fieldsetSchema[] = $datePicker;
        $fieldsetSchema[] = Toggle::make('is_current')
            ->label('Aktualny etap?')
            ->inline(false)
            ->default($includeTemplateStageSelect);

        return [
            Fieldset::make()->schema($fieldsetSchema)->extraAttributes([
                'class' => 'danger',
            ]),
            RichEditor::make('description')->label('Notatka'),
            FileUpload::make('files')
                ->disk('local')
                ->storeFileNamesIn('files_names')
                ->reorderable()
                ->acceptedFileTypes(['application/pdf'])
                ->removeUploadedFileButtonPosition('right')
                ->directory(function (Get $get): string {
                    $date = filled($get('date')) ? str_replace('-', '', (string) $get('date')) : now()->format('Ymd');

                    return "stages-notes/{$date}/";
                })
                ->multiple()
                ->openable()
                ->label('Załączniki'),
        ];
    }
}
