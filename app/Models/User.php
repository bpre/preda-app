<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{

    use HasFactory, Notifiable, HasRoles;
    use HasPanelShield {
        canAccessPanel as canAccessPanelViaShield;
    }

    protected $fillable = [
        'name',
        'email',
        'is_active',
        'is_employee',
        'is_lawyer',
        'password',
        'website_title',
        'website_description'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_employee' => 'boolean',
            'is_lawyer' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessPredaPanel($panel->getId());
    }

    public function canAccessPredaPanel(string $panelId): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->hasRole(config('filament-shield.super_admin.name', 'super_admin'))) {
            return true;
        }

        return match ($panelId) {
            'kancelaria', 'crm', 'cms' => $this->can("access_{$panelId}_panel")
                || (bool) $this->is_employee,
            default => false,
        };
    }
}
