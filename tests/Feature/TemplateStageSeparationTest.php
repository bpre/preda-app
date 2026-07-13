<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use App\Filament\Crm\Resources\PotentialMatterStageResource;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\TemplateStageResource;
use App\Models\CHFPotentialMatter;
use App\Models\TemplateStage;
use App\Models\User;
use App\Support\StageManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TemplateStageSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_and_kancelaria_manage_separate_stage_definitions(): void
    {
        $user = $this->makeSuperAdmin();

        TemplateStage::create([
            'category' => 'Potencjalna',
            'parent' => 'Pozyskanie klienta',
            'label' => 'CRM-only etap testowy',
            'sort' => 1,
            'is_active' => true,
        ]);

        TemplateStage::create([
            'category' => 'CHF',
            'parent' => 'Etap przedsądowy',
            'label' => 'Kancelaria-only etap testowy',
            'sort' => 1,
            'is_active' => true,
        ]);

        $this->assertTrue(Route::has('filament.crm.resources.etapy.index'));
        $this->assertTrue(Route::has('filament.kancelaria.resources.etapy.index'));

        $this->actingAs($user)
            ->get(TemplateStageResource::getUrl(panel: 'kancelaria'))
            ->assertOk()
            ->assertSee('Kancelaria-only etap testowy')
            ->assertDontSee('CRM-only etap testowy');

        $this->actingAs($user)
            ->get(PotentialMatterStageResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('CRM-only etap testowy')
            ->assertDontSee('Kancelaria-only etap testowy');
    }

    public function test_potential_matter_stage_history_uses_stage_snapshot_after_template_change(): void
    {
        $user = $this->makeSuperAdmin();
        $templateStage = TemplateStage::create([
            'category' => 'Potencjalna',
            'parent' => 'Pozyskanie klienta',
            'label' => 'Historyczny etap CRM',
            'sort' => 1,
            'is_active' => true,
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Sprawa z historią etapów',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $stage = StageManager::setCurrentStage($matter, $templateStage, '2026-07-12');
        $this->assertNotNull($stage);

        $templateStage->update([
            'label' => 'Nowa nazwa definicji CRM',
            'parent' => 'Nowa kategoria CRM',
            'is_active' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(StagesRelationManager::class, [
            'ownerRecord' => $matter->refresh(),
            'pageClass' => EditCHFPotentialMatter::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords([$stage])
            ->assertSee('Historyczny etap CRM')
            ->assertSee('Pozyskanie klienta')
            ->assertDontSee('Nowa nazwa definicji CRM')
            ->assertDontSee('Nowa kategoria CRM');
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
