<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_grants_and_revokes_selected_panel_access(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->artisan('users:panel-access', [
            'email' => $user->email,
            'panels' => ['crm', 'cms'],
        ])->assertExitCode(Command::SUCCESS);

        $user->refresh();

        $this->assertTrue($user->canAccessPredaPanel('crm'));
        $this->assertTrue($user->canAccessPredaPanel('cms'));
        $this->assertFalse($user->canAccessPredaPanel('kancelaria'));

        $this->artisan('users:panel-access', [
            'email' => $user->email,
            'panels' => ['crm'],
            '--revoke' => true,
        ])->assertExitCode(Command::SUCCESS);

        $user->refresh();

        $this->assertFalse($user->canAccessPredaPanel('crm'));
        $this->assertTrue($user->canAccessPredaPanel('cms'));
        $this->assertFalse($user->canAccessPredaPanel('kancelaria'));
    }

    public function test_it_rejects_unknown_panels(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->artisan('users:panel-access', [
            'email' => $user->email,
            'panels' => ['billing'],
        ])->assertExitCode(Command::FAILURE);

        $this->assertFalse($user->canAccessPredaPanel('crm'));
        $this->assertFalse($user->canAccessPredaPanel('cms'));
        $this->assertFalse($user->canAccessPredaPanel('kancelaria'));
    }

    public function test_it_rejects_non_employee_users(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => false,
        ]);

        $this->artisan('users:panel-access', [
            'email' => $user->email,
            'panels' => ['crm'],
        ])->assertExitCode(Command::FAILURE);

        $this->assertFalse($user->canAccessPredaPanel('crm'));
    }
}
