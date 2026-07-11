<?php

namespace App\Filament\Website\Resources\Reviews\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ToggleColumn;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Autor opinii')
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->searchable()
                    ->description(fn($record) => strip_tags($record['review']))
                    ->wrap()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Liczba opinii'),
                TextColumn::make('source')
                    ->label('Źródło')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'google_business_profile' => 'Google',
                        default => $state ?: 'ręcznie / CSV',
                    }),
                TextColumn::make('rating')
                    ->label('Ocena')
                    ->badge()
                    ->alignCenter(),
                ColorColumn::make('color')
                    ->label('Kolor'),
                ToggleColumn::make('is_published')
                    ->label('Opublikowana?'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort')
            ->reorderable('sort');

    }
}
