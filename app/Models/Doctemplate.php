<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctemplate extends Model
{
    use HasFactory, HasUuids;
    protected $casts = [
        'id' => 'string',
        'body' => 'array'
    ];
    protected $fillable = [
        'label', 'body'
    ];
}
