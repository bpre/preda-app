<?php

namespace App\Models\Website;

use App\Models\Website\Credit;
use App\Models\Website\Sentence;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $casts = [
        'is_published' => 'boolean',
        'is_analysisable' => 'boolean'
    ];

    protected $fillable = [
        'bank',
        'label',
        'form_a',
        'form_e',
        'form_w',
        'form_z',
        'slug',
        'successor_id',
        'is_published',
        'sort',
        'desc_chf',
        'desc_eur'
    ];

    public function successor()
    {
        return $this->belongsTo(Bank::class, 'successor_id');
    }

    public function ancestors()
    {
        return $this->HasMany(Bank::class, 'successor_id');
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'bank_id');
    }

    public function credits_chf()
    {
        return $this->hasMany(Credit::class, 'bank_id')
                    ->where('credit_currency', 'CHF')
                    ->orderBy('sort');
    }

    public function credits_eur()
    {
        return $this->hasMany(Credit::class, 'bank_id')
                    ->where('credit_currency', 'EUR')
                    ->orderBy('sort');
    }

    public function sentences()
    {
        return $this->hasMany(Sentence::class, 'bank_id');
    }

    public function sentences_prev()
    {
        return $this->hasMany(Sentence::class, 'bank_previously_id');
    }

    /**
     * Sprawdza czy bank ma kredyty w określonej walucie
     */
    public function hasEUR(): bool
    {
        return $this->credits()
                   ->where('credit_currency', 'EUR')
                   ->exists();
    }

    public function bank_sentences()
    {
        return $this->hasMany(Sentence::class, 'bank_id');
    }

    public function bank_published_sentences()
    {
        return $this->bank_sentences()->where('is_published', 1);
    }

}
