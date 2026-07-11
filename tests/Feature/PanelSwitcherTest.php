<?php

namespace Tests\Feature;

use App\Livewire\PanelSwitcher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_switcher_renders_available_panels_without_visible_panel_label(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $this->actingAs($this->makeSuperAdmin());

        Livewire::test(PanelSwitcher::class)
            ->assertSee('Kancelaria')
            ->assertSee('CRM')
            ->assertSee('Strona www')
            ->assertDontSee('Panel');
    }

    public function test_panel_switcher_redirects_to_selected_panel(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $this->actingAs($this->makeSuperAdmin());

        Livewire::test(PanelSwitcher::class)
            ->set('data.panel', 'crm')
            ->assertRedirect('http://crm.preda-app.test');
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }
}
