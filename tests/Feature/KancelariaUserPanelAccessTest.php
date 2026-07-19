<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource as CrmChanceResource;
use App\Filament\Resources\CHFMatterResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\Roles\Pages\EditRole as EditRolePage;
use App\Filament\Resources\Roles\RoleResource as KancelariaRoleResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Website\Resources\Banks\BankResource as WebsiteBankResource;
use App\Models\User;
use App\Models\UserImpersonationLog;
use App\Services\UserImpersonationService;
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
            ->assertSee('Link do konsultacji')
            ->assertSee('Kancelaria')
            ->assertSee('CRM')
            ->assertSee('Strona www');
    }

    public function test_kancelaria_user_edit_form_updates_consultation_url(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $admin = $this->makeSuperAdmin();
        $managedUser = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $managedUser->getRouteKey()])
            ->set('data.consultation_url', 'https://calendar.example.test/konsultacje')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            'https://calendar.example.test/konsultacje',
            $managedUser->refresh()->consultation_url,
        );
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

    public function test_kancelaria_user_edit_form_does_not_show_legacy_client_matters_field(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $admin = $this->makeSuperAdmin();
        $managedUser = User::factory()->create([
            'phone' => '500 600 700',
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $managedUser->getRouteKey()])
            ->assertFormFieldDoesNotExist('matters');
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
            ->assertSee('Zasoby')
            ->assertSee('Narzędzia administracyjne')
            ->assertSee('Działanie jako inny użytkownik');
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
            ->set('data.'.CrmChanceResource::class, ['view_any_c::h::f::potential::matter'])
            ->set('data.'.WebsiteBankResource::class, ['ViewAny:Bank'])
            ->set('data.kancelaria_custom_permissions_tab', [UserImpersonationService::PERMISSION])
            ->call('save')
            ->assertHasNoErrors();

        $expectedPermissions = [
            'view_any_contact',
            'view_any_c::h::f::potential::matter',
            'ViewAny:Bank',
            UserImpersonationService::PERMISSION,
        ];
        $actualPermissions = $role->refresh()->permissions()->pluck('name')->all();

        sort($expectedPermissions);
        sort($actualPermissions);

        $this->assertSame($expectedPermissions, $actualPermissions);
    }

    public function test_super_admin_can_impersonate_employee_and_return_to_admin_account(): void
    {
        Filament::setCurrentPanel('kancelaria');

        $admin = $this->makeSuperAdmin();
        $employee = User::factory()->create([
            'name' => 'Diagnozowany Pracownik',
            'phone' => '500 600 701',
            'is_active' => true,
            'is_employee' => true,
        ]);

        PanelAccess::grantDirect($employee, ['crm']);

        $this->actingAs($admin);
        session()->put('password_hash_web', $this->authenticateSessionPasswordHashFor($admin));

        $service = app(UserImpersonationService::class);
        $returnUrl = UserResource::getUrl(panel: 'kancelaria');
        $response = $service->start($admin, $employee, $returnUrl);

        $this->assertStringStartsWith(
            'http://crm.preda-app.test/impersonacja/przejmij/',
            $response->getTargetUrl(),
        );
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session(UserImpersonationService::SESSION_KEY));

        $log = UserImpersonationLog::query()->firstOrFail();

        $this->assertSame($admin->getKey(), $log->impersonator_id);
        $this->assertSame($employee->getKey(), $log->impersonated_user_id);
        $this->assertNull($log->ended_at);
        $this->assertNull($log->handoff_consumed_at);
        $this->assertNotNull($log->handoff_token_hash);
        $this->assertNotNull($log->handoff_expires_at);

        $this->get($response->getTargetUrl())
            ->assertRedirect('http://crm.preda-app.test');

        $state = session(UserImpersonationService::SESSION_KEY);

        $this->assertAuthenticatedAs($employee);
        $this->assertIsArray($state);
        $this->assertSame($admin->getKey(), $state['impersonator_id']);
        $this->assertSame($employee->getKey(), $state['impersonated_user_id']);
        $this->assertSame($this->authenticateSessionPasswordHashFor($employee), session('password_hash_web'));
        $this->assertNotNull($log->refresh()->handoff_consumed_at);

        $this
            ->get('http://crm.preda-app.test/')
            ->assertOk()
            ->assertSeeInOrder(['bg-red-200', '<div class="fi-layout"'], false)
            ->assertSee('background-color: rgb(254 202 202);', false)
            ->assertSee('Tryb diagnostyczny')
            ->assertSee('Diagnozowany Pracownik')
            ->assertSee($admin->name)
            ->assertSee('wyłącz tryb diagnostyczny');

        $this
            ->post('http://crm.preda-app.test/impersonacja/zakoncz')
            ->assertRedirect($returnUrl);

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session(UserImpersonationService::SESSION_KEY));
        $this->assertSame($this->authenticateSessionPasswordHashFor($admin), session('password_hash_web'));
        $this->assertNotNull($log->refresh()->ended_at);
    }

    public function test_super_admin_can_impersonate_employee_on_current_kancelaria_domain(): void
    {
        $admin = $this->makeSuperAdmin();
        $employee = User::factory()->create([
            'name' => 'Pracownik Kancelarii',
            'phone' => '500 600 704',
            'is_active' => true,
            'is_employee' => true,
        ]);

        PanelAccess::grantDirect($employee, ['kancelaria']);

        $returnUrl = UserResource::getUrl(panel: 'kancelaria');

        $this->actingAs($admin);
        session()->put('password_hash_web', $this->authenticateSessionPasswordHashFor($admin));

        $this
            ->withHeader('referer', $returnUrl)
            ->get('http://ewidencja.preda-app.test/impersonacja/rozpocznij/'.$employee->getKey())
            ->assertRedirect('http://ewidencja.preda-app.test');

        $this->assertAuthenticatedAs($employee);
        $this->assertSame($this->authenticateSessionPasswordHashFor($employee), session('password_hash_web'));

        $state = session(UserImpersonationService::SESSION_KEY);
        $log = UserImpersonationLog::query()->firstOrFail();

        $this->assertIsArray($state);
        $this->assertSame($admin->getKey(), $state['impersonator_id']);
        $this->assertSame($employee->getKey(), $state['impersonated_user_id']);
        $this->assertSame($admin->getKey(), $log->impersonator_id);
        $this->assertSame($employee->getKey(), $log->impersonated_user_id);
        $this->assertNull($log->handoff_token_hash);
        $this->assertNull($log->handoff_consumed_at);

        $this
            ->get('http://ewidencja.preda-app.test/')
            ->assertOk()
            ->assertSeeInOrder(['bg-red-200', '<div class="fi-layout"'], false)
            ->assertSee('background-color: rgb(254 202 202);', false)
            ->assertSee('Tryb diagnostyczny')
            ->assertSee('Pracownik Kancelarii')
            ->assertSee($admin->name)
            ->assertSee('wyłącz tryb diagnostyczny');
    }

    public function test_employee_without_impersonation_permission_cannot_impersonate_another_user(): void
    {
        $actor = User::factory()->create([
            'phone' => '500 600 702',
            'is_active' => true,
            'is_employee' => true,
        ]);
        $target = User::factory()->create([
            'phone' => '500 600 703',
            'is_active' => true,
            'is_employee' => true,
        ]);

        PanelAccess::grantDirect($target, ['crm']);

        $this->actingAs($actor);

        $this->assertFalse(app(UserImpersonationService::class)->canStart($actor, $target));
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

    private function authenticateSessionPasswordHashFor(User $user): string
    {
        $passwordHash = $user->getAuthPassword();
        $guard = auth()->guard();

        if (method_exists($guard, 'hashPasswordForCookie')) {
            return $guard->hashPasswordForCookie($passwordHash);
        }

        return $passwordHash;
    }
}
