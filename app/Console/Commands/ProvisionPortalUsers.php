<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\PortalUser;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProvisionPortalUsers extends Command
{
    protected $signature = 'portal:provision-users
        {--force : Create the missing portal users. Without this option the command only previews changes.}
        {--active : Mark newly created portal users as active.}
        {--limit= : Limit how many missing portal users are previewed or created.}';

    protected $description = 'Previews or creates portal user accounts for borrower contacts linked to matters.';

    public function handle(): int
    {
        $limit = $this->normalizedLimit();

        if ($limit === false) {
            return self::FAILURE;
        }

        $contacts = $this->candidateContacts();
        $missingContacts = $this->missingPortalUsers($contacts);
        $totalMissing = $missingContacts->count();

        if ($limit !== null) {
            $missingContacts = $missingContacts->take($limit);
        }

        $this->line('Eligible borrower contacts: '.$contacts->count());
        $this->line('Missing portal accounts: '.$totalMissing);

        if ($limit !== null) {
            $this->line('Selected by --limit: '.$missingContacts->count());
        }

        if ($missingContacts->isEmpty()) {
            $this->info('No portal users to provision.');

            return self::SUCCESS;
        }

        $this->table(
            ['Contact ID', 'Name', 'E-mail'],
            $missingContacts
                ->take(20)
                ->map(fn (Contact $contact): array => [
                    $contact->id,
                    $this->portalName($contact),
                    $this->portalEmail($contact),
                ])
                ->all(),
        );

        if ($missingContacts->count() > 20) {
            $this->line('Showing first 20 records.');
        }

        if (! $this->option('force')) {
            $this->warn('Dry run only. Re-run with --force to create these portal users.');

            return self::SUCCESS;
        }

        $created = 0;

        foreach ($missingContacts as $contact) {
            PortalUser::create([
                'name' => $this->portalName($contact),
                'email' => $this->portalEmail($contact),
                'password' => Str::random(48),
                'is_active' => (bool) $this->option('active'),
                'contact_id' => $contact->id,
            ]);

            $created++;
        }

        $this->info("Created {$created} portal users.");

        if (! $this->option('active')) {
            $this->line('New accounts are inactive. Use the Ewidencja panel to activate selected accounts.');
        }

        return self::SUCCESS;
    }

    private function normalizedLimit(): int|false|null
    {
        $limit = $this->option('limit');

        if ($limit === null) {
            return null;
        }

        if (! ctype_digit((string) $limit) || (int) $limit < 1) {
            $this->error('The --limit option must be a positive integer.');

            return false;
        }

        return (int) $limit;
    }

    /**
     * @return Collection<int, Contact>
     */
    private function candidateContacts(): Collection
    {
        return Contact::query()
            ->where('category', 'Kredytobiorca')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereHas('matters', fn (Builder $query) => $query->where('is_matter', true))
            ->orderBy('sort_name')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, Contact>  $contacts
     * @return Collection<int, Contact>
     */
    private function missingPortalUsers(Collection $contacts): Collection
    {
        $existingContactIds = PortalUser::query()
            ->whereNotNull('contact_id')
            ->pluck('contact_id')
            ->map(fn (string $contactId): string => strtolower($contactId))
            ->all();

        $existingEmails = PortalUser::query()
            ->pluck('email')
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->all();

        return $contacts
            ->reject(fn (Contact $contact): bool => in_array(strtolower((string) $contact->id), $existingContactIds, true))
            ->reject(fn (Contact $contact): bool => in_array($this->portalEmail($contact), $existingEmails, true))
            ->values();
    }

    private function portalName(Contact $contact): string
    {
        return trim((string) ($contact->sort_name ?: $contact->label ?: $contact->email));
    }

    private function portalEmail(Contact $contact): string
    {
        return strtolower(trim((string) $contact->email));
    }
}
