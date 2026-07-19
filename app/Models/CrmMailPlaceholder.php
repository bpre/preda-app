<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrmMailPlaceholder extends Model
{
    use HasUuids;

    public const BENEFITS = 'akapit_o_korzysciach';

    public const OFFER = 'akapit_o_ofercie';

    public const LAST_CONTACT_CONTEXT = 'kontekst_ostatniego_kontaktu';

    public const BENEFITS_WITH_AMOUNTS = 'benefits_with_amounts';

    public const BENEFITS_WITHOUT_AMOUNTS = 'benefits_without_amounts';

    public const OFFER_STANDARD = 'offer_standard';

    public const OFFER_CONDITIONAL = 'offer_conditional';

    public const LAST_MESSAGE_WITH_SUBJECT = 'last_message_with_subject';

    public const LAST_MESSAGE_GENERIC = 'last_message_generic';

    public const CURRENT_STAGE = 'current_stage';

    public const GENERIC_CONTEXT = 'generic_context';

    public const DEFINITIONS = [
        self::BENEFITS => [
            'placeholder' => '{{akapit_o_korzysciach}}',
            'name' => 'Akapit o korzyściach',
            'description' => 'Akapit z kwotami korzyści, jeżeli w potencjalnej sprawie oznaczono zaświadczenie.',
            'sort' => 1,
            'variants' => [
                self::BENEFITS_WITH_AMOUNTS => [
                    'label' => 'Gdy wpisano kwoty korzyści',
                    'body' => 'Na podstawie zaświadczenia szacowane korzyści przedstawiają się następująco: {{podsumowanie_korzysci}}.',
                    'variables' => [
                        '{{podsumowanie_korzysci}}' => 'zbiorcze podsumowanie wpisanych kwot',
                        '{{potencjalne_korzysci}}' => 'kwota z pola „Potencjalne korzyści”',
                        '{{anulowanie_przyszlych_rat}}' => 'kwota z pola „Anulowanie przyszłych rat”',
                        '{{nadplata_do_zwrotu}}' => 'kwota z pola „Nadpłata do zwrotu”',
                    ],
                ],
                self::BENEFITS_WITHOUT_AMOUNTS => [
                    'label' => 'Gdy jest zaświadczenie, ale nie wpisano kwot',
                    'body' => 'Na podstawie zaświadczenia możemy precyzyjniej omówić potencjalne korzyści związane z prowadzeniem sprawy.',
                    'variables' => [],
                ],
            ],
        ],
        self::OFFER => [
            'placeholder' => '{{akapit_o_ofercie}}',
            'name' => 'Akapit o ofercie',
            'description' => 'Akapit o przedstawionej ofercie, jeżeli oferta była już wysłana.',
            'sort' => 2,
            'variants' => [
                self::OFFER_STANDARD => [
                    'label' => 'Oferta standardowa',
                    'body' => 'Propozycja współpracy została już przedstawiona w tej sprawie.',
                    'variables' => [
                        '{{nazwa_oferty}}' => 'wewnętrzny label ostatnio wysłanej oferty, jeżeli jest zapisany',
                    ],
                ],
                self::OFFER_CONDITIONAL => [
                    'label' => 'Oferta warunkowa',
                    'body' => 'Przesłana propozycja współpracy ma charakter wstępny i wymaga ostatecznej weryfikacji dokumentów oraz podjęcia się sprawy przez kancelarię.',
                    'variables' => [
                        '{{nazwa_oferty}}' => 'wewnętrzny label ostatnio wysłanej oferty, jeżeli jest zapisany',
                    ],
                ],
            ],
        ],
        self::LAST_CONTACT_CONTEXT => [
            'placeholder' => '{{kontekst_ostatniego_kontaktu}}',
            'name' => 'Kontekst ostatniego kontaktu',
            'description' => 'Krótki opis ostatniej wiadomości lub aktualnego etapu sprawy.',
            'sort' => 3,
            'variants' => [
                self::LAST_MESSAGE_WITH_SUBJECT => [
                    'label' => 'Ostatnia wiadomość z datą i tematem',
                    'body' => 'Wracam do wiadomości z {{data_ostatniej_wiadomosci}} dotyczącej tematu: {{temat_ostatniej_wiadomosci}}.',
                    'variables' => [
                        '{{data_ostatniej_wiadomosci}}' => 'data ostatniej wiadomości do klienta',
                        '{{temat_ostatniej_wiadomosci}}' => 'temat ostatniej wiadomości do klienta',
                        '{{akcja_ostatniej_wiadomosci}}' => 'nazwa działania CRM dla ostatniej wiadomości',
                    ],
                ],
                self::LAST_MESSAGE_GENERIC => [
                    'label' => 'Ostatnia wiadomość bez pełnego kontekstu',
                    'body' => 'Wracam do naszej ostatniej wiadomości w tej sprawie.',
                    'variables' => [
                        '{{data_ostatniej_wiadomosci}}' => 'data ostatniej wiadomości do klienta, jeżeli jest dostępna',
                        '{{akcja_ostatniej_wiadomosci}}' => 'nazwa działania CRM dla ostatniej wiadomości',
                    ],
                ],
                self::CURRENT_STAGE => [
                    'label' => 'Brak wiadomości, ale jest aktualny etap',
                    'body' => 'Wracam do sprawy po ostatnim etapie: {{aktualny_etap}}.',
                    'variables' => [
                        '{{aktualny_etap}}' => 'nazwa aktualnego etapu potencjalnej sprawy',
                    ],
                ],
                self::GENERIC_CONTEXT => [
                    'label' => 'Brak wiadomości i aktualnego etapu',
                    'body' => 'Wracam do sprawy dotyczącej umowy kredytu.',
                    'variables' => [],
                ],
            ],
        ],
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'variants',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'id' => 'string',
        'variants' => 'array',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public static function placeholderForKey(?string $key): string
    {
        if (! $key) {
            return '';
        }

        return self::DEFINITIONS[$key]['placeholder'] ?? '{{'.$key.'}}';
    }

    public static function bodyFor(string $key, string $variant): string
    {
        $placeholder = static::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        $configuredBody = $placeholder ? data_get($placeholder->variants ?? [], $variant) : null;

        if (is_string($configuredBody)) {
            return $configuredBody;
        }

        return (string) data_get(self::DEFINITIONS, "{$key}.variants.{$variant}.body", '');
    }

    /**
     * @return array<string, string>
     */
    public static function defaultVariants(string $key): array
    {
        return collect(data_get(self::DEFINITIONS, "{$key}.variants", []))
            ->mapWithKeys(fn (array $variant, string $variantKey): array => [$variantKey => $variant['body']])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return self::DEFINITIONS[$this->key] ?? [];
    }
}
