<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $table = 'website_credits';

    protected $casts = [
        'is_published' => 'boolean',
        'clauses' => 'array'
    ];

    protected $fillable = [
        'credit_name',
        'credit_year',
        'credit_type',
        'credit_currency',
        'bank_id',
        'is_published',
        'clauses'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
