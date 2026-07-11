<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\LeadResource as CrmLeadResource;
use App\Filament\Resources\CHFMatterResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\Roles\Pages\EditRole as EditRolePage;
use App\Filament\Resources\Roles\RoleResource as KancelariaRoleResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Website\Resources\Banks\BankResource as WebsiteBankResource;
use App\Models\User;
use App\Support\PanelAccess;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
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

    public function test_shield_generates_kancelaria_permission_keys_used_by_current_policies(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $contactPermissions = FilamentShield::getResourcePermissionsWithLabels(ContactResource::class);
        $chfMatterPermissions = FilamentShield::getResourcePermissionsWithLabels(CHFMatterResource::class);

        $this->assertArrayHasKey('view_any_contact', $contactPermissions);
        $this->assertArrayHasKey('create_contact', $contactPermissions);
        $this->assertArrayNotHasKey('ViewAny:Contact', $contactPermissions);

        $this->assertArrayHasKey('view_any_c::h::f::matter', $chfMatterPermissions);
        $this->assertArrayHasKey('create_c::h::f::matter', $chfMatterPermissions);
        $this->assertArrayNotHasKey('ViewAny:CHFMatter', $chfMatterPermissions);
    }

    public function test_shield_role_form_permission_state_matches_role_permissions(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $role = Role::firstOrCreate([
            'name' => 'pracownik',
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'view_any_contact',
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo('view_any_contact');

        $checkedPermissions = collect(FilamentShield::getResourcePermissionsWithLabels(ContactResource::class))
            ->filter(fn (string $label, string $permission): bool => $role->checkPermissionTo($permission))
            ->keys()
            ->all();

        $this->assertContains('view_any_contact', $checkedPermissions);
    }

    public function test_kancelaria_role_edit_form_groups_permissions_by_panel(): void
    {
        $admin = $this->makeSuperAdmin();
        $role = Role::firstOrCreate([
            'name' => 'pracownik',
            'guard_name' => 'web',
        ]);

        $this->actingAs($admin)
            ->get(KancelariaRoleResource::getUrl('edit', ['record' => $role], panel: 'kancelaria'))
            ->assertOk()
            ->assertSee('Uprawnienia')
            ->assertSee('Kancelaria')
            ->assertSee('CRM')
            ->assertSee('Strona www')
            ->assertSee('Zasoby');
    }

    public function test_kancelaria_role_edit_form_saves_permissions_from_panel_groups(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $admin = $this->makeSuperAdmin();
        $role = Role::firstOrCreate([
            'name' => 'panel-test',
            'guard_name' => 'web',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditRolePage::class, ['record' => $role->getRouteKey()])
            ->set('data.'.ContactResource::class, ['view_any_contact'])
            ->set('data.'.CrmLeadResource::class, ['view_any_lead'])
            ->set('data.'.WebsiteBankResource::class, ['ViewAny:Bank'])
            ->call('save')
            ->assertHasNoErrors();

        $expectedPermissions = ['view_any_contact', 'view_any_lead', 'ViewAny:Bank'];
        $actualPermissions = $role->refresh()->permissions()->pluck('name')->all();

        sort($expectedPermissions);
        sort($actualPermissions);

        $this->assertSame($expectedPermissions, $actualPermissions);
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
