<?php

namespace App\Services\Website;

use App\Filament\Resources\MatterResource;
use App\Models\Branch;
use App\Models\CHFPotentialMatter;
use App\Models\Contact;
use App\Models\ContactMatter;
use App\Models\Matter;
use App\Models\Stage;
use App\Models\Task;
use App\Models\User;
use App\Models\Website\Lead;
use App\Support\StageManager;
use App\Support\Website\LeadStatuses;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeadPotentialMatterService
{
    public function createForLead(
        Lead $lead,
        ?User $actor = null,
        bool $automatic = false,
        ?User $responsibleUser = null,
        ?Branch $branch = null,
    ): Matter
    {
        return DB::transaction(function () use ($lead, $actor, $automatic, $responsibleUser, $branch): Matter {
            $lead = Lead::query()
                ->lockForUpdate()
                ->findOrFail($lead->getKey());

            if ($lead->potential_matter_id && ($matter = Matter::query()->find($lead->potential_matter_id))) {
                if (! in_array($lead->status, [
                    LeadStatuses::QUALIFIED,
                    LeadStatuses::AUTOMATICALLY_QUALIFIED,
                ], true)) {
                    $lead->qualify(
                        automatic: $automatic,
                        userId: $actor?->getKey(),
                        note: 'Lead został zakwalifikowany do istniejącej potencjalnej sprawy: '.$matter->label.'.',
                    );
                }

                return $matter;
            }

            $branch = $this->branch($branch);
            $responsibleUser = $this->responsibleUser($responsibleUser, $actor, $branch);

            $matter = CHFPotentialMatter::create([
                'label' => $this->matterLabel($lead),
                'lawyer_id' => $responsibleUser->getKey(),
                'branch_id' => $branch?->getKey(),
                'branch' => $branch?->label ?? 'Głogów',
                'userinfo' => [],
                'is_matter' => false,
            ]);

            $contact = $this->findOrCreateContact($lead);

            ContactMatter::firstOrCreate([
                'matter_id' => $matter->getKey(),
                'contact_id' => $contact->getKey(),
            ], [
                'receives_notifications' => false,
            ]);

            $lead->forceFill([
                'potential_matter_id' => $matter->getKey(),
                'potential_matter_created_at' => now(),
                'potential_matter_created_by' => $actor?->getKey(),
            ])->save();

            $lead->qualify(
                automatic: $automatic,
                userId: $actor?->getKey(),
                note: ($automatic ? 'Automatycznie utworzono' : 'Utworzono').' potencjalną sprawę: '.$matter->label.'.',
            );

            $this->syncLeadFilesToPotentialMatter($lead);
            $this->createQualificationTask($lead, $matter, $responsibleUser, $actor);

            return $matter->refresh();
        });
    }

    public function rejectLead(Lead $lead, string $reason, ?User $actor = null, Carbon|string|null $changedAt = null, ?string $note = null): Lead
    {
        return DB::transaction(function () use ($lead, $reason, $actor, $changedAt, $note): Lead {
            $lead = Lead::query()
                ->lockForUpdate()
                ->findOrFail($lead->getKey());

            if (filled($lead->potential_matter_id)) {
                throw new RuntimeException('Zakwalifikowanego leada można odrzucić tylko akcją oznaczenia błędnej kwalifikacji.');
            }

            $changedAt = $this->changedAt($changedAt);

            $lead->reject(
                reason: $reason,
                changedAt: $changedAt,
                userId: $actor?->getKey(),
                note: $note,
            );

            return $lead->refresh();
        });
    }

    public function markLeadAsIncorrectlyQualified(Lead $lead, string $reason, ?User $actor = null, Carbon|string|null $changedAt = null, ?string $note = null): Lead
    {
        return DB::transaction(function () use ($lead, $reason, $actor, $changedAt, $note): Lead {
            $lead = Lead::query()
                ->lockForUpdate()
                ->findOrFail($lead->getKey());

            if (blank($lead->potential_matter_id)) {
                throw new RuntimeException('Lead nie ma powiązanej potencjalnej sprawy.');
            }

            $matter = Matter::query()->find($lead->potential_matter_id);

            if (! $matter) {
                throw new RuntimeException('Nie znaleziono powiązanej potencjalnej sprawy.');
            }

            if ($matter->is_matter) {
                throw new RuntimeException('Nie można oznaczyć jako błędnie zakwalifikowanego leada powiązanego z przyjętą sprawą.');
            }

            $changedAt = $this->changedAt($changedAt);
            $note = $this->incorrectQualificationNote($note);

            $lead->reject(
                reason: $reason,
                changedAt: $changedAt,
                userId: $actor?->getKey(),
                note: $note,
            );

            $matter->forceFill([
                'status' => 'Zamknięta',
                'is_archived' => true,
                'end' => $changedAt->toDateString(),
            ])->save();

            return $lead->refresh();
        });
    }

    public function syncLeadFilesToPotentialMatter(Lead $lead): void
    {
        if (empty($lead->files) || ! $lead->potential_matter_id) {
            return;
        }

        $matter = Matter::query()->find($lead->potential_matter_id);

        if (! $matter) {
            return;
        }

        $stage = $matter->currentStageRecord
            ?? ($matter->currentStage ? StageManager::stageFor($matter, $matter->currentStage) : null)
            ?? StageManager::ensureDefaultStage($matter);

        if (! $stage) {
            return;
        }

        $files = array_values(array_unique(array_filter([
            ...($stage->files ?? []),
            ...($lead->files ?? []),
        ])));

        $fileNames = is_array($stage->files_names) ? $stage->files_names : [];

        foreach ($files as $file) {
            $fileNames[$file] ??= basename((string) $file);
        }

        $stage->forceFill([
            'files' => $files,
            'files_names' => $fileNames,
        ])->save();
    }

    public function syncStatusFromPotentialMatter(Matter $matter): void
    {
        // Lead status intentionally tracks only marketing qualification.
        // Operational progress is read from the linked potential matter.
    }

    public function markLeadAsRetained(Matter $matter): void
    {
        // Conversion to a retained matter is visible through the linked matter.
        // The lead keeps its qualification status.
    }

    public function urlForPotentialMatter(Matter $matter): ?string
    {
        return MatterResource::getEditUrlForMatter($matter);
    }

    private function branch(?Branch $branch = null): ?Branch
    {
        return $branch
            ?? Branch::query()->defaultForNewMatters()->first()
            ?? Branch::query()->acceptingNewMatters()->ordered()->first();
    }

    private function changedAt(Carbon|string|null $changedAt): Carbon
    {
        return $changedAt instanceof Carbon
            ? $changedAt
            : (filled($changedAt) ? Carbon::parse((string) $changedAt) : now());
    }

    private function incorrectQualificationNote(?string $note): string
    {
        $note = filled($note) ? trim((string) $note) : null;

        return trim('Błędnie zakwalifikowany.'.($note ? "\n".$note : ''));
    }

    private function responsibleUser(?User $responsibleUser = null, ?User $actor = null, ?Branch $branch = null): User
    {
        $user = $responsibleUser
            ?? ($branch?->user_id ? User::query()->find($branch->user_id) : null)
            ?? ($actor?->is_lawyer ? $actor : null)
            ?? User::query()->where('email', 'bartosz.preda@preda.info')->first()
            ?? User::responsible_lawyers()->where('is_active', true)->first()
            ?? User::responsible_lawyers()->first()
            ?? User::query()->first();

        if (! $user) {
            throw new RuntimeException('Nie można utworzyć potencjalnej sprawy bez użytkownika odpowiedzialnego.');
        }

        return $user;
    }

    private function createQualificationTask(Lead $lead, Matter $matter, User $responsibleUser, ?User $actor): Task
    {
        return Task::create([
            'label' => $this->qualificationTaskLabel($lead),
            'matter_id' => $matter->getKey(),
            'priority' => 3,
            'is_private' => false,
            'created_by' => $actor?->getKey() ?? $responsibleUser->getKey(),
            'assigned_to' => $responsibleUser->getKey(),
        ]);
    }

    private function qualificationTaskLabel(Lead $lead): string
    {
        return empty($lead->files)
            ? 'Analiza umowy - kontakt z klientem'
            : 'Kwalifikacja sprawy - kontakt z klientem';
    }

    private function matterLabel(Lead $lead): string
    {
        $name = $this->sortName($lead->name) ?: 'Lead #'.$lead->getKey();
        $bank = trim((string) $lead->bank);

        return $bank === '' ? $name : "{$name} / {$bank}";
    }

    private function sortName(?string $name): ?string
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) $name));

        if ($name === '') {
            return null;
        }

        $parts = explode(' ', $name);

        if (count($parts) === 1) {
            return $parts[0];
        }

        $lastName = array_pop($parts);

        return trim($lastName.' '.implode(' ', $parts));
    }

    private function findOrCreateContact(Lead $lead): Contact
    {
        $contact = $this->findExistingContact($lead);

        if ($contact) {
            $contact->fill([
                'email' => $contact->email ?: $lead->email,
                'phone' => $contact->phone ?: $lead->phone,
                'zip_code' => $contact->zip_code ?: $lead->postal_code,
            ])->save();

            return $contact;
        }

        [$firstName, $lastName] = $this->splitName($lead->name);

        return Contact::create([
            'type' => 'osoba',
            'category' => 'Kredytobiorca',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'zip_code' => $lead->postal_code,
        ]);
    }

    private function findExistingContact(Lead $lead): ?Contact
    {
        if (filled($lead->email)) {
            $contact = Contact::query()
                ->where('email', $lead->email)
                ->first();

            if ($contact) {
                return $contact;
            }
        }

        if (filled($lead->phone)) {
            return Contact::query()
                ->where('phone', $lead->phone)
                ->first();
        }

        return null;
    }

    private function splitName(?string $name): array
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) $name));

        if ($name === '') {
            return ['Lead', null];
        }

        $parts = explode(' ', $name, 2);

        return [$parts[0], $parts[1] ?? null];
    }
}
