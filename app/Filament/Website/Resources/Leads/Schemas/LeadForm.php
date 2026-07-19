<?php

namespace App\Filament\Website\Resources\Leads\Schemas;

use App\Models\Website\Lead;
use App\Support\Crm\MarketingAgencyAccess;
use App\Support\Website\LeadStatuses;
use App\Support\Website\LeadTypes;
use App\Support\Website\PostalCodeLookup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        if (self::hasRestrictedMarketingAccess()) {
            return $schema
                ->components([
                    self::sourceLeadSection(),
                    self::restrictedFormDataSection(),
                ]);
        }

        return $schema
            ->components([
                self::sourceLeadSection(),
                Section::make('Dane z formularza')
                    ->schema([
                        Placeholder::make('name_info')
                            ->label('Imię i nazwisko')
                            ->content(fn (?Lead $record): string => self::restrictedText($record?->name)),
                        Placeholder::make('email_info')
                            ->label('E-mail')
                            ->content(fn (?Lead $record): string => self::restrictedText($record?->email)),
                        Placeholder::make('phone_info')
                            ->label('Telefon')
                            ->content(fn (?Lead $record): string => self::restrictedText($record?->phone)),
                        Placeholder::make('lead_type_info')
                            ->label('Typ leada')
                            ->content(fn (?Lead $record): string => LeadTypes::label($record?->lead_type) ?? '-'),
                        Placeholder::make('postal_location')
                            ->label('Lokalizacja')
                            ->content(fn (?Lead $record): string => self::postalLocation($record)),
                        Placeholder::make('bank_info')
                            ->label('Bank')
                            ->content(fn (?Lead $record): string => self::text($record?->bank)),
                        Placeholder::make('contract_year_range_info')
                            ->label('Rok umowy')
                            ->content(fn (?Lead $record): string => self::text($record?->contract_year_range)),
                        Placeholder::make('credit_currency_info')
                            ->label('Waluta kredytu')
                            ->content(fn (?Lead $record): string => self::text($record?->credit_currency)),
                        Placeholder::make('credit_amount_range_info')
                            ->label('Kwota kredytu')
                            ->content(fn (?Lead $record): string => self::text($record?->credit_amount_range)),
                        Placeholder::make('credit_status_info')
                            ->label('Status kredytu')
                            ->content(fn (?Lead $record): string => self::text($record?->credit_status)),
                        Placeholder::make('has_contract_info')
                            ->label('Czy klient ma umowę?')
                            ->content(fn (?Lead $record): string => $record?->has_contract ? 'Tak' : 'Nie'),
                        Placeholder::make('additional_info_info')
                            ->label('Dodatkowe informacje')
                            ->content(fn (?Lead $record): HtmlString => self::additionalInfo($record))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columns(2)
                    ->columnSpanFull()
                    ->visibleOn('view'),
                Select::make('lead_type')
                    ->label('Typ leada')
                    ->options(LeadTypes::options())
                    ->default(LeadTypes::FORM)
                    ->native(false)
                    ->required()
                    ->hiddenOn('view'),
                TextInput::make('name')
                    ->label('Imię i nazwisko')
                    ->hiddenOn('view'),
                TextInput::make('email')
                    ->label('E-mail')
                    ->hiddenOn('view'),
                TextInput::make('postal_code')
                    ->label('Kod pocztowy')
                    ->placeholder('00-000')
                    ->mask('99-999')
                    ->maxLength(6)
                    ->regex('/^\d{2}-\d{3}$/')
                    ->validationMessages([
                        'regex' => 'Kod pocztowy powinien mieć format 00-000.',
                    ])
                    ->hiddenOn('view'),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->hiddenOn('view'),
                Section::make('Kwalifikacja')
                    ->schema([
                        Placeholder::make('status')
                            ->label('Aktualny status')
                            ->content(fn (?Lead $record): string => $record?->status ?? LeadStatuses::NEW),
                        Placeholder::make('status_changed_at')
                            ->label('Data ostatniej zmiany')
                            ->content(fn (?Lead $record): string => self::dateTime($record?->status_changed_at)),
                        Placeholder::make('rejection_reason')
                            ->label('Powód odrzucenia')
                            ->content(fn (?Lead $record): string => LeadStatuses::rejectionReasonLabel($record?->rejection_reason) ?? '-')
                            ->visible(fn (?Lead $record): bool => LeadStatuses::isRejected($record?->status)),
                        Placeholder::make('rejection_note')
                            ->label('Notatka')
                            ->content(fn (?Lead $record): string => $record?->rejection_note ?: '-')
                            ->visible(fn (?Lead $record): bool => LeadStatuses::isRejected($record?->status)),
                    ])
                    ->collapsible()
                    ->columns(2)
                    ->hidden(fn (): bool => self::hasRestrictedMarketingAccess()),
                Textarea::make('additional_info')
                    ->label('Dodatkowe informacje')
                    ->rows(4)
                    ->hiddenOn('view'),
                Section::make('Dalszy przebieg')
                    ->schema([
                        Placeholder::make('potential_matter_label')
                            ->label('Potencjalna sprawa')
                            ->content(fn (?Lead $record): string => $record?->potentialMatter?->label ?? 'Brak'),
                        Placeholder::make('potential_matter_status')
                            ->label('Stan sprawy')
                            ->content(fn (?Lead $record): string => self::potentialMatterStatus($record)),
                        Placeholder::make('potential_matter_stage')
                            ->label('Aktualny etap')
                            ->content(fn (?Lead $record): string => self::potentialMatterStage($record)),
                        Placeholder::make('potential_matter_created_at_after_history')
                            ->label('Data kwalifikacji')
                            ->content(fn (?Lead $record): string => self::dateTime($record?->potential_matter_created_at)),
                    ])
                    ->collapsible()
                    ->columns(2)
                    ->visible(fn (?Lead $record): bool => filled($record?->potential_matter_id) && ! self::hasRestrictedMarketingAccess()),
            ]);
    }

    private static function sourceLeadSection(): Section
    {
        return Section::make('Źródło leada')
            ->schema([
                Placeholder::make('attribution_summary_info')
                    ->label('Źródło')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_summary)),
                Placeholder::make('attribution_source_info')
                    ->label('Źródło techniczne')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_source)),
                Placeholder::make('attribution_medium_info')
                    ->label('Medium')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_medium)),
                Placeholder::make('attribution_campaign_info')
                    ->label('Kampania')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_campaign)),
                Placeholder::make('attribution_term_info')
                    ->label('Fraza / keyword')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_term)),
                Placeholder::make('attribution_content_info')
                    ->label('Treść / reklama')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_content)),
                Placeholder::make('attribution_landing_page_info')
                    ->label('Strona wejścia')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_landing_page)),
                Placeholder::make('attribution_conversion_page_info')
                    ->label('Strona wysłania formularza')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_conversion_page)),
                Placeholder::make('attribution_referrer_info')
                    ->label('Referrer')
                    ->content(fn (?Lead $record): string => self::text($record?->attribution_referrer)),
            ])
            ->collapsible()
            ->columns(2)
            ->columnSpanFull()
            ->visibleOn('view');
    }

    private static function restrictedFormDataSection(): Section
    {
        return Section::make('Dane z formularza')
            ->schema([
                Placeholder::make('name_info')
                    ->label('Imię i nazwisko')
                    ->content(fn (): string => MarketingAgencyAccess::hiddenValue()),
                Placeholder::make('email_info')
                    ->label('E-mail')
                    ->content(fn (): string => MarketingAgencyAccess::hiddenValue()),
                Placeholder::make('phone_info')
                    ->label('Telefon')
                    ->content(fn (): string => MarketingAgencyAccess::hiddenValue()),
                Placeholder::make('lead_type_info')
                    ->label('Typ leada')
                    ->content(fn (?Lead $record): string => LeadTypes::label($record?->lead_type) ?? '-'),
                Placeholder::make('postal_location')
                    ->label('Lokalizacja')
                    ->content(fn (?Lead $record): string => self::postalLocation($record)),
                Placeholder::make('bank_info')
                    ->label('Bank')
                    ->content(fn (?Lead $record): string => self::text($record?->bank)),
                Placeholder::make('contract_year_range_info')
                    ->label('Rok umowy')
                    ->content(fn (?Lead $record): string => self::text($record?->contract_year_range)),
                Placeholder::make('credit_currency_info')
                    ->label('Waluta kredytu')
                    ->content(fn (?Lead $record): string => self::text($record?->credit_currency)),
                Placeholder::make('credit_amount_range_info')
                    ->label('Kwota kredytu')
                    ->content(fn (?Lead $record): string => self::text($record?->credit_amount_range)),
                Placeholder::make('credit_status_info')
                    ->label('Status kredytu')
                    ->content(fn (?Lead $record): string => self::text($record?->credit_status)),
                Placeholder::make('has_contract_info')
                    ->label('Czy klient ma umowę?')
                    ->content(fn (?Lead $record): string => $record?->has_contract ? 'Tak' : 'Nie'),
                Placeholder::make('additional_info_info')
                    ->label('Dodatkowe informacje')
                    ->content(fn (): string => MarketingAgencyAccess::hiddenValue())
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->columns(2)
            ->columnSpanFull()
            ->visibleOn('view');
    }

    private static function potentialMatterStatus(?Lead $lead): string
    {
        $matter = $lead?->potentialMatter;

        if (! $matter) {
            return 'Brak';
        }

        if ($matter->is_matter) {
            return 'Sprawa przyjęta do prowadzenia';
        }

        if ($matter->end) {
            return 'Zamknięta';
        }

        if ($matter->is_archived) {
            return 'Zarchiwizowana';
        }

        return 'W toku';
    }

    private static function potentialMatterStage(?Lead $lead): string
    {
        $stage = $lead?->potentialMatter?->currentStage;

        if (! $stage) {
            return '-';
        }

        return collect([$stage->parent, $stage->label])
            ->filter()
            ->implode(' / ');
    }

    private static function dateTime(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d H:i')
            : (string) $value;
    }

    private static function text(?string $value): string
    {
        return filled($value) ? $value : '-';
    }

    private static function restrictedText(?string $value): string
    {
        if (self::hasRestrictedMarketingAccess()) {
            return MarketingAgencyAccess::hiddenValue();
        }

        return self::text($value);
    }

    private static function additionalInfo(?Lead $lead): HtmlString
    {
        if (self::hasRestrictedMarketingAccess()) {
            return new HtmlString(MarketingAgencyAccess::hiddenValue());
        }

        $additionalInfo = self::additionalInfoText($lead);

        if (blank($additionalInfo)) {
            return new HtmlString('-');
        }

        return new HtmlString('<div class="prose prose-sm max-w-none">'.nl2br(e($additionalInfo)).'</div>');
    }

    private static function additionalInfoText(?Lead $lead): ?string
    {
        if (! $lead) {
            return null;
        }

        if (filled($lead->additional_info)) {
            return trim((string) $lead->additional_info);
        }

        if (blank($lead->message)) {
            return null;
        }

        if (! preg_match('/(?:^|\R)Dodatkowe informacje:\s*(.+)\z/su', (string) $lead->message, $matches)) {
            return null;
        }

        $additionalInfo = trim($matches[1]);

        return $additionalInfo === '' ? null : $additionalInfo;
    }

    private static function postalLocation(?Lead $lead): string
    {
        if (! $lead) {
            return '-';
        }

        app(PostalCodeLookup::class)->fillMissingLeadRegion($lead);

        if (blank($lead->postal_code)) {
            return '-';
        }

        return collect([
            $lead->postal_code,
            filled($lead->postal_county) ? 'powiat '.$lead->postal_county : null,
            filled($lead->postal_voivodeship) ? 'województwo '.$lead->postal_voivodeship : null,
        ])->filter()->implode(', ');
    }

    private static function hasRestrictedMarketingAccess(): bool
    {
        return MarketingAgencyAccess::usesRestrictedLeadView();
    }
}
