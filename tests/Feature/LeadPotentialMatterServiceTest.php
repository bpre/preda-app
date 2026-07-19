<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Website\Resources\Leads\Pages\ViewLead;
use App\Models\Branch;
use App\Models\CHFPotentialMatter;
use App\Models\Stage;
use App\Models\Task;
use App\Models\TemplateStage;
use App\Models\User;
use App\Models\Website\Lead;
use App\Notifications\TaskCreated;
use App\Services\Website\LeadPotentialMatterService;
use App\Support\PanelAccess;
use App\Support\StageManager;
use App\Support\Website\LeadStatuses;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadPotentialMatterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_lead_creation_does_not_create_potential_matter_automatically(): void
    {
        $lead = $this->createLead();

        $this->assertNull($lead->potential_matter_id);
        $this->assertSame(0, CHFPotentialMatter::query()->count());
    }

    public function test_service_creates_potential_matter_contact_and_qualifies_lead(): void
    {
        $user = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $defaultStage = $this->createTemplateStage('Nowa umowa', isDefault: true);
        $analysisSentStage = $this->createTemplateStage('Przesłano analizę klientowi', sort: 2);
        $branch = Branch::create([
            'label' => 'Głogów',
            'user_id' => $user->getKey(),
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
            'is_default_for_new_matters' => true,
        ]);
        $lead = $this->createLead();

        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        $lead->refresh();

        $this->assertSame($matter->getKey(), $lead->potential_matter_id);
        $this->assertSame(LeadStatuses::QUALIFIED, $lead->status);
        $this->assertSame('Kowalski Jan / Bank Testowy', $matter->label);
        $this->assertFalse($matter->is_matter);
        $this->assertSame('CHF', $matter->category);
        $this->assertSame($branch->getKey(), $matter->branch_id);
        $this->assertSame('Głogów', $matter->branch);
        $this->assertSame($defaultStage->getKey(), $matter->refresh()->current_template_stage_id);
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.test',
        ]);
        $this->assertDatabaseHas('contact_matter', [
            'matter_id' => $matter->getKey(),
            'receives_notifications' => false,
        ]);
        $this->assertDatabaseHas('tasks', [
            'matter_id' => $matter->getKey(),
            'assigned_to' => $user->getKey(),
            'created_by' => $user->getKey(),
            'priority' => 3,
            'is_private' => false,
            'label' => 'Analiza umowy - kontakt z klientem',
        ]);

        app(LeadPotentialMatterService::class)->createForLead($lead, $user);
        $this->assertSame(1, CHFPotentialMatter::query()->count());
        $this->assertSame(1, Task::query()->count());

        StageManager::setCurrentStage($matter->refresh(), $analysisSentStage);

        $this->assertSame(LeadStatuses::QUALIFIED, $lead->refresh()->status);
        $this->assertSame($analysisSentStage->getKey(), $matter->refresh()->current_template_stage_id);
    }

    public function test_service_uses_branch_director_as_responsible_user_when_only_branch_is_selected(): void
    {
        $actor = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => false,
        ]);
        $branchUser = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $branch = Branch::create([
            'label' => 'Legnica',
            'user_id' => $branchUser->getKey(),
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);
        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();

        $matter = app(LeadPotentialMatterService::class)->createForLead(
            lead: $lead,
            actor: $actor,
            branch: $branch,
        );

        $this->assertSame($branch->getKey(), $matter->branch_id);
        $this->assertSame('Legnica', $matter->branch);
        $this->assertSame($branchUser->getKey(), $matter->lawyer_id);
        $this->assertDatabaseHas('tasks', [
            'matter_id' => $matter->getKey(),
            'assigned_to' => $branchUser->getKey(),
            'created_by' => $actor->getKey(),
            'priority' => 3,
        ]);
    }

    public function test_service_synchronizes_uploaded_lead_files_to_current_potential_matter_stage(): void
    {
        $user = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        $lead->forceFill([
            'files' => ['umowy-do-analizy/test/document.pdf'],
            'files_names' => ['umowy-do-analizy/test/document.pdf' => 'Umowa klienta.pdf'],
        ])->save();

        app(LeadPotentialMatterService::class)->syncLeadFilesToPotentialMatter($lead->refresh());

        $stage = Stage::query()
            ->where('matter_id', $matter->getKey())
            ->where('is_current', true)
            ->firstOrFail();

        $this->assertSame(['umowy-do-analizy/test/document.pdf'], $stage->files);
        $this->assertSame('Umowa klienta.pdf', $stage->files_names['umowy-do-analizy/test/document.pdf']);
    }

    public function test_crm_lead_record_action_creates_and_opens_potential_matter(): void
    {
        Filament::setCurrentPanel('crm');

        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $user = $this->makeSuperAdmin();
        $responsibleUser = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $branch = Branch::create([
            'label' => 'Zielona Góra',
            'user_id' => $responsibleUser->getKey(),
            'type' => Branch::TYPE_STATIONARY,
            'accepts_new_matters' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->callAction('openOrCreatePotentialMatter', data: [
                'branch_id' => $branch->getKey(),
                'responsible_user_id' => $responsibleUser->getKey(),
            ])
            ->assertHasNoActionErrors()
            ->assertRedirectContains('/potencjalne/');

        $lead->refresh();
        $matter = CHFPotentialMatter::query()->findOrFail($lead->potential_matter_id);

        $this->assertSame('Kowalski Jan / Bank Testowy', $matter->label);
        $this->assertSame($branch->getKey(), $matter->branch_id);
        $this->assertSame('Zielona Góra', $matter->branch);
        $this->assertSame($responsibleUser->getKey(), $matter->lawyer_id);
        $this->assertFalse($matter->is_matter);
        $this->assertSame([], $matter->userinfo);
        $this->assertSame(LeadStatuses::QUALIFIED, $lead->fresh()->status);
        $this->assertDatabaseHas('tasks', [
            'matter_id' => $matter->getKey(),
            'assigned_to' => $responsibleUser->getKey(),
            'created_by' => $user->getKey(),
            'priority' => 3,
            'is_private' => false,
            'label' => 'Analiza umowy - kontakt z klientem',
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => TaskCreated::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $responsibleUser->getKey(),
        ]);
        $this->assertDatabaseCount('notifications', 1);

        $this->get(CHFPotentialMatterResource::getUrl('edit', ['record' => $matter], panel: 'crm'))
            ->assertOk();
    }

    public function test_rejecting_lead_without_potential_matter_keeps_it_operationally_separate(): void
    {
        $user = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $lead = $this->createLead();

        app(LeadPotentialMatterService::class)->rejectLead(
            lead: $lead,
            reason: LeadStatuses::REASON_NOT_PROMISING,
            actor: $user,
            changedAt: '2026-07-10 15:00:00',
            note: 'Brak podstaw do dalszej obsługi.',
        );

        $lead->refresh();

        $this->assertSame(LeadStatuses::REJECTED, $lead->status);
        $this->assertSame(LeadStatuses::REASON_NOT_PROMISING, $lead->rejection_reason);
        $this->assertSame('Brak podstaw do dalszej obsługi.', $lead->rejection_note);
        $this->assertSame(0, CHFPotentialMatter::query()->count());
    }

    public function test_rejecting_qualified_lead_requires_incorrect_qualification_action(): void
    {
        $user = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        try {
            app(LeadPotentialMatterService::class)->rejectLead(
                lead: $lead,
                reason: LeadStatuses::REASON_DUPLICATE,
                actor: $user,
                changedAt: '2026-07-10 15:00:00',
                note: 'To samo zgłoszenie przyszło wcześniej z innego źródła.',
            );

            $this->fail('Zakwalifikowany lead został odrzucony zwykłą akcją.');
        } catch (RuntimeException) {
            $this->assertTrue(true);
        }

        $this->assertFalse($matter->fresh()->is_archived);
    }

    public function test_marking_lead_as_incorrectly_qualified_closes_linked_potential_matter(): void
    {
        $user = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        app(LeadPotentialMatterService::class)->markLeadAsIncorrectlyQualified(
            lead: $lead,
            reason: LeadStatuses::REASON_DUPLICATE,
            actor: $user,
            changedAt: '2026-07-10 15:00:00',
            note: 'To samo zgłoszenie przyszło wcześniej z innego źródła.',
        );

        $lead->refresh();
        $matter->refresh();

        $this->assertSame(LeadStatuses::REJECTED, $lead->status);
        $this->assertSame(LeadStatuses::REASON_DUPLICATE, $lead->rejection_reason);
        $this->assertStringContainsString('Błędnie zakwalifikowany.', $lead->rejection_note);
        $this->assertStringContainsString('To samo zgłoszenie przyszło wcześniej z innego źródła.', $lead->rejection_note);
        $this->assertSame('Zamknięta', $matter->status);
        $this->assertTrue($matter->is_archived);
        $this->assertSame('2026-07-10', $matter->end->format('Y-m-d'));
    }

    public function test_qualification_task_uses_file_specific_label_when_lead_has_files(): void
    {
        $user = User::factory()->create([
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $lead->forceFill([
            'files' => ['umowy-do-analizy/test/document.pdf'],
        ])->save();

        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        $this->assertDatabaseHas('tasks', [
            'matter_id' => $matter->getKey(),
            'assigned_to' => $user->getKey(),
            'priority' => 3,
            'label' => 'Kwalifikacja sprawy - kontakt z klientem',
        ]);
    }

    public function test_crm_lead_record_action_rejects_lead(): void
    {
        Filament::setCurrentPanel('crm');

        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $user = $this->makeSuperAdmin();

        $this->actingAs($user);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->assertActionVisible('rejectLead')
            ->callAction('rejectLead', data: [
                'reason' => LeadStatuses::REASON_NOT_PROMISING,
                'rejected_at' => '2026-07-10 16:00:00',
                'note' => 'Brak podstaw do dalszej obsługi.',
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(LeadStatuses::REJECTED, $lead->fresh()->status);
        $this->assertSame(LeadStatuses::REASON_NOT_PROMISING, $lead->fresh()->rejection_reason);
    }

    public function test_crm_lead_record_action_hides_regular_rejection_after_qualification(): void
    {
        Filament::setCurrentPanel('crm');

        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $user = $this->makeSuperAdmin();
        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        $this->actingAs($user);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->assertActionHidden('rejectLead')
            ->assertActionVisible('markLeadAsIncorrectlyQualified');

        $this->assertFalse($matter->fresh()->is_archived);
    }

    public function test_crm_incorrect_qualification_action_is_hidden_for_non_admin(): void
    {
        Filament::setCurrentPanel('crm');

        $responsibleUser = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);
        PanelAccess::grantDirect($responsibleUser, ['crm']);

        foreach (['View:Lead', 'ViewAny:Lead'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $responsibleUser->givePermissionTo(['View:Lead', 'ViewAny:Lead']);

        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        app(LeadPotentialMatterService::class)->createForLead($lead, $responsibleUser);

        $this->actingAs($responsibleUser);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->assertActionHidden('rejectLead')
            ->assertActionHidden('markLeadAsIncorrectlyQualified');
    }

    public function test_crm_lead_record_admin_action_marks_lead_as_incorrectly_qualified(): void
    {
        Filament::setCurrentPanel('crm');

        $this->createTemplateStage('Nowa umowa', isDefault: true);
        $lead = $this->createLead();
        $user = $this->makeSuperAdmin();
        $matter = app(LeadPotentialMatterService::class)->createForLead($lead, $user);

        $this->actingAs($user);

        Livewire::test(ViewLead::class, ['record' => $lead->getRouteKey()])
            ->callAction('markLeadAsIncorrectlyQualified', data: [
                'reason' => LeadStatuses::REASON_SPAM,
                'rejected_at' => '2026-07-10 16:00:00',
                'note' => 'Formularz wyglądał na automatyczne zgłoszenie.',
            ])
            ->assertHasNoActionErrors();

        $lead->refresh();
        $matter->refresh();

        $this->assertSame(LeadStatuses::REJECTED, $lead->status);
        $this->assertSame(LeadStatuses::REASON_SPAM, $lead->rejection_reason);
        $this->assertStringContainsString('Błędnie zakwalifikowany.', $lead->rejection_note);
        $this->assertStringContainsString('Formularz wyglądał na automatyczne zgłoszenie.', $lead->rejection_note);
        $this->assertTrue($matter->fresh()->is_archived);
        $this->assertSame('2026-07-10', $matter->end->format('Y-m-d'));
    }

    private function createLead(): Lead
    {
        return Lead::create([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
            'phone' => '500 600 700',
            'postal_code' => '67-200',
            'bank' => 'Bank Testowy',
            'contract_year_range' => '2007-2009',
            'credit_currency' => 'CHF',
            'credit_amount_range' => 'od 85.000 do 300.000 PLN',
            'credit_status' => 'nadal spłacam',
            'has_contract' => true,
            'message' => 'Zgłoszenie testowe.',
        ]);
    }

    private function createTemplateStage(string $label, int $sort = 1, bool $isDefault = false): TemplateStage
    {
        return TemplateStage::create([
            'category' => 'Potencjalna',
            'label' => $label,
            'parent' => 'Pozyskanie klienta',
            'sort' => $sort,
            'is_chf_default' => $isDefault,
            'is_active' => true,
        ]);
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
}
