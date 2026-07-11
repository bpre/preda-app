<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $casts = [
        'files' => 'array'
    ];

    protected $fillable = [
        'name',
        'sex',
        'email',
        'phone',
        'files',
        'message',
        'bank',
        'amount',
        'year',
        'variant',
        'start_wstepna',
        'start_premia',
        'start_procent_limit',
        'start_rozprawa',
        'start_razem_max',
        'max_wstepna',
        'max_druga_instancja',
        'max_rozprawa',
        'max_rozprawy_limit',
        'max_razem_max',
        'offer_confirmed_at',
        'offer_sent_at'
    ];
}
