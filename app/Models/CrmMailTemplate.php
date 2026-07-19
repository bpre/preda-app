<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmMailTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'action',
        'name',
        'subject',
        'body',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public const ACTION_LABELS = [
        'confirm_qualification' => 'Wyślij potwierdzenie kwalifikacji sprawy',
        'request_additional_info' => 'Wyślij prośbę o dodatkowe informacje',
        'request_certificate' => 'Wyślij prośbę o zaświadczenie',
        'send_contract_analysis' => 'Wyślij analizę umowy',
        'send_post_meeting_benefits_analysis' => 'Wyślij analizę korzyści po spotkaniu',
        'follow_up_after_qualification' => 'Wyślij follow-up po kwalifikacji',
        'follow_up_after_info_request' => 'Wyślij follow-up po prośbie o informacje',
        'follow_up_after_certificate_request' => 'Wyślij follow-up po prośbie o zaświadczenie',
        'follow_up_after_analysis' => 'Wyślij follow-up po wysłaniu analizy',
        'send_offer' => 'Wyślij ofertę',
        'follow_up_after_offer' => 'Wyślij follow-up po ofercie',
        'follow_up_after_meeting' => 'Wyślij follow-up po spotkaniu',
        'follow_up_after_post_meeting_benefits_analysis' => 'Wyślij follow-up po analizie korzyści po spotkaniu',
        'final_follow_up_before_closing' => 'Wyślij ostatni follow-up',
    ];

    public const AVAILABLE_PLACEHOLDERS = [
        '{{pani_pana}}' => 'Pani / Pana',
        '{{bank}}' => 'Nazwa banku z formularza',
        '{{waluta_kredytu}}' => 'Waluta kredytu z formularza',
        '{{rok_umowy}}' => 'Rok umowy z formularza',
        '{{link_do_konsultacji}}' => 'Link do konsultacji prawnika prowadzącego potencjalną sprawę',
        '{{prawnik}}' => 'Imię i nazwisko prawnika prowadzącego potencjalną sprawę',
        '{{funkcja}}' => 'Funkcja prawnika z podpisu mailowego',
        '{{akapit_o_korzysciach}}' => 'Akapit z kwotami korzyści, jeżeli w potencjalnej sprawie oznaczono zaświadczenie',
        '{{akapit_o_ofercie}}' => 'Akapit o przedstawionej ofercie, jeżeli oferta była już wysłana',
        '{{kontekst_ostatniego_kontaktu}}' => 'Krótki opis ostatniej wiadomości lub aktualnego etapu sprawy',
        '{napisał|napisała}' => 'wariant zależny od płci potencjalnego klienta: forma męska / forma żeńska',
        '[przeanalizowałem|przeanalizowałam]' => 'wariant zależny od płci prawnika: forma męska / forma żeńska',
    ];

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }
}
