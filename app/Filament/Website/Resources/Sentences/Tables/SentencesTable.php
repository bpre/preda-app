<?php

namespace App\Filament\Website\Resources\Sentences\Tables;

use App\Models\Website\Sentence;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Support\HtmlString;

class SentencesTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->recordClasses(fn ($record) => match($record->is_published) {
                false => 'opacity-20',
                default => null
            })
            ->columns([
                TextColumn::make('label')
                    ->label('Opis')
                    ->description(fn (Sentence $record): string|HtmlString => self::labelDescription($record))
                    ->size(TextSize::Medium)
                    ->weight('bold')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('excerpt')
                    ->label('Opis')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('sign')
                    ->label('Sygnatura')
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
                    ->toggleable()
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Opublikowany?')
                    ->toggleable(),
                ToggleColumn::make('is_paid_off')
                    ->label('Spłacony?')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextInputColumn::make('paid_off_year')
                    ->label('Kiedy spłacony')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),

                IconColumn::make('files')
                    ->label('Plik')
                    ->boolean()
                    ->toggleable()
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
            ->defaultSort('sentence_date', 'desc')
            ->filters([
                SelectFilter::make('currency')
                    ->label('Waluta')
                    ->native(false)
                    ->options([
                        'CHF' => 'CHF',
                        'EUR' => 'EUR',
                        'USD' => 'USD'
                    ]),
                TernaryFilter::make('is_paid_off')
                    ->label('Spłacony?')
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

    private static function labelDescription(Sentence $record): string|HtmlString
    {
        $excerpt = strip_tags((string) $record->excerpt);

        if (! self::hasDuplicateLabel($record)) {
            return $excerpt;
        }

        $warning = 'W bazie jest już wpis o takim samym tytule. Postaraj się o unikalny tytuł.';

        return new HtmlString(
            '<span class="inline-flex w-fit items-center rounded-md bg-danger-50 px-2 py-0.5 text-xs font-medium text-danger-700 ring-1 ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/30">'
            . e($warning)
            . '</span><br><span>'
            . e($excerpt)
            . '</span>'
        );
    }

    private static function hasDuplicateLabel(Sentence $record): bool
    {
        $label = trim((string) $record->label);

        if ($label === '') {
            return false;
        }

        return Sentence::query()
            ->where('label', $label)
            ->whereKeyNot($record->getKey())
            ->exists();
    }
}
