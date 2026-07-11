<?php

namespace App\Models\Website;

use App\Enums\Website\ContactCategories;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'website_contacts';

    protected $casts = [
        'is_published' => 'boolean',
        'category' => ContactCategories::class
    ];

    protected $fillable = [
        'category',
        'first_name',
        'last_name',
        'label',
        'slug',
        'sort_name',
        'organization'
    ];

    public function court_sentences()
    {
        return $this->hasMany(Sentence::class, 'court_id');
    }

    public function court_published_sentences()
    {
        return $this->court_sentences()->where('is_published', 1);
    }

    public function judge_sentences()
    {
        return $this->hasMany(Sentence::class, 'judge_id');
    }

    public function judge_published_sentences()
    {
        return $this->judge_sentences()->where('is_published', 1);
    }

}
