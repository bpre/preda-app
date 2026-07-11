<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactCredit extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    public function credit(): BelongsTo
    {
        return $this->belongsTo(Credit::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id')->where('category', 'Kredytobiorca');
    }

}
