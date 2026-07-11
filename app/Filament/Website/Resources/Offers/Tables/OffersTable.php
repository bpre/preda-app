<?php

namespace App\Filament\Website\Resources\Offers\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Imię i nazwisko')
                    ->searchable()
                    ->weight('bold')
                    ->size(TextSize::Medium)
                    ->description(fn($record) => $record->email)
                    ->searchable(),
                TextColumn::make('bank')
                    ->label('Umowa')
                    ->weight('bold')
                    ->description(fn($record) => 'umowa z ' . $record->year . ' r., ' . number_format($record->amount, 0, ',', ' ') . ' zł')
                    ->size(TextSize::Medium),

                TextColumn::make('variant')
                    ->placeholder('-')
                    ->label('Wariant')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Data zgłoszenia'),
                TextColumn::make('offer_sent_at')
                    ->label('Wysłano ofertę')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
