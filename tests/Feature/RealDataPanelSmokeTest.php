<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource as CrmPotentialMatterResource;
use App\Filament\Crm\Resources\LeadResource as CrmLeadResource;
use App\Filament\Resources\CHFMatterResource as KancelariaCHFMatterResource;
use App\Filament\Resources\ContactResource as KancelariaContactResource;
use App\Filament\Resources\LetterResource as KancelariaLetterResource;
use App\Filament\Resources\TaskResource as KancelariaTaskResource;
use App\Filament\Website\Resources\Leads\LeadResource as WebsiteLeadResource;
use App\Filament\Website\Resources\Posts\PostResource;
use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Models\CHFMatter;
use App\Models\Contact;
use App\Models\Lead as CrmLead;
use App\Models\User;
use App\Models\Website\Lead as WebsiteLead;
use App\Models\Website\Post;
use App\Models\Website\Sentence;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealDataPanelSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! filter_var(env('RUN_REAL_DATA_SMOKE', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Set RUN_REAL_DATA_SMOKE=1 to run checks against the local imported MySQL data.');
        }

        if (DB::connection()->getDatabaseName() !== 'preda_app_local_fresh') {
            $this->markTestSkipped('Real data smoke tests are scoped to preda_app_local_fresh.');
        }
    }

    public function test_real_data_panel_lists_and_key_records_render(): void
    {
        $this->actingAs($this->superAdmin());

        $this->get('http://ewidencja.preda-app.test/')->assertOk();
        $this->get('http://crm.preda-app.test/')->assertOk();
        $this->get(SentenceResource::getUrl(panel: 'cms'))->assertOk();

        $this->get(KancelariaContactResource::getUrl(panel: 'kancelaria'))->assertOk();
        $this->get(KancelariaCHFMatterResource::getUrl(panel: 'kancelaria'))->assertOk();
        $this->get(KancelariaLetterResource::getUrl(panel: 'kancelaria'))->assertOk();
        $this->get(KancelariaTaskResource::getUrl(panel: 'kancelaria'))->assertOk();

        $this->get(CrmLeadResource::getUrl(panel: 'crm'))->assertOk();
        $this->get(CrmPotentialMatterResource::getUrl(panel: 'crm'))->assertOk();
        $this->get(WebsiteLeadResource::getUrl(panel: 'crm'))->assertOk();

        $this->get(PostResource::getUrl(panel: 'cms'))->assertOk();
        $this->get(SentenceResource::getUrl(panel: 'cms'))->assertOk();

        $contact = Contact::query()->firstOrFail();
        $matter = CHFMatter::query()->where('is_matter', true)->firstOrFail();
        $crmLead = CrmLead::query()->firstOrFail();
        $websiteLead = WebsiteLead::query()->firstOrFail();
        $post = Post::query()->firstOrFail();
        $sentence = Sentence::query()->firstOrFail();

        $this->get(KancelariaContactResource::getUrl('edit', ['record' => $contact], panel: 'kancelaria'))->assertOk();
        $this->get(KancelariaCHFMatterResource::getUrl('edit', ['record' => $matter], panel: 'kancelaria'))->assertOk();
        $this->get(CrmLeadResource::getUrl('edit', ['record' => $crmLead], panel: 'crm'))->assertOk();
        $this->get(WebsiteLeadResource::getUrl('view', ['record' => $websiteLead], panel: 'crm'))->assertOk();
        $this->get(PostResource::getUrl('edit', ['record' => $post], panel: 'cms'))->assertOk();
        $this->get(SentenceResource::getUrl('edit', ['record' => $sentence], panel: 'cms'))->assertOk();
    }

    private function superAdmin(): User
    {
        $role = Role::query()
            ->where('name', config('filament-shield.super_admin.name'))
            ->where('guard_name', 'web')
            ->firstOrFail();

        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereKey($role->id))
            ->firstOrFail();
    }
}
