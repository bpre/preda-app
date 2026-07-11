<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Sentence extends Model
{
    protected $table = 'website_sentences';

    protected $casts = [
        'is_published' => 'boolean',
        'is_paid_off' => 'boolean',
        'security_granted' => 'boolean',
        'files' => 'array',
        'ruling_points' => 'array',
        'evidence_scope' => 'array',
        'content_generator_flags' => 'array',
        'content_generated_at' => 'datetime',
    ];

    protected $fillable = [
        'sign',
        'lawsuit_date',
        'appeal_date',
        'sentence_date',
        'instance',
        'parent_id',
        'court_id',
        'judge_id',
        'bank_id',
        'bank_previously_id',
        'credit_year',
        'credit_name',
        'wps',
        'hearings',
        'result',
        'claim',
        'lawyer',
        'label',
        'excerpt',
        'content',
        'slug',
        'metatitle',
        'metadescription',
        'is_published',
        'files',
        'credit_payoff',
        'credit_profit',
        'currency',
        'is_paid_off',
        'paid_off_year',
        'ruling_points',
        'judgment_publication_mode',
        'reasoning_source',
        'court_reasoning_summary',
        'evidence_scope',
        'security_granted',
        'security_note',
        'content_generator_flags',
        'setoff_or_retention_note',
        'counterclaim_note',
        'content_note',
        'content_generated_at',
    ];

    public function court()
    {
        return $this->belongsTo(Contact::class, 'court_id');
    }

    public function judge()
    {
        return $this->belongsTo(Contact::class, 'judge_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function bank_previously()
    {
        return $this->belongsTo(Bank::class, 'bank_previously_id');
    }

    public function parent() {
        return $this->belongsTo(Sentence::class, 'parent_id');
    }

    public function child() {
        return $this->hasOne(Sentence::class, 'parent_id');
    }
}
