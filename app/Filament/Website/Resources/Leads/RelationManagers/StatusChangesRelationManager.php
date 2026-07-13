<?php

namespace App\Filament\Website\Resources\Leads\RelationManagers;

use App\Support\Website\LeadStatuses;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusChanges';

    protected static ?string $title = 'Historia kwalifikacji';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => LeadStatuses::color($state))
                    ->wrap(),
                TextColumn::make('changed_at')
                    ->label('Data zmiany')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Zmienił')
                    ->placeholder('System'),
                TextColumn::make('note')
                    ->label('Notatka')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->defaultSort('changed_at', 'desc');
    }
}
