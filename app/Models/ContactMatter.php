<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMatter extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contact_matter';

    protected $casts = [
        'id' => 'string',
        'matter_id' => 'string',
        'contact_id' => 'string',
        'receives_notifications' => 'boolean',
    ];

    protected $fillable = [
        'matter_id',
        'contact_id',
        'receives_notifications',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }
}
