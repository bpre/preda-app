<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactLetter extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'id', 'contact_id', 'letter_id', 'delivery_type', 'neostamp_id', 'departament_id'
    ];

    public $timestamps = false;

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function departament(): BelongsTo
    {
        return $this->belongsTo(Departament::class, 'departament_id');
    }

    public function neostamp()
    {
        return $this->hasOne(Neostamp::class, 'contact_letter_id');
    }

    public function neostamps()
    {
        return $this->belongsTo(Neostamp::class, 'contact_letter_id');
    }

}
