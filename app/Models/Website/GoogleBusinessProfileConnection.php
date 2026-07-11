<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class GoogleBusinessProfileConnection extends Model
{
    protected $casts = [
        'available_accounts' => 'array',
        'available_locations' => 'array',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    protected $fillable = [
        'google_account_name',
        'google_account_label',
        'google_location_name',
        'google_location_title',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'available_accounts',
        'available_locations',
        'connected_at',
        'last_synced_at',
        'last_sync_error',
    ];

    public function hasRefreshToken(): bool
    {
        return filled($this->refresh_token);
    }

    public function hasSelectedLocation(): bool
    {
        return filled($this->google_location_name);
    }
}
