<?php

namespace App\Filament\Website\Resources\Securities\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;

class SecuritiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('sign')
                    ->label('Sygnatura')
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('judge.label')
                    ->label('Sędzia')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable(),
                TextColumn::make('bank.label')
                    ->label('Bank')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable(),
                TextColumn::make('sentence_date')
                    ->label('Data wyroku')
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Opublikowany?'),

                IconColumn::make('files')
                    ->label('Plik')
                    ->boolean()
                    ->state(function ($record) {
                        $files = $record->files;
                        if (is_string($files)) {
                            $files = json_decode($files, true);
                        }
                        return is_array($files) && count($files) > 0;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('currency')
                    ->label('Waluta')
                    ->native(false)
                    ->options([
                        'CHF' => 'CHF',
                        'EUR' => 'EUR',
                        'USD' => 'USD'
                    ])
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
            ->defaultSort('sentence_date', 'desc')
            ->recordClasses(fn ($record) => match($record->is_published) {
                false => 'opacity-20',
                default => null
            });

///


///


    }
}
