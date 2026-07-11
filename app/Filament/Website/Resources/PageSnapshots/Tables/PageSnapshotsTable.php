<?php

namespace App\Filament\Website\Resources\PageSnapshots\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use App\Models\Website\PageSnapshot;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class PageSnapshotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('url')->label('URL')->searchable(),

                TextColumn::make('category')->label('Kategoria  ')->badge(),

                TextColumn::make('h1')
                    ->label('H1')
                    ->wrap()
                    ->tooltip(fn ($record) => $record?->h1),

                TextColumn::make('h1_length')->label('(H1)'),
                IconColumn::make('is_h1_unique')->label('un(H1)')->boolean(),

                TextColumn::make('h2')
                    ->label('H2')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record?->h2),

                TextColumn::make('h2_length')->label('(H2)')->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label('Meta title')
                    ->wrap()
                    ->tooltip(fn ($record) => $record?->title),
                TextColumn::make('title_length')->label('(title)'),
                IconColumn::make('is_title_unique')->label('un(title)')->boolean(),


                TextColumn::make('meta_description')
                    ->label('Meta description')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record?->meta_description),

                TextColumn::make('meta_description_length')->label('(desc)')->toggleable(isToggledHiddenByDefault: true),





            ])
            ->filters([


                // == Kategorie ==
                SelectFilter::make('category')
                    ->label('Kategoria')
                    ->options(fn () => PageSnapshot::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->toArray()
                    )
                    ->native(false)
                    ->preload()        // wczyta opcje od razu
                    ->indicator('Kategoria'),

                // == Brak H1 ==
                TernaryFilter::make('missing_h1')
                    ->label('H1')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Bez H1')
                    ->falseLabel('Z H1')
                    ->queries(
                        true: fn (Builder $q) => $q->whereRaw("(h1 IS NULL OR TRIM(h1) = '')"),
                        false: fn (Builder $q) => $q->whereRaw("(h1 IS NOT NULL AND TRIM(h1) <> '')"),
                        blank: fn (Builder $q) => $q,
                    )
                    ->indicator(fn ($state) => $state === true ? 'Bez H1' : ($state === false ? 'Z H1' : null)),

                // == Brak title ==
                TernaryFilter::make('missing_title')
                    ->label('Title')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Bez title')
                    ->falseLabel('Z title')
                    ->queries(
                        true: fn (Builder $q) => $q->whereRaw("(title IS NULL OR TRIM(title) = '')"),
                        false: fn (Builder $q) => $q->whereRaw("(title IS NOT NULL AND TRIM(title) <> '')"),
                        blank: fn (Builder $q) => $q,
                    )
                    ->indicator(fn ($state) => $state === true ? 'Bez title' : ($state === false ? 'Z title' : null)),

                // == Brak meta description ==
                TernaryFilter::make('missing_meta_description')
                    ->label('Meta description')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Bez meta desc')
                    ->falseLabel('Z meta desc')
                    ->queries(
                        true: fn (Builder $q) => $q->whereRaw("(meta_description IS NULL OR TRIM(meta_description) = '')"),
                        false: fn (Builder $q) => $q->whereRaw("(meta_description IS NOT NULL AND TRIM(meta_description) <> '')"),
                        blank: fn (Builder $q) => $q,
                    )
                    ->indicator(fn ($state) => $state === true ? 'Bez meta desc' : ($state === false ? 'Z meta desc' : null)),

                // == Unikalność H1 ==
                TernaryFilter::make('uniq_h1')
                    ->label('Unikalność H1')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Nieunikalne')
                    ->falseLabel('Unikalne')
                    ->queries(
                        true: fn (Builder $q) => $q->where('is_h1_unique', false),
                        false: fn (Builder $q) => $q->where('is_h1_unique', true),
                        blank: fn (Builder $q) => $q,
                    )
                    ->indicator(fn ($state) => $state === true ? 'Nieunikalne H1' : ($state === false ? 'Unikalne H1' : null)),

                // == Unikalność title ==
                TernaryFilter::make('uniq_title')
                    ->label('Unikalność title')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Nieunikalne')
                    ->falseLabel('Unikalne')
                    ->queries(
                        true: fn (Builder $q) => $q->where('is_title_unique', false),
                        false: fn (Builder $q) => $q->where('is_title_unique', true),
                        blank: fn (Builder $q) => $q,
                    )
                    ->indicator(fn ($state) => $state === true ? 'Nieunikalne title' : ($state === false ? 'Unikalne title' : null)),



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
