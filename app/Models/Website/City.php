<?php

namespace App\Models\Website;

use App\Enums\Website\Provinces;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $casts = [
        'is_published' => 'boolean',
        'show_in_footer' => 'boolean',
        'province' => Provinces::class
    ];

    protected $fillable = [
        'city',
        'form_a',
        'form_e',
        'form_w',
        'form_z',
        'slug',
        'so',
        'sa',
        'km',
        'province',
        'is_published'
    ];
}
