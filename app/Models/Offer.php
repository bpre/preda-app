<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{

    protected $fillable = [
        'matter_id',
        'sex',
        'name',
        'bank',
        'amount',
        'amount_orig',
        'currency',
        'rate',
        'year',
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
        'phone',
        'email',
        'currency_index',
        'benefit',
        'is_paid_off',
        'is_initial_offer',
        'show_benefit'
    ];
}
