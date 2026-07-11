<?php

namespace App\Filament\Website\Resources\Cities\Tables;

use Filament\Tables\Table;
use App\Models\Website\City;
use App\Enums\Website\Provinces;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('city')
                    ->weight('bold')
                    ->size(TextSize::Medium)
                    ->label('Miasto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province')
                    ->label('Województwo')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Opublikowany?')
                    ->sortable(),
                ToggleColumn::make('show_in_footer')
                    ->label('W stopce?')
                    ->sortable()
            ])
            ->reorderable('sort')
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('province')
                    ->label('Województwo')
                    ->native(false)
                    ->placeholder('Wszystkie')
                    ->options([
                         '' => 'Wszystkie',
                        ...collect(Provinces::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->getProvince()])
                            ->toArray()
                    ]),
                TernaryFilter::make('show_in_footer')
                    ->native(false)
                    ->label('W stopce?')

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
