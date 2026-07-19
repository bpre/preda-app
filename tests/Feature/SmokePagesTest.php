<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource as CrmPotentialMatterResource;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\ListCHFPotentialMatters;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\RelationManagers\ClientMessagesRelationManager;
use App\Filament\Crm\Pages\Dashboard as CrmDashboard;
use App\Filament\Crm\Resources\CrmMailPlaceholderResource;
use App\Filament\Crm\Resources\CrmMailTemplateResource;
use App\Filament\Crm\Resources\CrmWorkflowOfferResource;
use App\Filament\Crm\Widgets\LeadStatsWidget;
use App\Filament\Crm\Widgets\PotentialMattersRequiringActionWidget;
use App\Filament\Portal\Resources\CHFMatterResource as PortalCHFMatterResource;
use App\Filament\Portal\Resources\LetterResource as PortalLetterResource;
use App\Filament\Resources\ActivityResource;
use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use App\Filament\Resources\BankMatterResource as KancelariaBankMatterResource;
use App\Filament\Resources\CHFMatterResource as KancelariaCHFMatterResource;
use App\Filament\Resources\CHFMatterResource\Pages\EditCHFMatter;
use App\Filament\Resources\CHFMatterResource\Pages\ListCHFMatters;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\OffersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\CHFPaymentMatterResource as KancelariaCHFPaymentMatterResource;
use App\Filament\Resources\MatterResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\ContactResource as KancelariaContactResource;
use App\Filament\Resources\LetterResource as KancelariaLetterResource;
use App\Filament\Resources\TaskResource as KancelariaTaskResource;
use App\Filament\Resources\UserResource as KancelariaUserResource;
use App\Filament\Website\Resources\Leads\LeadResource as WebsiteLeadResource;
use App\Filament\Website\Resources\Leads\Pages\ListLeads as WebsiteListLeads;
use App\Filament\Website\Resources\Posts\PostResource;
use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Filament\Website\Resources\Users\UserResource as WebsiteUserResource;
use App\Models\Activity;
use App\Models\CHFMatter;
use App\Models\CHFPotentialMatter;
use App\Models\Contact;
use App\Models\ContactMatter;
use App\Models\Deal;
use App\Models\Letter;
use App\Models\Matter;
use App\Models\PortalUser;
use App\Models\User;
use App\Models\Website\Lead;
use App\Models\Website\Post;
use App\Services\Crm\LeadStatsService;
use App\Services\Crm\PotentialMatterWorkflowService;
use App\Support\Crm\ClientAcquisitionAccess;
use App\Support\Crm\MarketingAgencyAccess;
use App\Support\ShieldPanelPermissions;
use App\Support\StageManager;
use App\Support\Website\LeadStatuses;
use App\Support\Website\LeadTypes;
use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmokePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_employee_panel_login_pages_are_accessible(): void
    {
        $this->get('http://ewidencja.preda-app.test/login')
            ->assertOk()
            ->assertDontSee('fi-simple-header-heading', false);

        $this->get('http://crm.preda-app.test/login')
            ->assertOk()
            ->assertDontSee('fi-simple-header-heading', false);

        $this->get('http://cms.preda-app.test/login')
            ->assertOk()
            ->assertDontSee('fi-simple-header-heading', false);
    }

    public function test_old_kancelaria_panel_paths_redirect_to_the_ewidencja_root_paths(): void
    {
        $this->get('http://ewidencja.preda-app.test/kancelaria')
            ->assertRedirect('http://ewidencja.preda-app.test');

        $this->get('http://ewidencja.preda-app.test/kancelaria/chf?table=wide')
            ->assertRedirect('http://ewidencja.preda-app.test/chf?table=wide');
    }

    public function test_the_portal_login_page_is_accessible(): void
    {
        $this->get('http://portal.preda-app.test/login')
            ->assertOk()
            ->assertDontSee('fi-simple-header-heading', false);
    }

    public function test_an_active_portal_user_can_only_open_their_own_matters_and_letters(): void
    {
        [$portalUser, $matter, $letter, $otherMatter] = $this->createPortalMatterFixture();

        $this->actingAs($portalUser, 'portal')
            ->get(PortalCHFMatterResource::getUrl(panel: 'portal'))
            ->assertOk()
            ->assertSee('Portal matter')
            ->assertDontSee('Other portal matter');

        $this->actingAs($portalUser, 'portal')
            ->get(PortalCHFMatterResource::getUrl('view', ['record' => $matter], panel: 'portal'))
            ->assertOk()
            ->assertSee('Portal matter');

        $this->actingAs($portalUser, 'portal')
            ->get(PortalCHFMatterResource::getUrl('view', ['record' => $otherMatter], panel: 'portal'))
            ->assertNotFound();

        $this->actingAs($portalUser, 'portal')
            ->get(PortalLetterResource::getUrl(panel: 'portal'))
            ->assertOk()
            ->assertSee('Portal letter')
            ->assertDontSee('Other portal letter');

        $this->actingAs($portalUser, 'portal')
            ->get(PortalLetterResource::getUrl('view', ['record' => $letter], panel: 'portal'))
            ->assertOk()
            ->assertSee('Portal letter');
    }

    public function test_portal_letter_files_are_scoped_to_the_logged_in_contact(): void
    {
        [$portalUser] = $this->createPortalMatterFixture();

        Storage::fake('local');
        Storage::disk('local')->put('k2/portal/document.pdf', 'portal file');
        Storage::disk('local')->put('k2/portal/other.pdf', 'other file');

        $this->actingAs($portalUser, 'portal')
            ->get('http://portal.preda-app.test/z/k2/portal/document.pdf')
            ->assertOk();

        $this->actingAs($portalUser, 'portal')
            ->get('http://portal.preda-app.test/z/k2/portal/other.pdf')
            ->assertNotFound();
    }

    public function test_portal_user_cannot_access_employee_panels(): void
    {
        [$portalUser] = $this->createPortalMatterFixture();

        $this->actingAs($portalUser, 'portal')
            ->get('http://ewidencja.preda-app.test/')
            ->assertRedirect();

        $this->actingAs($portalUser, 'portal')
            ->get('http://crm.preda-app.test/')
            ->assertRedirect();

        $this->actingAs($portalUser, 'portal')
            ->get('http://cms.preda-app.test/')
            ->assertRedirect();
    }

    public function test_employee_user_cannot_access_the_client_portal_with_web_session(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->actingAs($user)
            ->get('http://portal.preda-app.test/')
            ->assertRedirect();
    }

    public function test_the_blog_listing_page_renders_successfully(): void
    {
        $this->createNavigationPosts();

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Blog')
            ->assertSee('Blog entry');
    }

    public function test_the_paid_off_credit_page_renders_successfully(): void
    {
        $this->createNavigationPosts();

        $this->get('/splacony-kredyt-frankowy')
            ->assertOk()
            ->assertSee('Spłaciłeś kredyt frankowy?')
            ->assertSee('site:open-analysis-sidebar', false)
            ->assertSee('(min-width: 1600px)', false)
            ->assertSee('data-duo-analysis-sidebar-panel', false);
    }

    public function test_an_active_super_admin_can_open_panel_entry_points(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get('http://ewidencja.preda-app.test/')
            ->assertOk();

        $this->actingAs($user)
            ->get('http://crm.preda-app.test/')
            ->assertOk();

        $this->actingAs($user)
            ->get('http://cms.preda-app.test/')
            ->assertRedirect(SentenceResource::getUrl(panel: 'cms'));
    }

    public function test_an_active_super_admin_can_open_key_cms_resource_lists(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get(PostResource::getUrl(panel: 'cms'))
            ->assertOk();

        $this->actingAs($user)
            ->get(SentenceResource::getUrl(panel: 'cms'))
            ->assertOk();

        $this->actingAs($user)
            ->get(WebsiteUserResource::getUrl(panel: 'cms'))
            ->assertOk();
    }

    public function test_an_active_super_admin_can_open_key_kancelaria_resource_lists(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get(KancelariaContactResource::getUrl(panel: 'kancelaria'))
            ->assertOk();

        $this->actingAs($user)
            ->get(KancelariaCHFMatterResource::getUrl(panel: 'kancelaria'))
            ->assertOk()
            ->assertSee('fi-table-width-toggle-icon', false)
            ->assertSee('wire:poll.10s', false);

        $this->actingAs($user)
            ->get(KancelariaLetterResource::getUrl(panel: 'kancelaria'))
            ->assertOk();

        $this->actingAs($user)
            ->get(KancelariaTaskResource::getUrl(panel: 'kancelaria'))
            ->assertOk();

        $this->actingAs($user)
            ->get(ActivityResource::getUrl(panel: 'kancelaria'))
            ->assertOk()
            ->assertSee('Notatki i czynności');
    }

    public function test_kancelaria_user_edit_page_uses_user_name_as_title(): void
    {
        $user = $this->makeSuperAdmin();
        $managedUser = User::factory()->create([
            'name' => 'Anna Testowa',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(KancelariaUserResource::getUrl('edit', ['record' => $managedUser], panel: 'kancelaria'))
            ->assertOk()
            ->assertSeeInOrder(['fi-header-heading', 'Anna Testowa'], false);
    }

    public function test_kancelaria_user_resource_is_not_globally_searchable(): void
    {
        $this->assertSame([], KancelariaUserResource::getGloballySearchableAttributes());
        $this->assertFalse(KancelariaUserResource::canGloballySearch());
    }

    public function test_an_active_super_admin_can_open_key_crm_resource_lists(): void
    {
        $user = $this->makeSuperAdmin();
        Lead::create([
            'name' => 'Jan Kodowy',
            'email' => 'kodowy@example.test',
            'phone' => '500 600 700',
            'postal_code' => '67-200',
            'message' => 'Zgłoszenie testowe.',
        ]);

        $this->actingAs($user)
            ->get(CrmPotentialMatterResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('<span data-filament-table-width-page hidden></span>', false)
            ->assertSee('fi-table-width-toggle-icon', false)
            ->assertDontSee('window.filamentTableWidth?.markTablePage();', false);

        $this->actingAs($user)
            ->get(WebsiteLeadResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('<span data-filament-table-width-page hidden></span>', false)
            ->assertSee('67-200 (dolnośląskie, głogowski)');

        $this->actingAs($user)
            ->get(CrmMailTemplateResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Szablony maili');

        $this->actingAs($user)
            ->get(CrmMailPlaceholderResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Akapit o korzyściach');

        $this->actingAs($user)
            ->get(CrmWorkflowOfferResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Oferty workflow');
    }

    public function test_crm_potential_matter_table_hides_kancelaria_only_columns(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(ListCHFPotentialMatters::class)
            ->assertTableColumnDoesNotExist('currentStage.parent')
            ->assertTableColumnExists('currentStage.label')
            ->assertTableColumnDoesNotExist('next_action_key')
            ->assertTableColumnDoesNotExist('next_action_due_at')
            ->assertTableColumnDoesNotExist('is_matter')
            ->assertTableColumnDoesNotExist('is_archived')
            ->assertTableColumnDoesNotExist('start')
            ->assertTableColumnDoesNotExist('end');

        $this->assignPartnerRole($user);

        Livewire::test(ListCHFPotentialMatters::class)
            ->assertTableColumnExists('next_action_key')
            ->assertTableColumnExists('next_action_due_at');

        Filament::setCurrentPanel('kancelaria');

        Livewire::test(ListCHFMatters::class)
            ->assertTableColumnExists('currentStage.parent')
            ->assertTableColumnExists('is_matter')
            ->assertTableColumnExists('is_archived')
            ->assertTableColumnExists('start')
            ->assertTableColumnExists('end');
    }

    public function test_crm_potential_matter_table_width_toggle_stays_available_after_sorting(): void
    {
        $user = $this->makeSuperAdmin();

        CHFPotentialMatter::create([
            'label' => 'Testowa potencjalna sprawa',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $this->assignPartnerRole($user);

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(ListCHFPotentialMatters::class)
            ->assertSee('<span data-filament-table-width-page hidden></span>', false)
            ->assertSee('fi-table-width-toggle-icon', false)
            ->sortTable('label')
            ->assertSee('<span data-filament-table-width-page hidden></span>', false)
            ->assertSee('fi-table-width-toggle-icon', false);
    }

    public function test_crm_potential_matter_table_defaults_to_my_referat_filter(): void
    {
        $user = $this->makeSuperAdmin();
        $user->forceFill(['is_lawyer' => true])->save();

        $otherLawyer = User::factory()->create([
            'is_active' => true,
            'is_lawyer' => true,
        ]);

        $myPotentialMatter = CHFPotentialMatter::create([
            'label' => 'Moja potencjalna sprawa',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $otherPotentialMatter = CHFPotentialMatter::create([
            'label' => 'Cudza potencjalna sprawa',
            'lawyer_id' => $otherLawyer->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(ListCHFPotentialMatters::class)
            ->loadTable()
            ->assertTableFilterExists('scopeMine')
            ->assertTableFilterVisible('scopeMine')
            ->assertCanSeeTableRecords([$myPotentialMatter, $otherPotentialMatter]);

        $this->assignPartnerRole($user);
        $this->actingAs($user->fresh());

        Livewire::test(ListCHFPotentialMatters::class)
            ->loadTable()
            ->assertSet('tableFilters.scopeMine.isActive', true)
            ->assertCanSeeTableRecords([$myPotentialMatter])
            ->assertCanNotSeeTableRecords([$otherPotentialMatter]);
    }

    public function test_matters_are_soft_deleted_and_only_admin_can_restore_them(): void
    {
        $admin = $this->makeSuperAdmin();
        $employee = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);

        foreach ([
            'access_crm_panel',
            'view_c::h::f::potential::matter',
            'view_any_c::h::f::potential::matter',
            'restore_c::h::f::potential::matter',
            'restore_any_c::h::f::potential::matter',
        ] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $employee->givePermissionTo([
            'access_crm_panel',
            'view_c::h::f::potential::matter',
            'view_any_c::h::f::potential::matter',
            'restore_c::h::f::potential::matter',
            'restore_any_c::h::f::potential::matter',
        ]);

        $potentialMatter = CHFPotentialMatter::create([
            'label' => 'Usuwana potencjalna sprawa',
            'lawyer_id' => $employee->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $potentialMatter->delete();

        $this->assertSoftDeleted('matters', [
            'id' => $potentialMatter->getKey(),
        ]);

        $trashedPotentialMatter = CHFPotentialMatter::withTrashed()->findOrFail($potentialMatter->getKey());

        $this->assertFalse($employee->can('restore', $trashedPotentialMatter));
        $this->assertTrue($admin->can('restore', $trashedPotentialMatter));

        $this->actingAs($employee);

        Filament::setCurrentPanel('crm');

        Livewire::test(ListCHFPotentialMatters::class)
            ->assertTableFilterHidden('trashed');

        $this->actingAs($admin);

        Livewire::test(ListCHFPotentialMatters::class)
            ->assertTableFilterVisible('trashed')
            ->filterTable('trashed', false)
            ->loadTable()
            ->assertCanSeeTableRecords([$trashedPotentialMatter])
            ->assertTableActionHidden('edit', $trashedPotentialMatter)
            ->assertTableActionVisible('restore', $trashedPotentialMatter)
            ->callTableAction('restore', $trashedPotentialMatter);

        $this->assertDatabaseHas('matters', [
            'id' => $potentialMatter->getKey(),
            'deleted_at' => null,
        ]);
    }

    public function test_crm_potential_matter_table_sorts_non_partners_by_created_at_desc(): void
    {
        $user = $this->makeSuperAdmin();
        $user->forceFill(['is_lawyer' => true])->save();

        $olderUrgentMatter = CHFPotentialMatter::create([
            'label' => 'A starsza sprawa z pilnym terminem',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $olderUrgentMatter->forceFill([
            'created_at' => now()->subDays(10)->setTime(10, 0),
            'updated_at' => now()->subDays(10)->setTime(10, 0),
            'next_action_key' => 'follow_up_after_analysis',
            'next_action_due_at' => now()->subDay()->toDateString(),
            'next_action_reason' => 'Po analizie nie odnotowano odpowiedzi klienta.',
        ])->save();

        $newerMatter = CHFPotentialMatter::create([
            'label' => 'Z nowsza sprawa bez terminu',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $newerMatter->forceFill([
            'created_at' => now()->subDay()->setTime(10, 0),
            'updated_at' => now()->subDay()->setTime(10, 0),
        ])->save();

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(ListCHFPotentialMatters::class)
            ->loadTable()
            ->assertSeeInOrder([
                'Z nowsza sprawa bez terminu',
                'A starsza sprawa z pilnym terminem',
            ]);

        $this->assignPartnerRole($user);
        $this->actingAs($user->fresh());

        Livewire::test(ListCHFPotentialMatters::class)
            ->loadTable()
            ->assertSeeInOrder([
                'A starsza sprawa z pilnym terminem',
                'Z nowsza sprawa bez terminu',
            ]);
    }

    public function test_crm_navigation_shows_leads_before_potential_matters(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get(WebsiteLeadResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSeeInOrder(['Leady', 'Potencjalne sprawy']);
    }

    public function test_crm_dashboard_shows_widgets_according_to_permissions(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_crm_panel', 'ViewAny:Lead', 'view_any_c::h::f::potential::matter'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo(['access_crm_panel', 'ViewAny:Lead']);

        $this->actingAs($user);

        $this->assertTrue(Route::has('filament.crm.pages.dashboard'));
        $this->assertTrue(LeadStatsWidget::canView());
        $this->assertFalse(PotentialMattersRequiringActionWidget::canView());
        $this->assertTrue(CrmDashboard::shouldRegisterNavigation());
        $this->assertSame('Statystyki', CrmDashboard::getNavigationLabel());
        $this->assertSame('Statystyki', (string) app(CrmDashboard::class)->getTitle());

        $this->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Statystyki')
            ->assertSee('Okres statystyk leadów')
            ->assertSee('Statystyki leadów')
            ->assertDontSee('fi-sc-tabs', false)
            ->assertDontSee('Sprawy wymagające działania');

        $nonPartnerActionUser = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);
        $nonPartnerActionUser->givePermissionTo(['access_crm_panel', 'view_any_c::h::f::potential::matter']);

        $this->actingAs($nonPartnerActionUser);

        $this->assertFalse(PotentialMattersRequiringActionWidget::canView());
        $this->assertFalse(CrmDashboard::shouldRegisterNavigation());

        $this->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertRedirect(CrmPotentialMatterResource::getUrl(panel: 'crm'));

        $actionOnlyUser = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);
        $actionOnlyUser->givePermissionTo(['access_crm_panel', 'view_any_c::h::f::potential::matter']);
        $this->assignPartnerRole($actionOnlyUser);

        $this->actingAs($actionOnlyUser);

        $this->assertFalse(LeadStatsWidget::canView());
        $this->assertTrue(PotentialMattersRequiringActionWidget::canView());
        $this->assertTrue(CrmDashboard::shouldRegisterNavigation());
        $this->assertSame('Dashboard', CrmDashboard::getNavigationLabel());
        $this->assertSame('CRM', (string) app(CrmDashboard::class)->getTitle());
        $this->assertNull(app(CrmDashboard::class)->getHeading());

        $this->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Sprawy wymagające działania')
            ->assertDontSee('fi-sc-tabs', false)
            ->assertDontSee('Statystyki leadów');

        $this->actingAs($user);

        $user->givePermissionTo('view_any_c::h::f::potential::matter');
        $this->assignPartnerRole($user);

        $this->assertTrue(PotentialMattersRequiringActionWidget::canView());
        $this->assertSame('Dashboard', CrmDashboard::getNavigationLabel());
        $this->assertSame('CRM', (string) app(CrmDashboard::class)->getTitle());
        $this->assertNull(app(CrmDashboard::class)->getHeading());

        $this->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('fi-sc-tabs', false)
            ->assertSee('Sprawy wymagające działania')
            ->assertSeeInOrder([
                'Sprawy wymagające działania',
                'Okres statystyk leadów',
                'Statystyki leadów',
            ]);
    }

    public function test_marketing_agency_role_can_open_crm_lead_stats_without_action_widget(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $user->assignRole(Role::findOrCreate(MarketingAgencyAccess::ROLE, 'web'));

        $this->actingAs($user);

        $this->assertTrue($user->refresh()->canAccessPredaPanel('crm'));
        $this->assertFalse($user->canAccessPredaPanel('kancelaria'));
        $this->assertFalse($user->canAccessPredaPanel('cms'));
        $this->assertTrue(LeadStatsWidget::canView());
        $this->assertFalse(PotentialMattersRequiringActionWidget::canView());
        $this->assertSame('Statystyki', CrmDashboard::getNavigationLabel());
        $this->assertSame('Statystyki', (string) app(CrmDashboard::class)->getTitle());

        $this
            ->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Statystyki leadów')
            ->assertSee('Eksportuj')
            ->assertDontSee('Sprawy wymagające działania')
            ->assertDontSee('Potencjalne sprawy');
    }

    public function test_crm_role_form_exposes_marketing_access_permissions(): void
    {
        $permissions = ShieldPanelPermissions::groups()['crm']['customPermissions'] ?? [];

        $this->assertSame('Statystyki leadów', $permissions[MarketingAgencyAccess::VIEW_LEAD_STATS_PERMISSION] ?? null);
        $this->assertSame('Leady - widok marketingowy', $permissions[MarketingAgencyAccess::VIEW_MARKETING_LEADS_PERMISSION] ?? null);
        $this->assertSame('Eksport statystyk leadów', $permissions[LeadStatsService::EXPORT_PERMISSION] ?? null);
    }

    public function test_lead_stats_widget_permission_can_open_crm_dashboard_stats(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        Permission::firstOrCreate([
            'name' => MarketingAgencyAccess::VIEW_LEAD_STATS_WIDGET_PERMISSION,
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo(MarketingAgencyAccess::VIEW_LEAD_STATS_WIDGET_PERMISSION);

        $this->actingAs($user);

        $this->assertTrue($user->refresh()->canAccessPredaPanel('crm'));
        $this->assertTrue(LeadStatsWidget::canView());
        $this->assertFalse(PotentialMattersRequiringActionWidget::canView());
        $this->assertSame('Statystyki', CrmDashboard::getNavigationLabel());
        $this->assertSame('Statystyki', (string) app(CrmDashboard::class)->getTitle());

        $this
            ->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Statystyki leadów')
            ->assertDontSee('Sprawy wymagające działania')
            ->assertDontSee('Eksportuj');
    }

    public function test_marketing_agency_role_sees_restricted_lead_data_only(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $user->assignRole(Role::findOrCreate(MarketingAgencyAccess::ROLE, 'web'));

        $lead = Lead::create([
            'name' => 'Jan Tajny',
            'email' => 'jan.tajny@example.test',
            'phone' => '500 600 700',
            'postal_code' => '67-200',
            'postal_voivodeship' => 'dolnośląskie',
            'postal_county' => 'głogowski',
            'lead_type' => LeadTypes::FORM,
            'bank' => 'Bank Testowy',
            'contract_year_range' => '2007',
            'credit_currency' => 'CHF',
            'credit_amount_range' => 'od 85.000 do 300.000 PLN',
            'credit_status' => 'nadal spłacam',
            'has_contract' => true,
            'additional_info' => 'Klient prosi o kontakt po godzinie 16.',
            'status' => LeadStatuses::QUALIFIED,
            'attribution_channel' => 'google_ads',
            'attribution_source' => 'google',
            'attribution_medium' => 'cpc',
            'attribution_campaign' => 'kampania-testowa',
            'attribution_term' => 'kredyt frankowy',
            'attribution_content' => 'reklama-a',
            'attribution_landing_page' => 'https://preda.info/analiza',
            'attribution_conversion_page' => 'https://preda.info/analiza#formularz',
            'attribution_referrer' => 'https://google.example.test',
            'message' => 'Dodatkowe informacje: Tajna treść wiadomości.',
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Tajny Jan / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'userinfo' => [],
            'is_matter' => false,
        ]);
        $lead->forceFill([
            'potential_matter_id' => $matter->getKey(),
            'potential_matter_created_at' => now(),
            'potential_matter_created_by' => $user->getKey(),
        ])->save();
        $lead->qualify(userId: $user->getKey(), note: 'Utworzono potencjalną sprawę.');

        $this
            ->actingAs($user)
            ->get(WebsiteLeadResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee(MarketingAgencyAccess::hiddenValue())
            ->assertSee('Formularz')
            ->assertSee('Google Ads')
            ->assertSee('kampania-testowa')
            ->assertDontSee('Jan Tajny')
            ->assertDontSee('jan.tajny@example.test')
            ->assertDontSee('500 600 700')
            ->assertDontSee('Zakwalifikowane')
            ->assertDontSee('Potencjalna sprawa');

        $this
            ->actingAs($user)
            ->get(WebsiteLeadResource::getUrl('view', ['record' => $lead], panel: 'crm'))
            ->assertOk()
            ->assertSee('Lead #'.$lead->getKey())
            ->assertSee('Źródło leada')
            ->assertSee('Dane z formularza')
            ->assertSee('google')
            ->assertSee('cpc')
            ->assertSee('kampania-testowa')
            ->assertSee('Formularz')
            ->assertSee('67-200, powiat głogowski, województwo dolnośląskie')
            ->assertSee('Bank Testowy')
            ->assertSee('2007')
            ->assertSee('CHF')
            ->assertSee('od 85.000 do 300.000 PLN')
            ->assertSee('nadal spłacam')
            ->assertSee('Tak')
            ->assertSee(MarketingAgencyAccess::hiddenValue())
            ->assertDontSee('Jan Tajny')
            ->assertDontSee('jan.tajny@example.test')
            ->assertDontSee('500 600 700')
            ->assertDontSee('Klient prosi o kontakt po godzinie 16.')
            ->assertDontSee('Tajna treść wiadomości')
            ->assertDontSee('Kwalifikacja')
            ->assertDontSee('Historia kwalifikacji')
            ->assertDontSee('Dalszy przebieg')
            ->assertDontSee('Zakwalifikowany')
            ->assertDontSee('Tajny Jan / Bank Testowy');

        $this
            ->actingAs($user)
            ->get(WebsiteLeadResource::getUrl('edit', ['record' => $lead], panel: 'crm'))
            ->assertForbidden();
    }

    public function test_marketing_leads_page_ignores_legacy_column_manager_session_state(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $user->assignRole(Role::findOrCreate(MarketingAgencyAccess::ROLE, 'web'));

        Lead::create([
            'name' => 'Jan Tajny',
            'email' => 'jan.tajny@example.test',
            'phone' => '500 600 700',
            'attribution_channel' => 'google_ads',
        ]);

        $columnSessionKey = 'tables.'.md5(WebsiteListLeads::class).'_columns';

        $this
            ->withSession([
                $columnSessionKey => [
                    ['name' => 'legacy-column-without-type'],
                ],
            ])
            ->actingAs($user)
            ->get(WebsiteLeadResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Leady')
            ->assertSee(MarketingAgencyAccess::hiddenValue())
            ->assertDontSee('jan.tajny@example.test');
    }

    public function test_crm_leads_table_has_type_column_and_filter(): void
    {
        $user = $this->makeSuperAdmin();

        $formLead = Lead::create([
            'name' => 'Lead z formularza',
            'email' => 'formularz@example.test',
            'phone' => '500 100 100',
            'lead_type' => LeadTypes::FORM,
        ]);

        $emailLead = Lead::create([
            'name' => 'Lead z maila',
            'email' => 'mail@example.test',
            'phone' => '500 200 200',
            'lead_type' => LeadTypes::EMAIL,
        ]);

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(WebsiteListLeads::class)
            ->assertTableColumnExists('lead_type')
            ->assertTableFilterExists('lead_type')
            ->filterTable('lead_type', LeadTypes::EMAIL)
            ->assertCanSeeTableRecords([$emailLead])
            ->assertCanNotSeeTableRecords([$formLead]);
    }

    public function test_crm_dashboard_requiring_action_widget_respects_lawyer_referat(): void
    {
        $user = $this->makeSuperAdmin();
        $user->forceFill(['is_lawyer' => true])->save();

        $otherLawyer = User::factory()->create([
            'is_active' => true,
            'is_lawyer' => true,
        ]);

        $myMatter = CHFPotentialMatter::create([
            'label' => 'Moja sprawa do działania',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $myMatter->forceFill([
            'next_action_key' => 'follow_up_after_analysis',
            'next_action_due_at' => now()->subDay()->toDateString(),
            'next_action_reason' => 'Po analizie nie odnotowano odpowiedzi klienta.',
        ])->save();

        $otherMatter = CHFPotentialMatter::create([
            'label' => 'Cudza sprawa do działania',
            'lawyer_id' => $otherLawyer->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $otherMatter->forceFill([
            'next_action_key' => 'follow_up_after_analysis',
            'next_action_due_at' => now()->subDay()->toDateString(),
            'next_action_reason' => 'Po analizie nie odnotowano odpowiedzi klienta.',
        ])->save();

        $futureMatter = CHFPotentialMatter::create([
            'label' => 'Moja sprawa z przyszłym działaniem',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $futureMatter->forceFill([
            'next_action_key' => 'follow_up_after_analysis',
            'next_action_due_at' => now()->addDay()->toDateString(),
            'next_action_reason' => 'Jeszcze nie czas.',
        ])->save();

        $newMatter = CHFPotentialMatter::create([
            'label' => 'Nowa sprawa do weryfikacji',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        StageManager::setCurrentStage(
            $newMatter,
            app(PotentialMatterWorkflowService::class)->stageForKey(PotentialMatterWorkflowService::NEW_CONTRACT_STAGE),
        );

        $this->assertSame(
            PotentialMatterWorkflowService::REVIEW_NEW_POTENTIAL_MATTER,
            $newMatter->refresh()->next_action_key,
        );
        $this->assertSame(now()->toDateString(), $newMatter->next_action_due_at?->toDateString());

        $this->assignPartnerRole($user);

        $this->actingAs($user);

        Filament::setCurrentPanel('crm');

        Livewire::test(PotentialMattersRequiringActionWidget::class)
            ->loadTable()
            ->assertTableFilterExists('scopeMine')
            ->assertTableFilterVisible('scopeMine')
            ->assertSet('tableFilters.scopeMine.isActive', true)
            ->assertSee('Moja sprawa do działania')
            ->assertSee('Nowa sprawa do weryfikacji')
            ->assertSee('Zweryfikuj nową sprawę')
            ->assertDontSee('Status')
            ->assertDontSee('Otwórz')
            ->assertDontSee('Cudza sprawa do działania')
            ->assertDontSee('Moja sprawa z przyszłym działaniem');
    }

    public function test_crm_lead_stats_widget_uses_dashboard_date_range(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user);

        $julyLead = Lead::create([
            'name' => 'Lipiec Nowy',
            'email' => 'lipiec-nowy@example.test',
            'phone' => '500 100 100',
            'message' => 'Lead z lipca.',
        ]);
        $julyLead->forceFill([
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00',
        ])->save();

        $qualifiedJulyLead = Lead::create([
            'name' => 'Lipiec Zakwalifikowany',
            'email' => 'lipiec-zakwalifikowany@example.test',
            'phone' => '500 200 200',
            'message' => 'Drugi lead z lipca.',
            'status' => LeadStatuses::QUALIFIED,
        ]);
        $qualifiedJulyLead->forceFill([
            'created_at' => '2026-07-12 10:00:00',
            'updated_at' => '2026-07-12 10:00:00',
        ])->save();
        $retainedMatter = Matter::create([
            'label' => 'Sprawa z leada lipcowego',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_matter' => true,
            'start' => '2026-07-20',
        ]);
        $qualifiedJulyLead->forceFill([
            'potential_matter_id' => $retainedMatter->getKey(),
        ])->save();

        $juneLead = Lead::create([
            'name' => 'Czerwiec',
            'email' => 'czerwiec@example.test',
            'phone' => '500 300 300',
            'message' => 'Lead spoza zakresu.',
        ]);
        $juneLead->forceFill([
            'created_at' => '2026-06-15 10:00:00',
            'updated_at' => '2026-06-15 10:00:00',
        ])->save();

        $widget = app(LeadStatsWidget::class);
        $widget->pageFilters = [
            'leadDateRange' => '2026-07-01 - 2026-07-31',
        ];

        $statsMethod = new \ReflectionMethod($widget, 'getStats');
        $statsMethod->setAccessible(true);

        $stats = $statsMethod->invoke($widget);

        $this->assertCount(5, $stats);
        $this->assertSame('2', $stats[0]->getValue());
        $this->assertSame('1', $stats[2]->getValue());
        $this->assertSame('1', $stats[4]->getValue());
        $this->assertSame('+100%', $stats[0]->getDescription());
        $this->assertSame('heroicon-m-arrow-trending-up', $stats[0]->getDescriptionIcon());
        $this->assertSame('success', $stats[0]->getDescriptionColor());
        $this->assertSame('bez zmian', $stats[1]->getDescription());
        $this->assertSame('+100%', $stats[4]->getDescription());

        $widgetWithoutFilters = app(LeadStatsWidget::class);
        $widgetWithoutFilters->pageFilters = null;
        $defaultStats = $statsMethod->invoke($widgetWithoutFilters);

        $this->assertSame(
            '+100%',
            $defaultStats[0]->getDescription(),
        );
    }

    public function test_crm_lead_stats_widget_filters_by_currency_lead_type_and_source(): void
    {
        $user = $this->makeSuperAdmin();

        $chfFormLead = Lead::create([
            'name' => 'CHF Formularz',
            'email' => 'chf-formularz@example.test',
            'phone' => '500 400 100',
            'lead_type' => LeadTypes::FORM,
            'credit_currency' => 'CHF',
            'message' => 'Lead CHF z formularza.',
        ]);
        $chfFormLead->forceFill([
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00',
        ])->save();

        $chfEmailLead = Lead::create([
            'name' => 'CHF E-mail',
            'email' => 'chf-email@example.test',
            'phone' => '500 400 200',
            'lead_type' => LeadTypes::EMAIL,
            'credit_currency' => 'CHF',
            'attribution_channel' => 'google_ads',
            'status' => LeadStatuses::QUALIFIED,
            'message' => 'Lead CHF z e-maila.',
        ]);
        $chfEmailLead->forceFill([
            'created_at' => '2026-07-10 10:00:00',
            'updated_at' => '2026-07-10 10:00:00',
        ])->save();
        $retainedMatter = Matter::create([
            'label' => 'Sprawa z filtrowanego leada',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_matter' => true,
            'start' => '2026-07-20',
        ]);
        $chfEmailLead->forceFill([
            'potential_matter_id' => $retainedMatter->getKey(),
        ])->save();

        foreach ([1, 2] as $index) {
            $previousChfEmailLead = Lead::create([
                'name' => 'Poprzedni CHF E-mail '.$index,
                'email' => 'poprzedni-chf-email-'.$index.'@example.test',
                'phone' => '500 400 20'.$index,
                'lead_type' => LeadTypes::EMAIL,
                'credit_currency' => 'CHF',
                'attribution_channel' => 'google_ads',
                'message' => 'Lead z poprzedniego okresu.',
            ]);
            $previousChfEmailLead->forceFill([
                'created_at' => '2026-06-0'.(5 + $index).' 10:00:00',
                'updated_at' => '2026-06-0'.(5 + $index).' 10:00:00',
            ])->save();
        }

        $chfEmailMetaLead = Lead::create([
            'name' => 'CHF E-mail Meta',
            'email' => 'chf-email-meta@example.test',
            'phone' => '500 400 250',
            'lead_type' => LeadTypes::EMAIL,
            'credit_currency' => 'CHF',
            'attribution_channel' => 'meta_ads',
            'status' => LeadStatuses::REJECTED,
            'message' => 'Lead CHF z e-maila z innego źródła.',
        ]);
        $chfEmailMetaLead->forceFill([
            'created_at' => '2026-07-11 10:00:00',
            'updated_at' => '2026-07-11 10:00:00',
        ])->save();

        $eurEmailLead = Lead::create([
            'name' => 'EUR E-mail',
            'email' => 'eur-email@example.test',
            'phone' => '500 400 300',
            'lead_type' => LeadTypes::EMAIL,
            'credit_currency' => 'EUR',
            'status' => LeadStatuses::QUALIFIED,
            'message' => 'Lead EUR z e-maila.',
        ]);
        $eurEmailLead->forceFill([
            'created_at' => '2026-07-12 10:00:00',
            'updated_at' => '2026-07-12 10:00:00',
        ])->save();

        $chfPhoneLead = Lead::create([
            'name' => 'CHF Telefon',
            'email' => 'chf-telefon@example.test',
            'phone' => '500 400 400',
            'lead_type' => LeadTypes::PHONE,
            'credit_currency' => 'CHF',
            'status' => LeadStatuses::REJECTED,
            'message' => 'Lead CHF z telefonu.',
        ]);
        $chfPhoneLead->forceFill([
            'created_at' => '2026-07-15 10:00:00',
            'updated_at' => '2026-07-15 10:00:00',
        ])->save();

        $widget = app(LeadStatsWidget::class);
        $widget->pageFilters = [
            'leadDateRange' => '2026-07-01 - 2026-07-31',
            'leadCurrency' => 'CHF',
            'leadType' => LeadTypes::EMAIL,
            'leadSource' => 'google_ads',
        ];

        $statsMethod = new \ReflectionMethod($widget, 'getStats');
        $statsMethod->setAccessible(true);

        $stats = $statsMethod->invoke($widget);

        $this->assertSame('1', $stats[0]->getValue());
        $this->assertSame('0', $stats[1]->getValue());
        $this->assertSame('1', $stats[2]->getValue());
        $this->assertSame('0', $stats[3]->getValue());
        $this->assertSame('1', $stats[4]->getValue());
        $this->assertSame('-50%', $stats[0]->getDescription());
        $this->assertSame('heroicon-m-arrow-trending-down', $stats[0]->getDescriptionIcon());
        $this->assertSame('danger', $stats[0]->getDescriptionColor());
    }

    public function test_crm_lead_stats_widget_accepts_year_preset_labels(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user);

        $currentYearDate = now()->startOfYear()->addDays(4)->setTime(10, 0);
        $previousYearDate = now()->subYearNoOverflow()->startOfYear()->addDays(4)->setTime(10, 0);
        $olderDate = now()->subYearsNoOverflow(2)->startOfYear()->addDays(4)->setTime(10, 0);

        $currentYearLead = Lead::create([
            'name' => 'Biezacy rok',
            'email' => 'biezacy-rok@example.test',
            'phone' => '500 500 100',
            'message' => 'Lead z biezacego roku.',
        ]);
        $currentYearLead->forceFill([
            'created_at' => $currentYearDate->format('Y-m-d H:i:s'),
            'updated_at' => $currentYearDate->format('Y-m-d H:i:s'),
        ])->save();

        $previousYearLead = Lead::create([
            'name' => 'Poprzedni rok',
            'email' => 'poprzedni-rok@example.test',
            'phone' => '500 500 200',
            'status' => LeadStatuses::QUALIFIED,
            'message' => 'Lead z poprzedniego roku.',
        ]);
        $previousYearLead->forceFill([
            'created_at' => $previousYearDate->format('Y-m-d H:i:s'),
            'updated_at' => $previousYearDate->format('Y-m-d H:i:s'),
        ])->save();

        $olderLead = Lead::create([
            'name' => 'Starszy rok',
            'email' => 'starszy-rok@example.test',
            'phone' => '500 500 300',
            'message' => 'Lead spoza presetow rocznych.',
        ]);
        $olderLead->forceFill([
            'created_at' => $olderDate->format('Y-m-d H:i:s'),
            'updated_at' => $olderDate->format('Y-m-d H:i:s'),
        ])->save();

        $statsMethod = new \ReflectionMethod(LeadStatsWidget::class, 'getStats');
        $statsMethod->setAccessible(true);

        $currentYearWidget = app(LeadStatsWidget::class);
        $currentYearWidget->pageFilters = [
            'leadDateRange' => 'Ten rok',
        ];

        $currentYearStats = $statsMethod->invoke($currentYearWidget);

        $this->assertSame('1', $currentYearStats[0]->getValue());
        $this->assertSame('1', $currentYearStats[1]->getValue());
        $this->assertSame(
            'bez zmian',
            $currentYearStats[0]->getDescription(),
        );

        $previousYearWidget = app(LeadStatsWidget::class);
        $previousYearWidget->pageFilters = [
            'leadDateRange' => 'Poprzedni rok',
        ];

        $previousYearStats = $statsMethod->invoke($previousYearWidget);

        $this->assertSame('1', $previousYearStats[0]->getValue());
        $this->assertSame('1', $previousYearStats[2]->getValue());
        $this->assertSame(
            'bez zmian',
            $previousYearStats[0]->getDescription(),
        );
    }

    public function test_marketing_user_can_export_anonymous_crm_lead_stats_csv(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_crm_panel', LeadStatsService::EXPORT_PERMISSION] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo(['access_crm_panel', LeadStatsService::EXPORT_PERMISSION]);

        $matchingLead = Lead::create([
            'name' => 'Jan Tajny',
            'email' => 'jan.tajny@example.test',
            'phone' => '500 400 500',
            'lead_type' => LeadTypes::EMAIL,
            'credit_currency' => 'CHF',
            'status' => LeadStatuses::QUALIFIED,
            'attribution_channel' => 'google_ads',
            'attribution_source' => 'google',
            'attribution_medium' => 'cpc',
            'attribution_campaign' => 'summer_campaign',
            'message' => 'Umowa nr ABC/123/2008.',
        ]);
        $matchingLead->forceFill([
            'created_at' => '2026-07-05 10:00:00',
            'updated_at' => '2026-07-05 10:00:00',
        ])->save();
        $retainedMatter = Matter::create([
            'label' => 'Sprawa z eksportowanego leada',
            'lawyer_id' => $user->id,
            'category' => 'CHF',
            'is_matter' => true,
            'start' => '2026-07-20',
        ]);
        $matchingLead->forceFill([
            'potential_matter_id' => $retainedMatter->getKey(),
        ])->save();

        $otherLead = Lead::create([
            'name' => 'Anna Inna',
            'email' => 'anna.inna@example.test',
            'phone' => '500 400 600',
            'lead_type' => LeadTypes::FORM,
            'credit_currency' => 'EUR',
            'status' => LeadStatuses::NEW,
            'message' => 'Nie powinno trafić do eksportu filtrowanego.',
        ]);
        $otherLead->forceFill([
            'created_at' => '2026-07-10 10:00:00',
            'updated_at' => '2026-07-10 10:00:00',
        ])->save();

        $otherSourceLead = Lead::create([
            'name' => 'Marek Meta',
            'email' => 'marek.meta@example.test',
            'phone' => '500 400 700',
            'lead_type' => LeadTypes::EMAIL,
            'credit_currency' => 'CHF',
            'status' => LeadStatuses::QUALIFIED,
            'attribution_channel' => 'meta_ads',
            'attribution_source' => 'facebook',
            'attribution_medium' => 'paid_social',
            'attribution_campaign' => 'summer_campaign',
            'message' => 'Powinien wypasc z eksportu po filtrze zrodla.',
        ]);
        $otherSourceLead->forceFill([
            'created_at' => '2026-07-15 10:00:00',
            'updated_at' => '2026-07-15 10:00:00',
        ])->save();

        $response = $this->actingAs($user)->get('http://crm.preda-app.test/statystyki-leadow/export?'.http_build_query([
            'leadDateRange' => '2026-07-01 - 2026-07-31',
            'leadCurrency' => 'CHF',
            'leadType' => LeadTypes::EMAIL,
            'leadSource' => 'google_ads',
        ]));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Leady razem', $csv);
        $this->assertStringContainsString('Zakwalifikowane', $csv);
        $this->assertStringContainsString('Zlecone sprawy', $csv);
        $this->assertStringContainsString('Kredyt CHF', $csv);
        $this->assertStringContainsString('E-mail', $csv);
        $this->assertStringContainsString('Google Ads', $csv);
        $this->assertStringContainsString('summer_campaign', $csv);
        $this->assertStringNotContainsString('Meta Ads', $csv);
        $this->assertStringNotContainsString('Jan Tajny', $csv);
        $this->assertStringNotContainsString('jan.tajny@example.test', $csv);
        $this->assertStringNotContainsString('500 400 500', $csv);
        $this->assertStringNotContainsString('Umowa nr ABC/123/2008', $csv);
        $this->assertStringNotContainsString('Anna Inna', $csv);
        $this->assertStringNotContainsString('anna.inna@example.test', $csv);
        $this->assertStringNotContainsString('Marek Meta', $csv);
        $this->assertStringNotContainsString('marek.meta@example.test', $csv);
    }

    public function test_crm_lead_stats_export_requires_export_permission(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        Permission::firstOrCreate([
            'name' => 'access_crm_panel',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo('access_crm_panel');

        $this
            ->actingAs($user)
            ->get('http://crm.preda-app.test/statystyki-leadow/export')
            ->assertForbidden();
    }

    public function test_crm_lead_stats_are_visible_for_marketing_export_permission(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_crm_panel', LeadStatsService::EXPORT_PERMISSION] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo(['access_crm_panel', LeadStatsService::EXPORT_PERMISSION]);

        $this->actingAs($user);

        $this->assertTrue(LeadStatsWidget::canView());

        $this
            ->get(CrmDashboard::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Statystyki leadów')
            ->assertSee('Eksportuj')
            ->assertSee('Okres statystyk leadów')
            ->assertSee('Własny zakres')
            ->assertSee('Źródło');
    }

    public function test_potential_matter_does_not_show_accepted_matter_relation_managers(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user);

        $potentialMatterRelations = CrmPotentialMatterResource::getRelations();

        $this->assertNotContains(LettersRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(LawsuitsRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(PaymentsRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(OffersRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(ClientMessagesRelationManager::class, $potentialMatterRelations);
        $this->assertContains(DealsRelationManager::class, $potentialMatterRelations);
        $this->assertContains(ActivitiesRelationManager::class, $potentialMatterRelations);
        $this->assertFalse(collect($potentialMatterRelations)->contains(
            fn (mixed $relation): bool => $relation instanceof RelationGroup,
        ));

        $this->assignPartnerRole($user);

        $this->assertContains(ClientMessagesRelationManager::class, CrmPotentialMatterResource::getRelations());

        $acceptedMatterRelations = KancelariaCHFMatterResource::getRelations();

        $this->assertContains(LettersRelationManager::class, $acceptedMatterRelations);
        $this->assertContains(LawsuitsRelationManager::class, $acceptedMatterRelations);
        $this->assertContains(ActivitiesRelationManager::class, $acceptedMatterRelations);
        $this->assertContains(ActivitiesRelationManager::class, KancelariaCHFPaymentMatterResource::getRelations());
        $this->assertContains(ActivitiesRelationManager::class, KancelariaBankMatterResource::getRelations());
    }

    public function test_non_partner_cannot_view_crm_client_messages_relation_manager(): void
    {
        $user = $this->makeSuperAdmin();
        $potentialMatter = CHFPotentialMatter::create([
            'label' => 'Potencjalna sprawa bez dostępu do wiadomości',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        $this->actingAs($user);

        $this->assertFalse(ClientMessagesRelationManager::canViewForRecord(
            $potentialMatter,
            EditCHFPotentialMatter::class,
        ));
    }

    public function test_activity_resource_defaults_to_my_referat_and_counts_unread_notes(): void
    {
        $user = $this->makeSuperAdmin();
        $user->forceFill(['is_lawyer' => true])->save();

        $otherLawyer = User::factory()->create([
            'is_active' => true,
            'is_lawyer' => true,
        ]);
        $author = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $myMatter = CHFMatter::create([
            'label' => 'Moja sprawa z notatkami',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => true,
        ]);
        $otherMatter = CHFMatter::create([
            'label' => 'Cudza sprawa z notatkami',
            'lawyer_id' => $otherLawyer->getKey(),
            'category' => 'CHF',
            'is_matter' => true,
        ]);

        $this->actingAs($user);

        $myUnreadActivity = Activity::create([
            'date' => now()->toDateString(),
            'description' => 'Nieprzeczytana notatka z mojego referatu',
            'matter_id' => $myMatter->getKey(),
            'created_by' => $author->getKey(),
        ]);
        $myUnreadActivity->forceFill([
            'created_at' => now()->setTime(13, 45, 30),
            'updated_at' => now()->setTime(13, 45, 30),
        ])->save();

        $myOwnActivity = Activity::create([
            'date' => now()->toDateString(),
            'description' => 'Moja własna notatka',
            'matter_id' => $myMatter->getKey(),
        ]);
        $otherUnreadActivity = Activity::create([
            'date' => now()->toDateString(),
            'description' => 'Nieprzeczytana notatka z cudzego referatu',
            'matter_id' => $otherMatter->getKey(),
            'created_by' => $author->getKey(),
        ]);

        Filament::setCurrentPanel('kancelaria');

        $this->assertSame($user->getKey(), $myOwnActivity->created_by);
        $this->assertSame('1', ActivityResource::getNavigationBadge());

        $nonLawyer = $this->makeSuperAdmin();
        $this->actingAs($nonLawyer);
        $this->assertNull(ActivityResource::getNavigationBadge());

        $this->actingAs($user);

        Livewire::test(ListActivities::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$myUnreadActivity, $myOwnActivity])
            ->assertCanNotSeeTableRecords([$otherUnreadActivity])
            ->mountTableAction('edit', $myUnreadActivity);

        $this->assertNull(ActivityResource::getNavigationBadge());
        $this->assertTrue($myUnreadActivity->refresh()->isReadBy($user));

        Livewire::test(ActivitiesRelationManager::class, [
            'ownerRecord' => $myMatter,
            'pageClass' => EditCHFMatter::class,
        ])
            ->loadTable()
            ->assertSee(now()->format('Y-m-d').' 13:45')
            ->assertDontSee('13:45:30');
    }

    public function test_potential_matter_deals_do_not_hide_drafts_by_default(): void
    {
        $user = $this->makeSuperAdmin();
        $potentialMatter = CHFPotentialMatter::create([
            'label' => 'Potencjalna sprawa ze zleceniami',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);
        $acceptedMatter = CHFMatter::create([
            'label' => 'Przyjęta sprawa ze zleceniami',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => true,
        ]);
        $potentialDraftDeal = $this->createDeal($potentialMatter->getKey(), 'Szkic potencjalnej sprawy', true);
        $potentialFinalDeal = $this->createDeal($potentialMatter->getKey(), 'Zlecenie potencjalnej sprawy', false);
        $acceptedDraftDeal = $this->createDeal($acceptedMatter->getKey(), 'Szkic przyjętej sprawy', true);
        $acceptedFinalDeal = $this->createDeal($acceptedMatter->getKey(), 'Zlecenie przyjętej sprawy', false);

        $this->actingAs($user);

        Livewire::test(DealsRelationManager::class, [
            'ownerRecord' => $potentialMatter,
            'pageClass' => EditCHFPotentialMatter::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords([$potentialDraftDeal, $potentialFinalDeal]);

        Livewire::test(DealsRelationManager::class, [
            'ownerRecord' => $acceptedMatter,
            'pageClass' => EditCHFMatter::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords([$acceptedFinalDeal])
            ->assertCanNotSeeTableRecords([$acceptedDraftDeal]);
    }

    public function test_potential_matter_edit_shows_source_lead_form_data(): void
    {
        $user = $this->makeSuperAdmin();
        $potentialMatter = CHFPotentialMatter::create([
            'label' => 'Kowalski Jan / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        Lead::create([
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
            'additional_info' => 'Klient prosi o kontakt po godzinie 16.',
            'message' => 'Zgłoszenie testowe.',
            'potential_matter_id' => $potentialMatter->getKey(),
            'potential_matter_created_at' => now(),
            'potential_matter_created_by' => $user->getKey(),
        ]);

        $this->actingAs($user)
            ->get(CrmPotentialMatterResource::getUrl('edit', ['record' => $potentialMatter], panel: 'crm'))
            ->assertOk()
            ->assertDontSee('Pliki przesłane przez klienta')
            ->assertSee('Dane z formularza leada')
            ->assertSee('Jan Kowalski')
            ->assertSee('jan@example.test')
            ->assertSee('500 600 700')
            ->assertSee('67-200, powiat głogowski, województwo dolnośląskie')
            ->assertSee('Bank Testowy')
            ->assertSee('Klient prosi o kontakt po godzinie 16.');
    }

    public function test_crm_potential_matter_shows_uploaded_lead_files_above_source_form_data(): void
    {
        Storage::fake('local');

        $user = $this->makeSuperAdmin();
        $potentialMatter = CHFPotentialMatter::create([
            'label' => 'Nowak Anna / mBank',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        Storage::disk('local')->put('umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GW.pdf', '%PDF-test');
        Storage::disk('local')->put('umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GX.pdf', '%PDF-regulamin');

        Lead::create([
            'name' => 'Anna Nowak',
            'email' => 'anna@example.test',
            'phone' => '501 600 700',
            'bank' => 'mBank',
            'has_contract' => true,
            'files' => [
                'umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GW.pdf',
                'umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GX.pdf',
            ],
            'files_names' => [
                'umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GW.pdf' => 'Umowa kredytowa Anna Nowak.pdf',
                'umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GX.pdf' => 'Regulamin kredytu Anna Nowak.pdf',
            ],
            'potential_matter_id' => $potentialMatter->getKey(),
            'potential_matter_created_at' => now(),
            'potential_matter_created_by' => $user->getKey(),
        ]);

        $firstFileUrl = route('crm.potential-matter.lead-file.download', [
            'matter' => $potentialMatter,
            'fileIndex' => 0,
        ]);

        $this->actingAs($user)
            ->get(CrmPotentialMatterResource::getUrl('edit', ['record' => $potentialMatter], panel: 'crm'))
            ->assertOk()
            ->assertSeeInOrder([
                'Pliki przesłane przez klienta',
                'Umowa kredytowa Anna Nowak.pdf',
                'Regulamin kredytu Anna Nowak.pdf',
                'Dane z formularza leada',
            ])
            ->assertSee($firstFileUrl, false);

        $this->actingAs($user)
            ->get($firstFileUrl)
            ->assertOk()
            ->assertDownload('Umowa kredytowa Anna Nowak.pdf');
    }

    public function test_crm_potential_matter_does_not_render_download_link_for_missing_lead_file(): void
    {
        Storage::fake('local');

        $user = $this->makeSuperAdmin();
        $potentialMatter = CHFPotentialMatter::create([
            'label' => 'Brakujący plik / mBank',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
        ]);

        Lead::create([
            'name' => 'Brakujący Plik',
            'email' => 'missing-file@example.test',
            'phone' => '501 600 701',
            'bank' => 'mBank',
            'has_contract' => true,
            'files' => [
                'umowy-do-analizy/test/01KXPH1GAKDNXZMCVV3265H9GY.pdf',
            ],
            'potential_matter_id' => $potentialMatter->getKey(),
            'potential_matter_created_at' => now(),
            'potential_matter_created_by' => $user->getKey(),
        ]);

        $fileUrl = route('crm.potential-matter.lead-file.download', [
            'matter' => $potentialMatter,
            'fileIndex' => 0,
        ]);

        $this->actingAs($user)
            ->get(CrmPotentialMatterResource::getUrl('edit', ['record' => $potentialMatter], panel: 'crm'))
            ->assertOk()
            ->assertSee('Pliki przesłane przez klienta')
            ->assertSee('Dokument 1')
            ->assertSee('Niedostępny')
            ->assertDontSee($fileUrl, false);

        $this->actingAs($user)
            ->get($fileUrl)
            ->assertNotFound();
    }

    public function test_crm_resources_are_not_registered_in_the_kancelaria_panel(): void
    {
        $this->assertTrue(Route::has('filament.crm.resources.potencjalne.index'));
        $this->assertFalse(Route::has('filament.crm.resources.szanse.index'));

        $this->assertFalse(Route::has('filament.kancelaria.resources.szanse.index'));
        $this->assertFalse(Route::has('filament.kancelaria.resources.potencjalne.index'));
    }

    public function test_legacy_crm_chance_paths_redirect_to_potential_matters(): void
    {
        $this->get('http://crm.preda-app.test/szanse/abc/edit?activeRelationManager=0')
            ->assertRedirect('/potencjalne/abc/edit?activeRelationManager=0');
    }

    public function test_acquisition_resources_are_registered_in_crm_not_cms(): void
    {
        $this->assertTrue(Route::has('filament.crm.resources.leady.index'));
        $this->assertTrue(Route::has('filament.cms.resources.pracownicy.index'));

        $this->assertFalse(Route::has('filament.crm.resources.umowy-do-analizy.index'));
        $this->assertFalse(Route::has('filament.cms.resources.leady.index'));
        $this->assertFalse(Route::has('filament.cms.resources.umowy-do-analizy.index'));
        $this->assertFalse(Route::has('filament.crm.resources.zapytania-ofertowe.index'));
        $this->assertFalse(Route::has('filament.cms.resources.zapytania-ofertowe.index'));
    }

    public function test_legacy_analysis_lead_paths_redirect_to_leady(): void
    {
        $this->get('http://crm.preda-app.test/umowy-do-analizy/123?table=wide')
            ->assertRedirect('/leady/123?table=wide');
    }

    public function test_employee_user_management_is_not_registered_in_the_crm_panel(): void
    {
        $this->assertFalse(Route::has('filament.crm.resources.pracownicy.index'));
    }

    public function test_shield_role_management_is_only_registered_in_kancelaria_panel(): void
    {
        $user = $this->makeSuperAdmin();

        $this->assertTrue(Route::has('filament.kancelaria.resources.shield.roles.index'));
        $this->assertFalse(Route::has('filament.crm.resources.shield.roles.index'));
        $this->assertFalse(Route::has('filament.cms.resources.shield.roles.index'));

        $this->actingAs($user)
            ->get('http://crm.preda-app.test/shield/roles')
            ->assertNotFound();

        $this->actingAs($user)
            ->get('http://cms.preda-app.test/shield/roles')
            ->assertNotFound();
    }

    public function test_cms_team_profiles_do_not_allow_user_lifecycle_actions(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->assertFalse(Route::has('filament.cms.resources.pracownicy.create'));
        $this->assertFalse(WebsiteUserResource::canCreate());
        $this->assertFalse(WebsiteUserResource::canDelete($user));
        $this->assertFalse(WebsiteUserResource::canDeleteAny());
    }

    public function test_non_admin_panel_access_requires_explicit_panel_permission(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        $this->assertFalse($user->canAccessPredaPanel('crm'));

        Permission::firstOrCreate([
            'name' => 'access_crm_panel',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo('access_crm_panel');

        $this->assertTrue($user->canAccessPredaPanel('crm'));
        $this->assertFalse($user->canAccessPredaPanel('cms'));

        $this->actingAs($user)
            ->get('http://crm.preda-app.test/')
            ->assertRedirect(CrmPotentialMatterResource::getUrl(panel: 'crm'));

        $this->actingAs($user)
            ->get('http://cms.preda-app.test/')
            ->assertForbidden();
    }

    public function test_crm_and_cms_access_does_not_grant_kancelaria_access(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_crm_panel', 'access_cms_panel'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo([
            'access_crm_panel',
            'access_cms_panel',
        ]);

        $this->assertTrue($user->canAccessPredaPanel('crm'));
        $this->assertTrue($user->canAccessPredaPanel('cms'));
        $this->assertFalse($user->canAccessPredaPanel('kancelaria'));

        $this->actingAs($user)
            ->get('http://crm.preda-app.test/')
            ->assertRedirect(CrmPotentialMatterResource::getUrl(panel: 'crm'));

        $this->actingAs($user)
            ->get('http://cms.preda-app.test/')
            ->assertRedirect(SentenceResource::getUrl(panel: 'cms'));

        $this->actingAs($user)
            ->get('http://ewidencja.preda-app.test/')
            ->assertForbidden();
    }

    public function test_an_active_super_admin_can_open_the_post_create_form(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get(PostResource::getUrl('create', panel: 'cms'))
            ->assertOk();
    }

    public function test_an_active_super_admin_can_open_a_lead_record_view(): void
    {
        $user = $this->makeSuperAdmin();
        $lead = Lead::create([
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
            'additional_info' => 'Klient prosi o kontakt po godzinie 16.',
            'message' => 'Zgłoszenie testowe.',
        ]);
        $matter = CHFPotentialMatter::create([
            'label' => 'Kowalski Jan / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'userinfo' => [],
            'is_matter' => false,
        ]);
        $lead->forceFill([
            'potential_matter_id' => $matter->getKey(),
            'potential_matter_created_at' => now(),
            'potential_matter_created_by' => $user->getKey(),
        ])->save();
        $lead->qualify(userId: $user->getKey(), note: 'Utworzono potencjalną sprawę.');

        $this->actingAs($user)
            ->get(WebsiteLeadResource::getUrl('view', ['record' => $lead], panel: 'crm'))
            ->assertOk()
            ->assertDontSee('<span data-filament-table-width-page hidden></span>', false)
            ->assertSeeInOrder([
                'Źródło leada',
                'Dane z formularza',
                'Kwalifikacja',
                'Dalszy przebieg',
            ])
            ->assertSee('Zakwalifikowany')
            ->assertSee('Klient prosi o kontakt po godzinie 16.')
            ->assertSee('Lokalizacja')
            ->assertSee('67-200, powiat głogowski, województwo dolnośląskie');
    }

    public function test_the_sentence_content_generator_module_is_disabled(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get('http://cms.preda-app.test/szablony-generatora-wyrokow')
            ->assertNotFound();
    }

    public function test_an_inactive_super_admin_is_forbidden_from_accessing_filament_panels(): void
    {
        $user = $this->makeSuperAdmin(isActive: false);

        $this->actingAs($user)
            ->get('http://ewidencja.preda-app.test/')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('http://cms.preda-app.test/')
            ->assertForbidden();
    }

    private function makeSuperAdmin(bool $isActive = true): User
    {
        $role = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'is_active' => $isActive,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function assignPartnerRole(User $user): void
    {
        $role = Role::findOrCreate(ClientAcquisitionAccess::ROLE, 'web');

        $user->forceFill([
            'is_employee' => true,
        ])->save();

        $user->assignRole($role);
    }

    private function createPortalMatterFixture(): array
    {
        $lawyer = User::factory()->create([
            'is_active' => true,
            'is_lawyer' => true,
        ]);

        $contact = Contact::create([
            'type' => 'person',
            'category' => 'Kredytobiorca',
            'first_name' => 'Jan',
            'last_name' => 'Portalowy',
            'label' => 'Jan Portalowy',
            'sort_name' => 'Portalowy Jan',
            'email' => 'jan.portalowy@example.test',
        ]);

        $otherContact = Contact::create([
            'type' => 'person',
            'category' => 'Kredytobiorca',
            'first_name' => 'Anna',
            'last_name' => 'Inna',
            'label' => 'Anna Inna',
            'sort_name' => 'Inna Anna',
            'email' => 'anna.inna@example.test',
        ]);

        $portalUser = PortalUser::create([
            'name' => 'Jan Portalowy',
            'email' => 'portal@example.test',
            'password' => 'password',
            'is_active' => true,
            'contact_id' => $contact->id,
        ]);

        $matter = CHFMatter::create([
            'label' => 'Portal matter',
            'lawyer_id' => $lawyer->id,
            'category' => 'CHF',
            'is_matter' => true,
        ]);

        $otherMatter = CHFMatter::create([
            'label' => 'Other portal matter',
            'lawyer_id' => $lawyer->id,
            'category' => 'CHF',
            'is_matter' => true,
        ]);

        ContactMatter::create([
            'matter_id' => $matter->id,
            'contact_id' => $contact->id,
            'receives_notifications' => true,
        ]);

        ContactMatter::create([
            'matter_id' => $otherMatter->id,
            'contact_id' => $otherContact->id,
            'receives_notifications' => true,
        ]);

        $letter = Letter::create([
            'label' => 'Portal letter',
            'date' => now()->toDateString(),
            'type' => 'in',
            'matter_id' => $matter->id,
            'files' => ['k2/portal/document.pdf'],
            'files_names' => ['k2/portal/document.pdf' => 'Dokument.pdf'],
        ]);

        $otherLetter = Letter::create([
            'label' => 'Other portal letter',
            'date' => now()->toDateString(),
            'type' => 'in',
            'matter_id' => $otherMatter->id,
            'files' => ['k2/portal/other.pdf'],
            'files_names' => ['k2/portal/other.pdf' => 'Inny.pdf'],
        ]);

        return [$portalUser, $matter, $letter, $otherMatter, $otherLetter];
    }

    private function createNavigationPosts(): void
    {
        Post::create([
            'title' => 'Blog entry',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'date' => now()->toDateString(),
            'slug' => 'blog-entry',
            'metatitle' => 'Blog entry',
            'metadescription' => 'Blog entry description',
            'is_published' => true,
            'category' => 'blog',
        ]);

        Post::create([
            'title' => 'Judgment entry',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'date' => now()->toDateString(),
            'slug' => 'judgment-entry',
            'metatitle' => 'Judgment entry',
            'metadescription' => 'Judgment entry description',
            'is_published' => true,
            'category' => 'orzecznictwo',
        ]);
    }

    private function createDeal(string $matterId, string $label, bool $isDraft): Deal
    {
        $deal = new Deal;
        $deal->forceFill([
            'label' => $label,
            'date' => now()->toDateString(),
            'matter_id' => $matterId,
            'is_draft' => $isDraft,
        ]);
        $deal->save();

        return $deal;
    }
}
