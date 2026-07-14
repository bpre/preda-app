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
        'send_contract_analysis' => 'Wyślij analizę umowy',
        'follow_up_after_qualification' => 'Wyślij follow-up po kwalifikacji',
        'follow_up_after_info_request' => 'Wyślij follow-up po prośbie o informacje',
        'follow_up_after_analysis' => 'Wyślij follow-up po wysłaniu analizy',
        'send_offer' => 'Wyślij ofertę',
        'follow_up_after_meeting' => 'Wyślij follow-up po spotkaniu',
    ];

    public const AVAILABLE_PLACEHOLDERS = [
        '{{pani_pana}}' => 'Pani / Pana',
        '{{bank}}' => 'Nazwa banku z formularza',
        '{{waluta_kredytu}}' => 'Waluta kredytu z formularza',
        '{{rok_umowy}}' => 'Rok umowy z formularza',
        '{{link_do_konsultacji}}' => 'Link do konsultacji prawnika prowadzącego potencjalną sprawę',
        '{{prawnik}}' => 'Imię i nazwisko prawnika prowadzącego potencjalną sprawę',
        '{{funkcja}}' => 'Funkcja prawnika z podpisu mailowego',
        '{napisał|napisała}' => 'wariant zależny od płci potencjalnego klienta: forma męska / forma żeńska',
        '[przeanalizowałem|przeanalizowałam]' => 'wariant zależny od płci prawnika: forma męska / forma żeńska',
    ];

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }
}
