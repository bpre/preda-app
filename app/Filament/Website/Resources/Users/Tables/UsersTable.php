<?php

namespace App\Filament\Website\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Imię i nazwisko')
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('website_title')
                    ->label('Tytuł')
                    ->searchable(),
                ToggleColumn::make('website_is_published')
                    ->label('Na stronie?')
                    ->searchable(),

            ])
            ->reorderable('website_sort')
            ->defaultSort('website_sort')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
