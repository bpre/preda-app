<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Branch extends Model
{
    use HasFactory, HasUuids;

    public const TYPE_STATIONARY = 'stacjonarny';

    public const TYPE_REMOTE = 'zdalny';

    protected $casts = [
        'id' => 'string',
        'accepts_new_matters' => 'boolean',
        'is_default_for_new_matters' => 'boolean',
        'closed_at' => 'date',
        'monthly_matter_goal' => 'integer',
        'monthly_revenue_goal' => 'float',
    ];

    protected $fillable = [
        'label',
        'user_id',
        'type',
        'accepts_new_matters',
        'closed_at',
        'is_default_for_new_matters',
        'street',
        'postal_code',
        'city',
        'email',
        'phone',
        'monthly_matter_goal',
        'monthly_revenue_goal',
    ];

    protected static function booted(): void
    {
        static::saving(function (Branch $branch): void {
            if (! $branch->accepts_new_matters) {
                $branch->is_default_for_new_matters = false;
            }
        });

        static::saved(function (Branch $branch): void {
            if (! $branch->is_default_for_new_matters) {
                return;
            }

            static::query()
                ->whereKeyNot($branch->getKey())
                ->update(['is_default_for_new_matters' => false]);
        });
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_STATIONARY => 'Stacjonarny',
            self::TYPE_REMOTE => 'Zdalny',
        ];
    }

    public function scopeAcceptingNewMatters(Builder $query): Builder
    {
        return $query->where('accepts_new_matters', true);
    }

    public function scopeDefaultForNewMatters(Builder $query): Builder
    {
        return $query->where('is_default_for_new_matters', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort')->orderBy('label');
    }

    public function isRemote(): bool
    {
        return $this->type === self::TYPE_REMOTE;
    }

    public function acceptsNewMatters(): bool
    {
        return (bool) $this->accepts_new_matters;
    }

    public function closeForNewMatters(mixed $closedAt = null): void
    {
        $this->forceFill([
            'accepts_new_matters' => false,
            'closed_at' => $closedAt ? Carbon::parse($closedAt)->toDateString() : now()->toDateString(),
            'is_default_for_new_matters' => false,
        ])->save();
    }

    public function reopenForNewMatters(): void
    {
        $this->forceFill([
            'accepts_new_matters' => true,
            'closed_at' => null,
        ])->save();
    }

    public function fullAddress(): ?string
    {
        $parts = array_filter([
            $this->street,
            trim(implode(' ', array_filter([$this->postal_code, $this->city]))),
        ]);

        return $parts === [] ? null : implode(', ', $parts);
    }

    public function hasAnyRelation(): bool
    {
        return $this->matters()->exists() || $this->expenses()->exists();
    }

    public function matters()
    {
        return $this->hasMany(Matter::class, 'branch_id');
    }

    public function chfMatters()
    {
        return $this->matters()->chfMatter();
    }

    public function activeChfMatters()
    {
        return $this->chfMatters()->active();
    }

    public function chf_matters()
    {
        return $this->chfMatters();
    }

    public function active_chf_matters()
    {
        return $this->activeChfMatters();
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Matter::class, 'branch_id', 'matter_id');
    }

    public function chfPayments()
    {
        return $this->payments()
            ->where('matters.is_chf', true)
            ->where('matters.is_matter', true)
            ->where('matters.category', 'CHF');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'branch_id');
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
