<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PanelAccess;
use App\Support\Crm\MarketingAgencyAccess;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use RalphJSmit\Filament\Notifications\Concerns\FilamentNotifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use FilamentNotifiable;
    use HasApiTokens;
    use HasFactory, HasRoles;
    use HasPanelShield {
        canAccessPanel as canAccessPanelViaShield;
    }

    protected $fillable = [
        'name',
        'signature_title',
        'name_genitive',
        'consultation_url',
        'email',
        'phone',
        'is_active',
        'is_employee',
        'is_lawyer',
        'password',
        'website_title',
        'website_description',
        'filament_layout_preferences',
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
            'filament_layout_preferences' => 'array',
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

        if (! $this->is_employee) {
            return false;
        }

        if (! in_array($panelId, PanelAccess::panelIds(), true)) {
            return false;
        }

        if ($panelId === 'crm' && MarketingAgencyAccess::canAccessCrmPanel($this)) {
            return true;
        }

        return in_array($panelId, PanelAccess::directPanelsFor($this), true);
    }

    public function lawyer_matters()
    {
        return $this->hasMany(Matter::class, 'lawyer_id');
    }

    public function tasks_assigned_to()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function matterUser(): HasMany
    {
        return $this->hasMany(MatterUser::class);
    }

    public function userLetters()
    {
        return $this->hasManyThrough(Letter::class, MatterUser::class, 'matter_id', 'id');
    }

    public static function responsible_lawyers()
    {
        return User::where('is_lawyer', 1);
    }

    public static function is_active()
    {
        return User::where('is_active', 1);
    }

    public function isAdmin(): bool
    {
        if (($this->role ?? null) === 'admin') {
            return true;
        }

        return $this->hasRole(config('filament-shield.super_admin.name', 'super_admin'));
    }

    public function getMailSignatureTitleAttribute(): string
    {
        if (filled($this->signature_title)) {
            return (string) $this->signature_title;
        }

        if ($this->is_lawyer) {
            return 'Adwokat';
        }

        if ($this->is_employee) {
            return 'Pracownik kancelarii';
        }

        return '';
    }
}
