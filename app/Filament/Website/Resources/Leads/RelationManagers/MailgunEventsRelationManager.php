<?php

namespace App\Filament\Website\Resources\Leads\RelationManagers;

use App\Filament\Tables\MailgunEventsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MailgunEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'mailgunEvents';

    protected static ?string $title = 'Historia wiadomości';

    protected static ?string $modelLabel = 'Zdarzenie wiadomości';

    protected static ?string $pluralModelLabel = 'Historia wiadomości';

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
