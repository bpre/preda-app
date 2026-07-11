<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class SentenceContentTemplate extends Model
{
    protected $table = 'website_sentence_content_templates';

    protected $casts = [
        'is_active' => 'boolean',
        'all_of' => 'array',
        'any_of' => 'array',
        'none_of' => 'array',
    ];

    protected $fillable = [
        'key',
        'name',
        'is_active',
        'instance',
        'section',
        'all_of',
        'any_of',
        'none_of',
        'priority',
        'selection_mode',
        'content',
        'note',
        'sort',
    ];

    /**
     * @return array<string, string>
     */
    public static function sectionOptions(): array
    {
        return [
            'procedural_events' => 'Zdarzenia procesowe',
            'evidence' => 'Postępowanie dowodowe',
            'security' => 'Zabezpieczenie',
            'benefit' => 'Korzyść z wygrania sprawy',
            'custom' => 'Inne',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function conditionOptions(): array
    {
        return [
            'instance_1' => 'I instancja',
            'instance_2' => 'II instancja',
            'instance_3' => 'Postępowanie kasacyjne',
            'parent_exists' => 'Ma powiązany wyrok I instancji',
            'borrower_hearing' => 'Przesłuchanie kredytobiorców',
            'documents' => 'Dokumenty',
            'witnesses' => 'Świadkowie',
            'expert_opinion' => 'Opinia biegłego',
            'expert_omitted' => 'Pominięcie opinii biegłego',
            'bank_witness_omitted' => 'Pominięcie świadków banku',
            'security_granted' => 'Udzielono zabezpieczenia',
            'counterclaim_dismissed' => 'Sąd nie uwzględnił powództwa wzajemnego banku',
            'setoff_dismissed' => 'Sąd nie uwzględnił zarzutu potrącenia',
            'retention_dismissed' => 'Sąd nie uwzględnił zarzutu zatrzymania',
            'closed_session' => 'Wyrok na posiedzeniu niejawnym',
            'hearing_publication' => 'Wyrok po rozprawie',
            'oral_reasons' => 'Ustne motywy',
            'written_reasons' => 'Uzasadnienie pisemne',
            'paid_off' => 'Kredyt spłacony',
            'credit_profit_present' => 'Uzupełniona korzyść',
            'credit_payoff_present' => 'Uzupełniona kwota wypłacona',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function selectionModeOptions(): array
    {
        return [
            'random' => 'Losowo z pasującej puli',
            'first' => 'Pierwszy wg priorytetu',
        ];
    }
}
