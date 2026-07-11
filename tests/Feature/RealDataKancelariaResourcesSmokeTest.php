<?php

namespace Tests\Feature;

use App\Filament\Resources\BankMatterResource;
use App\Filament\Resources\BranchResource;
use App\Filament\Resources\CHFMatterResource;
use App\Filament\Resources\CHFPaymentMatterResource;
use App\Filament\Resources\ContactMatterResource;
use App\Filament\Resources\ContactResource;
use App\Filament\Resources\CreditResource;
use App\Filament\Resources\DealResource;
use App\Filament\Resources\DepartamentResource;
use App\Filament\Resources\ExchangeRateResource;
use App\Filament\Resources\LawsuitResource;
use App\Filament\Resources\LetterNotificationResource;
use App\Filament\Resources\LetterNotificationTemplateResource;
use App\Filament\Resources\LetterResource;
use App\Filament\Resources\MatterResource;
use App\Filament\Resources\NeostampResource;
use App\Filament\Resources\NotificationResource;
use App\Filament\Resources\OtherMatterResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\StageResource;
use App\Filament\Resources\TaskResource;
use App\Filament\Resources\TemplateStageResource;
use App\Filament\Resources\UserResource;
use App\Models\BankMatter;
use App\Models\Branch;
use App\Models\CHFMatter;
use App\Models\CHFPaymentMatter;
use App\Models\Contact;
use App\Models\ContactMatter;
use App\Models\Credit;
use App\Models\Deal;
use App\Models\Departament;
use App\Models\ExchangeRate;
use App\Models\Matter;
use App\Models\OtherMatter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealDataKancelariaResourcesSmokeTest extends TestCase
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

    public function test_real_data_kancelaria_create_forms_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->creatableKancelariaResources() as $resource) {
            $this->get($resource::getUrl('create', panel: 'kancelaria'))
                ->assertOk();
        }
    }

    public function test_real_data_kancelaria_resource_lists_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->listableKancelariaResources() as $resource) {
            $this->get($resource::getUrl(panel: 'kancelaria'))
                ->assertOk();
        }
    }

    public function test_real_data_kancelaria_record_pages_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->editableKancelariaResources() as [$resource, $record]) {
            $this->get($resource::getUrl('edit', ['record' => $record], panel: 'kancelaria'))
                ->assertOk();
        }

        $branch = Branch::query()->firstOrFail();

        $this->get(BranchResource::getUrl('view', ['record' => $branch], panel: 'kancelaria'))
            ->assertOk();

        $this->get(BranchResource::getUrl('raport', ['record' => $branch], panel: 'kancelaria'))
            ->assertOk();
    }

    private function creatableKancelariaResources(): array
    {
        return [
            BankMatterResource::class,
            BranchResource::class,
            CHFMatterResource::class,
            CHFPaymentMatterResource::class,
            ContactMatterResource::class,
            CreditResource::class,
            DealResource::class,
            DepartamentResource::class,
            MatterResource::class,
            NeostampResource::class,
            OtherMatterResource::class,
        ];
    }

    private function listableKancelariaResources(): array
    {
        return [
            BankMatterResource::class,
            BranchResource::class,
            CHFMatterResource::class,
            CHFPaymentMatterResource::class,
            ContactMatterResource::class,
            ContactResource::class,
            CreditResource::class,
            DealResource::class,
            DepartamentResource::class,
            ExchangeRateResource::class,
            LawsuitResource::class,
            LetterNotificationResource::class,
            LetterNotificationTemplateResource::class,
            LetterResource::class,
            MatterResource::class,
            NeostampResource::class,
            NotificationResource::class,
            OtherMatterResource::class,
            PaymentResource::class,
            StageResource::class,
            TaskResource::class,
            TemplateStageResource::class,
            UserResource::class,
        ];
    }

    private function editableKancelariaResources(): array
    {
        return [
            [BankMatterResource::class, BankMatter::query()->firstOrFail()],
            [BranchResource::class, Branch::query()->firstOrFail()],
            [CHFMatterResource::class, CHFMatter::query()->where('is_matter', true)->firstOrFail()],
            [CHFPaymentMatterResource::class, CHFPaymentMatter::query()->firstOrFail()],
            [ContactMatterResource::class, ContactMatter::query()->firstOrFail()],
            [ContactResource::class, Contact::query()->firstOrFail()],
            [CreditResource::class, Credit::query()->firstOrFail()],
            [DealResource::class, Deal::query()->firstOrFail()],
            [DepartamentResource::class, Departament::query()->firstOrFail()],
            [ExchangeRateResource::class, ExchangeRate::query()->firstOrFail()],
            [MatterResource::class, Matter::query()->firstOrFail()],
            [OtherMatterResource::class, OtherMatter::query()->firstOrFail()],
            [UserResource::class, User::query()->firstOrFail()],
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
