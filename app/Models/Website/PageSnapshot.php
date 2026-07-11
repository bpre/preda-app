<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class PageSnapshot extends Model
{
    protected $fillable = [
        'url','category',
        'title','meta_description','h1','h2',
        'title_length','meta_description_length','h1_length','h2_length',
        'is_title_unique','is_h1_unique',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
        'is_title_unique' => 'boolean',
        'is_h1_unique' => 'boolean',
    ];
}
