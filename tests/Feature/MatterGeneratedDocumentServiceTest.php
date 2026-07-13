<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Credit;
use App\Models\CHFPotentialMatter;
use App\Models\MatterGeneratedDocument;
use App\Models\User;
use App\Models\Website\Lead;
use App\Services\CreditDocumentPdfService;
use App\Services\MatterGeneratedDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MatterGeneratedDocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_and_stores_contract_analysis_document(): void
    {
        Storage::fake('local');
        $this->travelTo(now()->setDate(2026, 7, 12)->setTime(18, 30));

        $this->app->bind(CreditDocumentPdfService::class, fn () => new class extends CreditDocumentPdfService
        {
            public function contractAnalysis(Credit $credit): string
            {
                return '%PDF-test-analysis';
            }
        });

        $matter = $this->matterWithLead();
        $credit = $this->creditForMatter($matter);

        $document = app(MatterGeneratedDocumentService::class)
            ->generateContractAnalysis($credit, $matter);

        $this->assertSame(MatterGeneratedDocument::TYPE_CONTRACT_ANALYSIS, $document->type);
        $this->assertSame('2026.07.12 J. Kowalski - Analiza umowy', $document->filename);
        $this->assertSame('2026.07.12 J. Kowalski - Analiza umowy.pdf', $document->downloadFilename());
        $this->assertSame('application/pdf', $document->mime_type);
        Storage::disk('local')->assertExists($document->path);

        $document->delete();

        Storage::disk('local')->assertMissing($document->path);
    }

    public function test_it_generates_and_stores_certificate_request_for_potential_matter(): void
    {
        Storage::fake('local');
        $this->travelTo(now()->setDate(2026, 7, 12)->setTime(18, 30));

        $this->app->bind(CreditDocumentPdfService::class, fn () => new class extends CreditDocumentPdfService
        {
            public function certificateRequest(Credit $credit, array $data): string
            {
                return '%PDF-test-certificate-request';
            }
        });

        $matter = $this->matterWithLead();
        $credit = $this->creditForMatter($matter);

        $document = app(MatterGeneratedDocumentService::class)
            ->generateCertificateRequest($credit, $matter, [
                'wnioskodawca' => 'test-contact-id',
                'dokumenty' => true,
                'regulamin' => false,
                'date' => now(),
            ]);

        $this->assertSame(MatterGeneratedDocument::TYPE_CERTIFICATE_REQUEST, $document->type);
        $this->assertSame('2026.07.12 J. Kowalski - Wniosek o wydanie zaświadczenia', $document->filename);
        $this->assertSame('2026.07.12 J. Kowalski - Wniosek o wydanie zaświadczenia.pdf', $document->downloadFilename());
        Storage::disk('local')->assertExists($document->path);
    }

    private function matterWithLead(): CHFPotentialMatter
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_employee' => true,
            'is_lawyer' => true,
        ]);

        $matter = CHFPotentialMatter::create([
            'label' => 'Kowalski Jan / Bank Testowy',
            'lawyer_id' => $user->getKey(),
            'category' => 'CHF',
            'is_matter' => false,
            'userinfo' => [],
        ]);

        Lead::create([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.test',
            'phone' => '500 600 700',
            'bank' => 'Bank Testowy',
            'potential_matter_id' => $matter->getKey(),
        ]);

        return $matter;
    }

    private function creditForMatter(CHFPotentialMatter $matter): Credit
    {
        $bank = Contact::create([
            'category' => 'Bank',
            'type' => 'organization',
            'label' => 'Bank Testowy',
            'sort_name' => 'Bank Testowy',
            'organization' => 'Bank Testowy S.A.',
        ]);

        $credit = Credit::create([
            'former_bank' => $bank->getKey(),
            'current_bank' => $bank->getKey(),
            'number' => 'ABC/123',
            'date' => '2010-01-01',
            'details' => [],
        ]);

        $credit->forceFill([
            'matter_id' => $matter->getKey(),
        ])->save();

        return $credit;
    }
}
