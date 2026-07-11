<?php

namespace App\Models\Website;

use App\Support\Website\LeadStatuses;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Lead extends Model
{
    protected $casts = [
        'files' => 'array',
        'has_contract' => 'boolean',
        'documents_uploaded_at' => 'datetime',
        'documents_skipped_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'attribution_first_touch_at' => 'datetime',
        'attribution_last_touch_at' => 'datetime',
        'attribution_click_ids' => 'array',
        'attribution_data' => 'array',
    ];

    protected $fillable = [
        'name',
        'email',
        'postal_code',
        'phone',
        'bank',
        'contract_year_range',
        'credit_currency',
        'credit_amount_range',
        'credit_status',
        'has_contract',
        'files',
        'upload_token',
        'documents_uploaded_at',
        'documents_skipped_at',
        'status',
        'status_changed_at',
        'attribution_channel',
        'attribution_source',
        'attribution_medium',
        'attribution_campaign',
        'attribution_term',
        'attribution_content',
        'attribution_landing_page',
        'attribution_conversion_page',
        'attribution_referrer',
        'attribution_first_touch_at',
        'attribution_last_touch_at',
        'attribution_click_ids',
        'attribution_data',
        'message'
    ];

    protected static function booted(): void
    {
        static::creating(function (Lead $lead): void {
            if (! Schema::hasColumn($lead->getTable(), 'status')) {
                return;
            }

            $lead->status = LeadStatuses::normalize($lead->status);
            $lead->status_changed_at ??= now();
        });

        static::created(function (Lead $lead): void {
            if (
                ! Schema::hasTable('lead_status_changes')
                || ! Schema::hasColumn($lead->getTable(), 'status')
            ) {
                return;
            }

            $lead->statusChanges()->create([
                'status' => LeadStatuses::normalize($lead->status),
                'changed_at' => $lead->status_changed_at ?? $lead->created_at ?? now(),
                'changed_by' => auth()->id(),
                'note' => 'Status początkowy.',
            ]);
        });
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(LeadStatusChange::class)->latest('changed_at');
    }

    public function changeStatus(string $status, Carbon|string|null $changedAt = null, ?int $userId = null, ?string $note = null): LeadStatusChange
    {
        $status = LeadStatuses::normalize($status);
        $changedAt = $changedAt instanceof Carbon
            ? $changedAt
            : (filled($changedAt) ? Carbon::parse((string) $changedAt) : now());

        $this->forceFill([
            'status' => $status,
            'status_changed_at' => $changedAt,
        ])->save();

        return $this->statusChanges()->create([
            'status' => $status,
            'changed_at' => $changedAt,
            'changed_by' => $userId,
            'note' => filled($note) ? trim((string) $note) : null,
        ]);
    }

    public function getAttributionSummaryAttribute(): string
    {
        return match ($this->attribution_channel) {
            'google_ads' => 'Google Ads',
            'meta_ads' => 'Meta Ads',
            'remarketing' => 'Remarketing',
            'organic_search' => $this->attribution_source === 'google'
                ? 'Google organic'
                : 'Wyszukiwarka organiczna',
            'referral' => 'Odesłanie z innej strony',
            'social' => 'Social media',
            'direct' => 'Wejście bezpośrednie',
            'other' => 'Inne źródło',
            default => 'Brak danych',
        };
    }

    public function getAttributionDescriptionAttribute(): ?string
    {
        $parts = [];

        if ($this->attribution_source) {
            $parts[] = 'Źródło: '.$this->attribution_source;
        }

        if ($this->attribution_medium) {
            $parts[] = 'Medium: '.$this->attribution_medium;
        }

        if ($this->attribution_campaign) {
            $parts[] = 'Kampania: '.$this->attribution_campaign;
        }

        if ($this->attribution_term) {
            $parts[] = 'Fraza: '.$this->attribution_term;
        }

        return $parts === [] ? null : implode(' | ', $parts);
    }
}
