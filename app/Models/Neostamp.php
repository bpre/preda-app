<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Neostamp extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string'
    ];
    protected $fillable = [
        'label', 'type', 'letter_id', 'expiration_date'
    ];


    // RELACJE

    public function hasAnyRelation()
    {
        return $this->contact_letter()->exists();
    }
    public function contact_letter()
    {
        return $this->belongsTo(ContactLetter::class);
    }
}
