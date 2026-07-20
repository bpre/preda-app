<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\Website\Resources\Leads\Actions\MarkLeadAsIncorrectlyQualifiedAction;
use App\Filament\Website\Resources\Leads\Actions\OpenOrCreatePotentialMatterAction;
use App\Filament\Website\Resources\Leads\Actions\RejectLeadAction;
use App\Filament\Website\Resources\Leads\LeadResource;
use App\Support\Crm\MarketingAgencyAccess;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function getTitle(): string
    {
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return 'Lead #'.$this->getRecord()->getKey();
        }

        return $this->getRecord()->display_name
            ?: $this->getRecord()->{static::$resource::getRecordTitleAttribute()};
    }

    public function getRecordTitle(): string|Htmlable
    {
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return 'Lead #'.$this->getRecord()->getKey();
        }

        return $this->getRecord()->display_name
            ?: parent::getRecordTitle();
    }

    public function getBreadcrumb(): string
    {
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return 'Lead #'.$this->getRecord()->getKey();
        }

        return $this->getRecord()->display_name
            ?: parent::getBreadcrumb();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! MarketingAgencyAccess::usesRestrictedLeadView()) {
            return $data;
        }

        return Arr::only($data, [
            'id',
            'lead_type',
            'postal_code',
            'postal_voivodeship',
            'postal_county',
            'bank',
            'contract_year_range',
            'credit_currency',
            'credit_amount_range',
            'credit_status',
            'has_contract',
            'attribution_channel',
            'attribution_source',
            'attribution_medium',
            'attribution_campaign',
            'google_ads_campaign_id',
            'attribution_term',
            'attribution_content',
            'attribution_landing_page',
            'attribution_conversion_page',
            'attribution_referrer',
            'created_at',
        ]);
    }

    protected function getHeaderActions(): array
    {
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return [];
        }

        return [
            OpenOrCreatePotentialMatterAction::make()
                ->outlined(),
            RejectLeadAction::make()
                ->outlined(),
            MarkLeadAsIncorrectlyQualifiedAction::make()
                ->outlined(),
            ActionGroup::make([
                EditAction::make(),
            ])->dropdownWidth(Width::Small),
        ];
    }

}
