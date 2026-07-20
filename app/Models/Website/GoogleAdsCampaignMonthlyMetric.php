<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAdsCampaignMonthlyMetric extends Model
{
    protected $table = 'website_google_ads_campaign_monthly_metrics';

    protected $casts = [
        'month' => 'date',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'decimal:6',
        'average_cpc_micros' => 'integer',
        'cost_micros' => 'integer',
        'conversions' => 'decimal:4',
        'conversion_value' => 'decimal:4',
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    protected $fillable = [
        'google_ads_campaign_id',
        'month',
        'currency_code',
        'clicks',
        'impressions',
        'ctr',
        'average_cpc_micros',
        'cost_micros',
        'conversions',
        'conversion_value',
        'last_synced_at',
        'raw_data',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GoogleAdsCampaign::class, 'google_ads_campaign_id');
    }

    public function getCostAttribute(): ?float
    {
        return $this->microsToMoney($this->cost_micros);
    }

    public function getAverageCpcAttribute(): ?float
    {
        return $this->microsToMoney($this->average_cpc_micros);
    }

    private function microsToMoney(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return round(((float) $value) / 1_000_000, 2);
    }
}
