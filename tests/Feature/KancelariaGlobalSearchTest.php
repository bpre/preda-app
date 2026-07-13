<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Search\KancelariaGlobalSearchProvider;
use App\Models\CHFPotentialMatter;
use App\Models\TemplateStage;
use App\Models\User;
use App\Support\PanelAccess;
use App\Support\StageManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KancelariaGlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_kancelaria_global_search_includes_potential_matters_for_users_with_crm_access(): void
    {
        $user = $this->createEmployeeWithPermissions([
            'kancelaria',
            'crm',
        ], [
            'view_any_c::h::f::potential::matter',
            'update_c::h::f::potential::matter',
        ]);

        $stage = TemplateStage::create([
            'label' => 'Analiza umowy',
            'category' => 'Potencjalna',
            'parent' => 'Pozyskanie klienta',
            'sort' => 1,
            'is_active' => true,
        ]);
        $lawyer = User::factory()->create([
            'name' => 'Anna Referatowa',
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Kowalski Jan / Bank Testowy',
            'lawyer_id' => $lawyer->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
            'is_archived' => false,
        ]);
        StageManager::setCurrentStage($matter, $stage);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('kancelaria'));

        $category = app(KancelariaGlobalSearchProvider::class)
            ->getResults('Kowalski')
            ?->getCategories()
            ->get('Potencjalne sprawy');

        $this->assertCount(1, $category);
        $this->assertSame('Kowalski Jan / Bank Testowy', $category[0]->title);
        $this->assertSame(
            CHFPotentialMatterResource::getUrl('edit', ['record' => $matter], panel: 'crm'),
            $category[0]->url,
        );
        $this->assertSame([
            'Panel' => 'CRM',
            'Etap' => 'Analiza umowy',
            'Referat' => 'Anna Referatowa',
        ], $category[0]->details);
    }

    public function test_kancelaria_global_search_hides_potential_matters_without_crm_access(): void
    {
        $user = $this->createEmployeeWithPermissions([
            'kancelaria',
        ], [
            'view_any_c::h::f::potential::matter',
            'update_c::h::f::potential::matter',
        ]);
        $lawyer = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);

        CHFPotentialMatter::create([
            'label' => 'Nowak Anna / Bank Ukryty',
            'lawyer_id' => $lawyer->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
            'is_archived' => false,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('kancelaria'));

        $this->assertFalse(
            app(KancelariaGlobalSearchProvider::class)
                ->getResults('Nowak')
                ?->getCategories()
                ->has('Potencjalne sprawy')
        );
    }

    private function createEmployeeWithPermissions(array $panels, array $permissions): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        PanelAccess::grantDirect($user, $panels);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}
