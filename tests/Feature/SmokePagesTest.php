<?php

namespace Tests\Feature;

use App\Filament\Website\Resources\Leads\LeadResource;
use App\Filament\Website\Resources\Posts\PostResource;
use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Filament\Resources\CHFMatterResource as KancelariaCHFMatterResource;
use App\Filament\Resources\CHFPotentialMatterResource as CrmPotentialMatterResource;
use App\Filament\Resources\ContactResource as KancelariaContactResource;
use App\Filament\Resources\LeadResource as CrmLeadResource;
use App\Filament\Resources\LetterResource as KancelariaLetterResource;
use App\Filament\Resources\TaskResource as KancelariaTaskResource;
use App\Models\User;
use App\Models\Website\Lead;
use App\Models\Website\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_the_portal_login_page_is_accessible(): void
    {
        $this->get('http://portal.preda-app.test/login')->assertOk();
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

    public function test_an_active_super_admin_can_open_key_crm_resource_lists(): void
    {
        $user = $this->makeSuperAdmin();

        $this->actingAs($user)
            ->get(CrmLeadResource::getUrl(panel: 'crm'))
            ->assertOk();

        $this->actingAs($user)
            ->get(CrmPotentialMatterResource::getUrl(panel: 'crm'))
            ->assertOk();
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
            'message' => 'Zgłoszenie testowe.',
        ]);

        $this->actingAs($user)
            ->get(LeadResource::getUrl('view', ['record' => $lead], panel: 'cms'))
            ->assertOk()
            ->assertSee('Nowy lead');
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
}
