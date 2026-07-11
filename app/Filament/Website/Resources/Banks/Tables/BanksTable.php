<?php

namespace App\Filament\Website\Resources\Banks\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class BanksTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('label')
                    ->weight('bold')
                    ->size(TextSize::Medium)
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Opublikowany?')
                    ->sortable(),

                TextColumn::make('credits_chf_count')
                    ->label('CHF')
                    ->counts('credits_chf')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('credits_eur_count')
                    ->label('EUR')
                    ->counts('credits_eur')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('sentences_count')
                    ->label('Wyroki')
                    ->counts('sentences')
                    // ->state(function ($record) {
                    //     try {
                    //         return $record->sentences()->count();
                    //     } catch (\Exception $e) {
                    //         return 'błąd: ' . $e->getMessage();
                    //     }
                    // })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('sentences_prev_count')
                    ->label('Wyroki (pop.)')
                    ->counts('sentences_prev')
                    ->sortable()
                    ->alignCenter(),


            ])
            ->reorderable('sort')
            ->defaultSort('sort')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

    }
}
