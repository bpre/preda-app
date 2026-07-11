<?php

namespace App\Filament\Pages\Administration;

use App\Models\Matter;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class MattersWithoutNotificationRecipients extends Page
{
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected string $view = 'filament.pages.administration.matters-without-notification-recipients';

    protected static ?string $navigationLabel = 'Sprawy bez odbiorcy powiadomień';

    protected static ?string $title = 'Sprawy bez odbiorcy powiadomień';

    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    protected static ?string $navigationParentItem = 'Powiadomienia (pisma)';

    protected static ?int $navigationSort = 4;

    public Collection $matters;

    public function mount(): void
    {
        $this->matters = Matter::query()
            ->where('is_matter', 1)
            ->whereDoesntHave('notificationContactMatters')
            ->orderBy('label')
            ->get();
    }
}
