<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Security extends Model
{
    protected $table = 'website_securities';

    protected $casts = [
        'is_published' => 'boolean',
        'files' => 'array'
    ];

    protected $fillable = [
        'sign',
        'sentence_date',
        'court_id',
        'judge_id',
        'bank_id',
        'bank_previously_id',
        'credit_year',
        'credit_name',
        'is_published',
        'files'
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
}
