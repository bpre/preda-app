<?php

namespace App\Filament\Website\Resources\Faqs\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('question')
                    ->label('Pytanie')
                    ->weight('bold')
                    ->size(TextSize::Medium),
                TextColumn::make('prefix')
                    ->label('Lista')
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-')

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort')
            ->reorderable('sort');
;
    }
}
