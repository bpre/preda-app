<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'label',
        'deadline',
        'amount',
        'matter_id',
        'is_paid',
        'date',
    ];

    // RELACJE

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('is_paid', false);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->unpaid()->whereNotNull('deadline')->where('deadline', '<', now()->toDateString());
    }

    public function scopeFuture(Builder $query): Builder
    {
        return $query->unpaid()->whereNotNull('deadline')->where('deadline', '>=', now()->toDateString());
    }

    public function scopePotential(Builder $query): Builder
    {
        return $query->unpaid()->whereNull('deadline');
    }

    public function scopeForChfMatters(Builder $query): Builder
    {
        return $query->whereHas('matter', fn (Builder $query): Builder => $query->chfMatter());
    }

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }
}
