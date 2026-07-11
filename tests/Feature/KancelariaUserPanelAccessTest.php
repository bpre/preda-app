<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use App\Support\PanelAccess;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KancelariaUserPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_kancelaria_user_edit_form_shows_panel_access_controls(): void
    {
        $admin = $this->makeSuperAdmin();
        $managedUser = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->actingAs($admin)
            ->get(UserResource::getUrl('edit', ['record' => $managedUser], panel: 'kancelaria'))
            ->assertOk()
            ->assertSee('Dostęp do paneli')
            ->assertSee('Kancelaria')
            ->assertSee('CRM')
            ->assertSee('Strona www');
    }

    public function test_kancelaria_user_edit_form_updates_direct_panel_access(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $admin = $this->makeSuperAdmin();
        $managedUser = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
            'is_employee' => true,
        ]);

        PanelAccess::grantDirect($managedUser, ['kancelaria']);

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $managedUser->getRouteKey()])
            ->set('data.panel_access', ['crm', 'cms'])
            ->call('save')
            ->assertHasNoErrors();

        $managedUser->refresh();

        $this->assertSame(['crm', 'cms'], PanelAccess::directPanelsFor($managedUser));
        $this->assertFalse($managedUser->canAccessPredaPanel('kancelaria'));
        $this->assertTrue($managedUser->canAccessPredaPanel('crm'));
        $this->assertTrue($managedUser->canAccessPredaPanel('cms'));
    }

    public function test_regular_role_panel_permissions_do_not_grant_panel_entry(): void
    {
        PanelAccess::ensurePermissions(['crm']);

        $role = Role::firstOrCreate([
            'name' => 'marketing',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo(PanelAccess::permissionName('crm'));

        $user = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
            'is_employee' => true,
        ]);
        $user->assignRole($role);

        $this->assertTrue($user->can(PanelAccess::permissionName('crm')));
        $this->assertFalse($user->canAccessPredaPanel('crm'));
    }

    public function test_direct_panel_permissions_do_not_grant_entry_to_non_employee_user(): void
    {
        $user = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
            'is_employee' => false,
        ]);

        PanelAccess::grantDirect($user, ['crm']);

        $this->assertSame(['crm'], PanelAccess::directPanelsFor($user));
        $this->assertFalse($user->canAccessPredaPanel('crm'));
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }
}
