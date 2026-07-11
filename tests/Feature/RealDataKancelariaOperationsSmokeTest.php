<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Letter;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealDataKancelariaOperationsSmokeTest extends TestCase
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

    public function test_real_data_letter_file_preview_and_download_routes_work(): void
    {
        $this->actingAs($this->superAdmin());

        [$letter, $path, $displayName] = $this->firstExistingLetterFile();
        [$bucket, $directory, $filename] = $this->storageRouteSegments($path);

        $this->get("http://ewidencja.preda-app.test/z/{$bucket}/{$directory}/{$filename}")
            ->assertOk()
            ->assertSee($displayName);

        $response = $this->get("http://ewidencja.preda-app.test/file/{$bucket}/{$directory}/{$filename}");

        $response->assertOk();
        $this->assertSame($letter->getKey(), Letter::query()->whereJsonContains('files', $path)->firstOrFail()->getKey());
        $this->assertStringContainsString('attachment', strtolower((string) $response->headers->get('content-disposition')));
    }

    public function test_real_data_offer_pdf_download_route_works(): void
    {
        $this->actingAs($this->superAdmin());

        $offer = $this->firstExistingOfferPdf();

        $response = $this->get("http://ewidencja.preda-app.test/offers/{$offer->getKey()}/pdf");

        $response->assertOk();
        $this->assertStringContainsString('attachment', strtolower((string) $response->headers->get('content-disposition')));
        $this->assertStringContainsString('pdf', strtolower((string) $response->headers->get('content-type')));
    }

    public function test_real_data_branch_report_export_route_works(): void
    {
        $this->actingAs($this->superAdmin());

        $branch = Branch::query()->firstOrFail();

        $this->get("http://ewidencja.preda-app.test/oddzialy/{$branch->getKey()}/raport/export/xlsx?report_category=CHF")
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function firstExistingLetterFile(): array
    {
        $letters = Letter::query()
            ->whereNotNull('files')
            ->whereRaw('JSON_LENGTH(files) > 0')
            ->orderBy('id')
            ->cursor();

        foreach ($letters as $letter) {
            foreach ((array) $letter->files as $path) {
                if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
                    continue;
                }

                return [
                    $letter,
                    $path,
                    $letter->files_names[$path] ?? basename($path),
                ];
            }
        }

        $this->fail('Missing a letter with an existing local file in the imported real data.');
    }

    private function firstExistingOfferPdf(): Offer
    {
        $offers = Offer::query()
            ->whereNotNull('pdf_path')
            ->orderBy('id')
            ->cursor();

        foreach ($offers as $offer) {
            if (is_string($offer->pdf_path) && Storage::disk('private')->exists($offer->pdf_path)) {
                return $offer;
            }
        }

        $this->fail('Missing an offer with an existing local PDF in the imported real data.');
    }

    private function storageRouteSegments(string $path): array
    {
        $segments = explode('/', $path, 3);

        $this->assertCount(3, $segments, "Expected storage path [{$path}] to contain three route segments.");

        return array_map(rawurlencode(...), $segments);
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
