<?php

namespace App\Models;

use App\Models\ContactDeal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'is_draft' => 'boolean',
        'is_bonus' => 'boolean',
    ];
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'label',
        'date',
        'entry_fee',
        'stage_one_fee',
        'stage_two_fee',
        're_recogniction_fee',
        'supreme_court_fee',
        'bank_lawsuit_fee',
        'hearing_fee',
        'hearing_online_fee',
        'is_bonus',
        'bonus_percent',
        'bonus_minimum',
        'bonus_fee',
        'installments',
        'first_installment_date',
        'is_draft',
        'wps_fee'
    ];

    // RELACJE

    public function contactDeal(): HasMany
    {
        return $this->hasMany(ContactDeal::class);
    }

    public function credits()
    {
        return $this->belongsToMany(Credit::class);
    }

    // RELACJE - REV

    public function deal_contacts() {
        return $this->belongsToMany(Contact::class);
    }

    public function deal_credits() {
        return $this->belongsToMany(Credit::class);
    }

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }

    public function scopeMine($query) {
        $myMatters = Matter::where('lawyer_id', auth()->user()->id)->pluck('id');
        return $query->whereIn('matter_id', $myMatters);
    }

}
