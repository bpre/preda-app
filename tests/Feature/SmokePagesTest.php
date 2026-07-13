<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource as CrmPotentialMatterResource;
use App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages\EditCHFPotentialMatter;
use App\Filament\Crm\Resources\CrmMailTemplateResource;
use App\Filament\Portal\Resources\CHFMatterResource as PortalCHFMatterResource;
use App\Filament\Portal\Resources\LetterResource as PortalLetterResource;
use App\Filament\Resources\CHFMatterResource as KancelariaCHFMatterResource;
use App\Filament\Resources\CHFMatterResource\Pages\EditCHFMatter;
use App\Filament\Resources\CHFMatterResource\RelationManagers\DealsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LawsuitsRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\LettersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\OffersRelationManager;
use App\Filament\Resources\CHFMatterResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\ContactResource as KancelariaContactResource;
use App\Filament\Resources\LetterResource as KancelariaLetterResource;
use App\Filament\Resources\TaskResource as KancelariaTaskResource;
use App\Filament\Resources\UserResource as KancelariaUserResource;
use App\Filament\Website\Resources\Leads\LeadResource as WebsiteLeadResource;
use App\Filament\Website\Resources\Posts\PostResource;
use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Filament\Website\Resources\Users\UserResource as WebsiteUserResource;
use App\Models\CHFMatter;
use App\Models\CHFPotentialMatter;
use App\Models\Contact;
use App\Models\ContactMatter;
use App\Models\Deal;
use App\Models\Letter;
use App\Models\PortalUser;
use App\Models\User;
use App\Models\Website\Lead;
use App\Models\Website\Post;
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
        $this->get('http://ewidencja.preda-app.test/login')->assertOk();
        $this->get('http://crm.preda-app.test/login')->assertOk();
        $this->get('http://cms.preda-app.test/login')->assertOk();
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
        $this->get('http://portal.preda-app.test/login')->assertOk();
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
            ->assertRedirect(WebsiteLeadResource::getUrl(panel: 'crm'));

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
            ->assertOk();

        $this->actingAs($user)
            ->get(KancelariaLetterResource::getUrl(panel: 'kancelaria'))
            ->assertOk();

        $this->actingAs($user)
            ->get(KancelariaTaskResource::getUrl(panel: 'kancelaria'))
            ->assertOk();
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
            ->assertSee('<span data-filament-table-width-page hidden></span>', false);

        $this->actingAs($user)
            ->get(WebsiteLeadResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('<span data-filament-table-width-page hidden></span>', false)
            ->assertSee('67-200 (dolnośląskie, głogowski)');

        $this->actingAs($user)
            ->get(CrmMailTemplateResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSee('Szablony maili');
    }

    public function test_crm_navigation_shows_leads_before_potential_matters(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get(WebsiteLeadResource::getUrl(panel: 'crm'))
            ->assertOk()
            ->assertSeeInOrder(['Leady', 'Potencjalne sprawy']);
    }

    public function test_crm_has_no_dashboard_and_redirects_home_to_leads_when_available(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_crm_panel', 'ViewAny:Lead'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo(['access_crm_panel', 'ViewAny:Lead']);

        $this->assertFalse(Route::has('filament.crm.pages.dashboard'));

        $this->actingAs($user)
            ->get('http://crm.preda-app.test/')
            ->assertRedirect(WebsiteLeadResource::getUrl(panel: 'crm'));
    }

    public function test_crm_home_redirects_to_potential_matters_when_user_cannot_view_leads(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
        ]);

        foreach (['access_crm_panel', 'view_any_c::h::f::potential::matter'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo(['access_crm_panel', 'view_any_c::h::f::potential::matter']);

        $this->actingAs($user)
            ->get('http://crm.preda-app.test/')
            ->assertRedirect(CrmPotentialMatterResource::getUrl(panel: 'crm'));
    }

    public function test_potential_matter_does_not_show_accepted_matter_relation_managers(): void
    {
        $potentialMatterRelations = CrmPotentialMatterResource::getRelations();

        $this->assertNotContains(LettersRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(LawsuitsRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(PaymentsRelationManager::class, $potentialMatterRelations);
        $this->assertNotContains(OffersRelationManager::class, $potentialMatterRelations);
        $this->assertContains(DealsRelationManager::class, $potentialMatterRelations);
        $this->assertFalse(collect($potentialMatterRelations)->contains(
            fn (mixed $relation): bool => $relation instanceof RelationGroup,
        ));

        $acceptedMatterRelations = KancelariaCHFMatterResource::getRelations();

        $this->assertContains(LettersRelationManager::class, $acceptedMatterRelations);
        $this->assertContains(LawsuitsRelationManager::class, $acceptedMatterRelations);
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
            ->assertSee('Dane z formularza leada')
            ->assertSee('Jan Kowalski')
            ->assertSee('jan@example.test')
            ->assertSee('500 600 700')
            ->assertSee('67-200, powiat głogowski, województwo dolnośląskie')
            ->assertSee('Bank Testowy')
            ->assertSee('Klient prosi o kontakt po godzinie 16.');
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
