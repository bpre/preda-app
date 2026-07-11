<?php

namespace App\Models\Website;

use App\Enums\Website\FAQPrefixes;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'website_faqs';

    protected $casts = [
        'prefix' => FAQPrefixes::class,
    ];
    protected $fillable = [
        'prefix',
        'question',
        'answer',
        'sort'
    ];
}
