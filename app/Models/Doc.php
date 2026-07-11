<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doc extends Model
{
    use HasFactory, HasUuids;
    protected $casts = [
        'id' => 'string',
        'body' => 'array'
    ];
    protected $fillable = [
        'label', 'date', 'body' ,'matter_id', 'credit_id', 'recipient_id', 'author_id'
    ];
}
