<?php

namespace Tests\Feature;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Filament\Crm\Resources\LeadResource as CrmLeadResource;
use App\Filament\Website\Resources\Leads\LeadResource as WebsiteLeadResource;
use App\Models\CHFPotentialMatter;
use App\Models\Lead as CrmLead;
use App\Models\User;
use App\Models\Website\Lead as WebsiteLead;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealDataCrmSmokeTest extends TestCase
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

    public function test_real_data_crm_create_forms_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->creatableCrmResources() as $resource) {
            $this->get($resource::getUrl('create', panel: 'crm'))
                ->assertOk();
        }
    }

    public function test_real_data_crm_record_pages_render(): void
    {
        $this->actingAs($this->superAdmin());

        $crmLead = CrmLead::query()->firstOrFail();
        $potentialMatter = CHFPotentialMatter::query()->firstOrFail();
        $websiteLead = WebsiteLead::query()->firstOrFail();

        $this->get(CrmLeadResource::getUrl('edit', ['record' => $crmLead], panel: 'crm'))
            ->assertOk();

        $this->get(CHFPotentialMatterResource::getUrl('edit', ['record' => $potentialMatter], panel: 'crm'))
            ->assertOk();

        $this->get(WebsiteLeadResource::getUrl('view', ['record' => $websiteLead], panel: 'crm'))
            ->assertOk();

        $this->get(WebsiteLeadResource::getUrl('edit', ['record' => $websiteLead], panel: 'crm'))
            ->assertOk();
    }

    public function test_real_data_crm_resource_lists_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->creatableCrmResources() as $resource) {
            $this->get($resource::getUrl(panel: 'crm'))
                ->assertOk();
        }
    }

    private function creatableCrmResources(): array
    {
        return [
            CrmLeadResource::class,
            CHFPotentialMatterResource::class,
            WebsiteLeadResource::class,
        ];
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
