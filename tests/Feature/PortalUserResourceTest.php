<?php

namespace Tests\Feature;

use App\Filament\Resources\PortalUserResource;
use App\Models\Contact;
use App\Models\PortalUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalUserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_user_resource_is_registered_only_in_the_kancelaria_panel(): void
    {
        $this->assertTrue(Route::has('filament.kancelaria.resources.konta-portalu.index'));
        $this->assertFalse(Route::has('filament.crm.resources.konta-portalu.index'));
        $this->assertFalse(Route::has('filament.cms.resources.konta-portalu.index'));
        $this->assertFalse(Route::has('filament.portal.resources.konta-portalu.index'));
    }

    public function test_super_admin_can_open_portal_user_list_and_edit_form(): void
    {
        $user = $this->makeSuperAdmin();
        $portalUser = $this->createPortalUserFixture();

        $this->actingAs($user)
            ->get(PortalUserResource::getUrl(panel: 'kancelaria'))
            ->assertOk()
            ->assertSee($portalUser->email);

        $this->actingAs($user)
            ->get(PortalUserResource::getUrl('edit', ['record' => $portalUser], panel: 'kancelaria'))
            ->assertOk()
            ->assertSee($portalUser->email);
    }

    public function test_employee_needs_portal_user_resource_permission(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_kancelaria_panel', 'view_any_portal::user'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo('access_kancelaria_panel');

        $this->actingAs($user)
            ->get(PortalUserResource::getUrl(panel: 'kancelaria'))
            ->assertForbidden();

        $user->givePermissionTo('view_any_portal::user');

        $this->actingAs($user)
            ->get(PortalUserResource::getUrl(panel: 'kancelaria'))
            ->assertOk();
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function createPortalUserFixture(): PortalUser
    {
        $contact = Contact::create([
            'type' => 'person',
            'category' => 'Kredytobiorca',
            'first_name' => 'Jan',
            'last_name' => 'Portalowy',
            'label' => 'Jan Portalowy',
            'sort_name' => 'Portalowy Jan',
            'email' => 'jan.portalowy@example.test',
        ]);

        return PortalUser::create([
            'name' => 'Jan Portalowy',
            'email' => 'portal@example.test',
            'password' => 'password',
            'is_active' => true,
            'contact_id' => $contact->id,
        ]);
    }
}
