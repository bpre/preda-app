<?php

namespace App\Support\Crm;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class MarketingAgencyAccess
{
    public const ROLE = 'Agencja marketingowa';

    public const VIEW_LEAD_STATS_PERMISSION = 'view_lead_stats';

    public const VIEW_LEAD_STATS_WIDGET_PERMISSION = 'view_lead_stats_widget';

    public const VIEW_MARKETING_LEADS_PERMISSION = 'view_marketing_leads';

    public static function canViewLeadStats(?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        return $user instanceof User
            && ($user->can(self::VIEW_LEAD_STATS_PERMISSION)
                || $user->can(self::VIEW_LEAD_STATS_WIDGET_PERMISSION));
    }

    public static function canViewMarketingLeads(?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        return $user instanceof User
            && $user->can(self::VIEW_MARKETING_LEADS_PERMISSION);
    }

    public static function canAccessCrmPanel(?Authenticatable $user = null): bool
    {
        return self::canViewLeadStats($user) || self::canViewMarketingLeads($user);
    }

    public static function usesRestrictedLeadView(?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        return $user instanceof User
            && self::canViewMarketingLeads($user)
            && ! self::canViewFullLeadData($user);
    }

    public static function canViewFullLeadData(?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        return $user instanceof User
            && ($user->can('ViewAny:Lead') || $user->can('View:Lead'));
    }

    public static function hiddenValue(): string
    {
        return '[dane ukryte]';
    }
}
