<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Credit;
use App\Models\Matter;
use App\Models\MatterGeneratedDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MatterGeneratedDocumentService
{
    public function __construct(
        private readonly CreditDocumentPdfService $pdfService,
    ) {}

    public function generateContractAnalysis(Credit $credit, Matter $matter): MatterGeneratedDocument
    {
        return $this->store(
            matter: $matter,
            credit: $credit,
            type: MatterGeneratedDocument::TYPE_CONTRACT_ANALYSIS,
            pdf: $this->pdfService->contractAnalysis($credit),
        );
    }

    /**
     * @param  array{wnioskodawca: string, dokumenty?: bool, regulamin?: bool, date?: mixed}  $data
     */
    public function generateCertificateRequest(Credit $credit, Matter $matter, array $data): MatterGeneratedDocument
    {
        return $this->store(
            matter: $matter,
            credit: $credit,
            type: MatterGeneratedDocument::TYPE_CERTIFICATE_REQUEST,
            pdf: $this->pdfService->certificateRequest($credit, $data),
        );
    }

    private function store(Matter $matter, Credit $credit, string $type, string $pdf): MatterGeneratedDocument
    {
        if ($credit->matter_id !== $matter->getKey()) {
            throw new InvalidArgumentException('Umowa kredytowa nie należy do tej sprawy.');
        }

        $id = (string) Str::uuid();
        $path = "matter-generated-documents/{$matter->getKey()}/{$id}.pdf";

        Storage::disk('local')->put($path, $pdf);

        return MatterGeneratedDocument::create([
            'id' => $id,
            'matter_id' => $matter->getKey(),
            'credit_id' => $credit->getKey(),
            'type' => $type,
            'filename' => $this->defaultFilename($matter, $type),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('local')->size($path),
            'generated_at' => now(),
        ]);
    }

    private function defaultFilename(Matter $matter, string $type): string
    {
        $typeLabel = MatterGeneratedDocument::typeLabels()[$type] ?? 'Dokument';

        return now()->format('Y.m.d').' '.$this->clientShortName($matter).' - '.$typeLabel;
    }

    private function clientShortName(Matter $matter): string
    {
        $contact = $matter->contacts()
            ->whereNotNull('contacts.last_name')
            ->where('contacts.last_name', '!=', '')
            ->first(['contacts.first_name', 'contacts.last_name']);

        if ($contact instanceof Contact && filled($contact->last_name)) {
            return $this->formatShortName($contact->first_name, $contact->last_name);
        }

        $leadName = trim((string) $matter->sourceWebsiteLead()->value('name'));

        if ($leadName !== '') {
            return $this->shortNameFromFullName($leadName, false);
        }

        $labelClientPart = trim((string) Str::of((string) $matter->label)->before('/'));

        if ($labelClientPart !== '') {
            return $this->shortNameFromFullName($labelClientPart, str_contains((string) $matter->label, '/'));
        }

        return 'Klient';
    }

    private function shortNameFromFullName(string $name, bool $assumeLastNameFirst): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, fn (string $part): bool => $part !== ''));

        if (count($parts) < 2) {
            return $name;
        }

        if ($assumeLastNameFirst) {
            return $this->formatShortName($parts[1], $parts[0]);
        }

        return $this->formatShortName($parts[0], $parts[count($parts) - 1]);
    }

    private function formatShortName(?string $firstName, ?string $lastName): string
    {
        $firstName = trim((string) $firstName);
        $lastName = trim((string) $lastName);

        if ($firstName === '') {
            return $lastName;
        }

        if ($lastName === '') {
            return $firstName;
        }

        return mb_substr($firstName, 0, 1).'. '.$lastName;
    }
}
