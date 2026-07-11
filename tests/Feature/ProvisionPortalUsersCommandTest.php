<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ContactMatter;
use App\Models\Matter;
use App\Models\PortalUser;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionPortalUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_previews_missing_portal_users_without_creating_accounts(): void
    {
        $this->createBorrowerWithMatter('preview@example.test');

        $this->artisan('portal:provision-users')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount('portal_users', 0);
    }

    public function test_it_creates_only_eligible_missing_portal_users_when_forced(): void
    {
        $eligible = $this->createBorrowerWithMatter('eligible@example.test');
        $withoutMatter = $this->createContact('without-matter@example.test');
        $withoutEmail = $this->createBorrowerWithMatter(null);
        $otherCategory = $this->createBorrowerWithMatter('bank@example.test', 'Bank');

        PortalUser::create([
            'name' => 'Existing',
            'email' => 'existing@example.test',
            'password' => 'password',
            'is_active' => false,
            'contact_id' => $this->createBorrowerWithMatter('existing@example.test')->id,
        ]);

        $this->artisan('portal:provision-users', [
            '--force' => true,
        ])->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('portal_users', [
            'email' => 'eligible@example.test',
            'contact_id' => $eligible->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseMissing('portal_users', [
            'email' => $withoutMatter->email,
            'contact_id' => $withoutMatter->id,
        ]);
        $this->assertDatabaseMissing('portal_users', [
            'contact_id' => $withoutEmail->id,
        ]);
        $this->assertDatabaseMissing('portal_users', [
            'email' => $otherCategory->email,
            'contact_id' => $otherCategory->id,
        ]);

        $this->assertDatabaseCount('portal_users', 2);
    }

    public function test_it_can_create_active_accounts_when_requested(): void
    {
        $contact = $this->createBorrowerWithMatter('active@example.test');

        $this->artisan('portal:provision-users', [
            '--force' => true,
            '--active' => true,
        ])->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('portal_users', [
            'email' => 'active@example.test',
            'contact_id' => $contact->id,
            'is_active' => true,
        ]);
    }

    public function test_it_rejects_invalid_limits(): void
    {
        $this->artisan('portal:provision-users', [
            '--limit' => '0',
        ])->assertExitCode(Command::FAILURE);
    }

    private function createBorrowerWithMatter(?string $email, string $category = 'Kredytobiorca'): Contact
    {
        $contact = $this->createContact($email, $category);
        $lawyer = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $matter = Matter::create([
            'label' => 'Matter '.$contact->id,
            'lawyer_id' => $lawyer->id,
            'category' => 'CHF',
            'is_matter' => true,
        ]);

        ContactMatter::create([
            'matter_id' => $matter->id,
            'contact_id' => $contact->id,
            'receives_notifications' => true,
        ]);

        return $contact;
    }

    private function createContact(?string $email, string $category = 'Kredytobiorca'): Contact
    {
        return Contact::create([
            'type' => 'person',
            'category' => $category,
            'first_name' => 'Jan',
            'last_name' => 'Portalowy',
            'label' => 'Jan Portalowy',
            'sort_name' => 'Portalowy Jan',
            'email' => $email,
        ]);
    }
}
