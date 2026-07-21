<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\RelationManagers;

use App\Filament\Tables\MailgunEventsTable;
use App\Support\Crm\ClientAcquisitionAccess;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MailgunEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'mailgunEvents';

    protected static ?string $title = 'Historia wiadomości';

    protected static ?string $modelLabel = 'Zdarzenie wiadomości';

    protected static ?string $pluralModelLabel = 'Historia wiadomości';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ClientAcquisitionAccess::canUse()
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return MailgunEventsTable::configure($table)
            ->paginated([10, 25, 50]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
