<?php

namespace App\Filament\Website\Resources\Posts\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tytuł')
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('category'
                    )->label('Kategoria')
                    ->badge()
                    ->color(fn (string $state): string => match($state)
                    {
                        'blog' => 'gray',
                        default => 'info'
                    }),
                ToggleColumn::make('is_published')->label('Opublikowany?'),
                TextColumn::make('date')->label('Data')->date()->sortable()
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Opublikowane?')
                    ->native(false),
                SelectFilter::make('category')
                    ->label('Kategoria')
                    ->options([
                        'blog' => 'blog',
                        'orzecznictwo' => 'orzecznictwo'
                    ])
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
            ])
            ->defaultSort('date', 'desc');

    }
}
