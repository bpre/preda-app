<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAdsCampaign extends Model
{
    protected $table = 'website_google_ads_campaigns';

    protected $casts = [
        'optimization_score' => 'decimal:4',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'decimal:6',
        'average_cpc_micros' => 'integer',
        'cost_micros' => 'integer',
        'conversions' => 'decimal:4',
        'conversion_value' => 'decimal:4',
        'metrics_from' => 'date',
        'metrics_to' => 'date',
        'last_synced_at' => 'datetime',
        'raw_data' => 'array',
    ];

    protected $fillable = [
        'customer_id',
        'campaign_id',
        'name',
        'status',
        'advertising_channel_type',
        'bidding_strategy_type',
        'optimization_score',
        'budget_name',
        'budget_amount_micros',
        'budget_type',
        'currency_code',
        'clicks',
        'impressions',
        'ctr',
        'average_cpc_micros',
        'cost_micros',
        'conversions',
        'conversion_value',
        'metrics_from',
        'metrics_to',
        'last_synced_at',
        'raw_data',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'google_ads_campaign_id', 'campaign_id');
    }

    public function monthlyMetrics(): HasMany
    {
        return $this->hasMany(GoogleAdsCampaignMonthlyMetric::class);
    }

    public function getCostAttribute(): ?float
    {
        return $this->microsToMoney($this->cost_micros);
    }

    public function getAverageCpcAttribute(): ?float
    {
        return $this->microsToMoney($this->average_cpc_micros);
    }

    public function getBudgetAmountAttribute(): ?float
    {
        return $this->microsToMoney($this->budget_amount_micros);
    }

    public function getCostPerLeadAttribute(): ?float
    {
        $leadCount = $this->leads_count ?? $this->leads()->count();

        if ($leadCount <= 0 || ! is_numeric($this->cost)) {
            return null;
        }

        return round(((float) $this->cost) / $leadCount, 2);
    }

    private function microsToMoney(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return round(((float) $value) / 1_000_000, 2);
    }
}
