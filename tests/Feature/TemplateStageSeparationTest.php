<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use App\Filament\Crm\Resources\PotentialMatterStageResource;
use App\Filament\Crm\Resources\PotentialMatterStageResource\Pages\ListPotentialMatterStages;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\TemplateStageResource;
use App\Models\CHFPotentialMatter;
use App\Models\Stage;
use App\Models\TemplateStage;
use App\Models\User;
use App\Support\StageManager;
use Filament\Facades\Filament;
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

    public function test_crm_stage_form_hides_category_when_only_one_category_exists(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(ListPotentialMatterStages::class)
            ->mountAction('create')
            ->assertFormFieldHidden('parent')
            ->setActionData([
                'label' => 'Ręczny etap CRM',
                'is_active' => true,
                'is_chf_default' => false,
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('template_stages', [
            'category' => PotentialMatterStageResource::CATEGORY,
            'parent' => 'Pozyskanie klienta',
            'label' => 'Ręczny etap CRM',
        ]);
    }

    public function test_potential_matter_add_stage_form_hides_category_when_only_one_category_is_addable(): void
    {
        $user = $this->makeSuperAdmin();
        $templateStage = TemplateStage::create([
            'category' => 'Potencjalna',
            'parent' => 'Pozyskanie klienta',
            'label' => 'Etap do dodania w CRM',
            'sort' => 999,
            'is_active' => true,
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Sprawa z dodawanym etapem',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(StagesRelationManager::class, [
            'ownerRecord' => $matter,
            'pageClass' => EditCHFPotentialMatter::class,
        ])
            ->mountTableAction('addStage')
            ->assertFormFieldHidden('stage_parent')
            ->setTableActionData([
                'stage_id' => $templateStage->getKey(),
                'date' => '2026-07-19',
                'is_current' => true,
            ])
            ->callMountedTableAction()
            ->assertHasNoActionErrors();

        $stage = Stage::query()
            ->where('matter_id', $matter->getKey())
            ->where('stage_id', $templateStage->getKey())
            ->firstOrFail();

        $this->assertDatabaseHas('stages', [
            'matter_id' => $matter->getKey(),
            'stage_id' => $templateStage->getKey(),
            'parent' => 'Pozyskanie klienta',
            'label' => 'Etap do dodania w CRM',
            'is_current' => true,
        ]);
        $this->assertSame('2026-07-19', $stage->date?->toDateString());
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

    public function test_stage_manager_records_who_and_when_set_current_stage(): void
    {
        $user = $this->makeSuperAdmin();
        $templateStage = TemplateStage::create([
            'category' => 'Potencjalna',
            'parent' => 'Pozyskanie klienta',
            'label' => 'Audytowany etap CRM',
            'sort' => 1,
            'is_active' => true,
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Sprawa z audytem aktualnego etapu',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $this->actingAs($user);
        $this->travelTo('2026-07-19 10:15:00');

        $stage = StageManager::setCurrentStage($matter, $templateStage, '2026-07-19');

        $this->travelBack();

        $this->assertNotNull($stage);
        $this->assertSame($user->getKey(), $stage->current_stage_set_by);
        $this->assertSame('2026-07-19 10:15:00', $stage->current_stage_set_at?->format('Y-m-d H:i:s'));
        $this->assertSame($user->getKey(), $stage->last_edited_by);
        $this->assertSame('2026-07-19 10:15:00', $stage->last_edited_at?->format('Y-m-d H:i:s'));

        $matter->refresh();

        $this->assertSame($user->getKey(), $matter->current_stage_set_by);
        $this->assertSame('2026-07-19 10:15:00', $matter->current_stage_set_at?->format('Y-m-d H:i:s'));
    }

    public function test_stage_manager_records_who_and_when_last_edited_stage_without_replacing_current_stage_setter(): void
    {
        $setter = $this->makeSuperAdmin();
        $editor = User::factory()->create(['is_active' => true]);
        $templateStage = TemplateStage::create([
            'category' => 'Potencjalna',
            'parent' => 'Pozyskanie klienta',
            'label' => 'Edytowany etap CRM',
            'sort' => 1,
            'is_active' => true,
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Sprawa z audytem edycji etapu',
            'lawyer_id' => $setter->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $this->actingAs($setter);
        $this->travelTo('2026-07-19 10:15:00');

        $stage = StageManager::setCurrentStage($matter, $templateStage, '2026-07-19');

        $this->actingAs($editor);
        $this->travelTo('2026-07-19 11:45:00');

        StageManager::saveStageDetails($matter->refresh(), $templateStage, [
            'date' => '2026-07-19',
            'is_current' => true,
            'description' => '<p>Uzupełniona notatka.</p>',
        ]);

        $this->travelBack();

        $stage->refresh();

        $this->assertSame($setter->getKey(), $stage->current_stage_set_by);
        $this->assertSame('2026-07-19 10:15:00', $stage->current_stage_set_at?->format('Y-m-d H:i:s'));
        $this->assertSame($editor->getKey(), $stage->last_edited_by);
        $this->assertSame('2026-07-19 11:45:00', $stage->last_edited_at?->format('Y-m-d H:i:s'));

        $matter->refresh();

        $this->assertSame($setter->getKey(), $matter->current_stage_set_by);
        $this->assertSame('2026-07-19 10:15:00', $matter->current_stage_set_at?->format('Y-m-d H:i:s'));
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
