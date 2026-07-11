<?php

namespace App\Filament\Website\Resources\Contacts\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Enums\Website\ContactCategories;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('sort_name')
                    ->label('Kontakt')
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('category')->label('Kategoria')

            ])
            ->defaultSort('sort_name')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategoria')
                    ->options(ContactCategories::class)
                    ->native(false)
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

    }
}
