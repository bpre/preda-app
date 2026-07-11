<?php

namespace Tests\Feature;

use App\Filament\Website\Resources\Banks\BankResource;
use App\Filament\Website\Resources\Cities\CityResource;
use App\Filament\Website\Resources\Contacts\ContactResource;
use App\Filament\Website\Resources\Credits\CreditResource;
use App\Filament\Website\Resources\Faqs\FaqResource;
use App\Filament\Website\Resources\Offices\OfficeResource;
use App\Filament\Website\Resources\PageSnapshots\PageSnapshotResource;
use App\Filament\Website\Resources\Posts\PostResource;
use App\Filament\Website\Resources\Reviews\ReviewResource;
use App\Filament\Website\Resources\Securities\SecurityResource;
use App\Filament\Website\Resources\Sentences\SentenceResource;
use App\Filament\Website\Resources\Users\UserResource as WebsiteUserResource;
use App\Models\User;
use App\Models\Website\Bank;
use App\Models\Website\City;
use App\Models\Website\Contact;
use App\Models\Website\Credit;
use App\Models\Website\Faq;
use App\Models\Website\Office;
use App\Models\Website\PageSnapshot;
use App\Models\Website\Post;
use App\Models\Website\Review;
use App\Models\Website\Security;
use App\Models\Website\Sentence;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealDataCmsSmokeTest extends TestCase
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

    public function test_real_data_cms_content_create_forms_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->creatableCmsResources() as $resource) {
            $this->get($resource::getUrl('create', panel: 'cms'))
                ->assertOk();
        }
    }

    public function test_real_data_cms_content_edit_forms_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->editableCmsResources() as [$resource, $record]) {
            $this->get($resource::getUrl('edit', ['record' => $record], panel: 'cms'))
                ->assertOk();
        }
    }

    public function test_real_data_cms_resource_lists_render(): void
    {
        $this->actingAs($this->superAdmin());

        foreach ($this->listableCmsResources() as $resource) {
            $this->get($resource::getUrl(panel: 'cms'))
                ->assertOk();
        }
    }

    private function creatableCmsResources(): array
    {
        return [
            PostResource::class,
            SentenceResource::class,
            BankResource::class,
            CreditResource::class,
            ContactResource::class,
            SecurityResource::class,
            FaqResource::class,
            CityResource::class,
            OfficeResource::class,
            ReviewResource::class,
            PageSnapshotResource::class,
        ];
    }

    private function listableCmsResources(): array
    {
        return [
            PostResource::class,
            SentenceResource::class,
            BankResource::class,
            CreditResource::class,
            ContactResource::class,
            SecurityResource::class,
            FaqResource::class,
            CityResource::class,
            OfficeResource::class,
            ReviewResource::class,
            PageSnapshotResource::class,
            WebsiteUserResource::class,
        ];
    }

    private function editableCmsResources(): array
    {
        return [
            [PostResource::class, Post::query()->firstOrFail()],
            [SentenceResource::class, Sentence::query()->firstOrFail()],
            [BankResource::class, Bank::query()->firstOrFail()],
            [CreditResource::class, Credit::query()->firstOrFail()],
            [ContactResource::class, Contact::query()->firstOrFail()],
            [SecurityResource::class, Security::query()->firstOrFail()],
            [FaqResource::class, Faq::query()->firstOrFail()],
            [CityResource::class, City::query()->firstOrFail()],
            [OfficeResource::class, Office::query()->firstOrFail()],
            [ReviewResource::class, Review::query()->firstOrFail()],
            [PageSnapshotResource::class, PageSnapshot::query()->firstOrFail()],
            [WebsiteUserResource::class, User::query()->firstOrFail()],
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
