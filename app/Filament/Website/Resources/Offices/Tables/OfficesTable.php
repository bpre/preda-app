<?php

namespace App\Filament\Website\Resources\Offices\Tables;

use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class OfficesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('city')
                    ->label('Miasto')
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Aktywny'),
                ToggleColumn::make('is_headquarters')
                    ->label('Siedziba')
            ])
            ->filters([
                //
            ])
            ->reorderable('sort')
            ->defaultSort('sort')
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
