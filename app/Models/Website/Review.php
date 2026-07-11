<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $casts = [
        'date' => 'date',
        'rating' => 'integer',
        'is_published' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'source',
        'source_review_id',
        'date',
        'amount',
        'rating',
        'color',
        'review',
        'avatar_url',
        'is_published',
        'sort'
    ];
}
