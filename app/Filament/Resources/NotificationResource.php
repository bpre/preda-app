<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class NotificationResource extends \RalphJSmit\Filament\Notifications\Filament\Resources\NotificationResource
{


    protected static ?string $navigationLabel = 'Powiadomienia systemowe';
    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    public static function can(string|\UnitEnum $action, ?Model $record = null): bool
    {
        return auth()->id() === 1;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::can('view');
    }
}

?>
