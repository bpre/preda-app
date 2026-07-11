<?php

namespace App\Filament\Website\Resources\SentenceContentTemplates\Tables;

use App\Models\Website\SentenceContentTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SentenceContentTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->weight('bold')
                    ->size(TextSize::Medium)
                    ->description(fn ($record): string => strip_tags($record->content))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('section')
                    ->label('Sekcja')
                    ->formatStateUsing(fn (?string $state): string => SentenceContentTemplate::sectionOptions()[$state] ?? '-')
                    ->sortable(),
                TextColumn::make('instance')
                    ->label('Instancja')
                    ->formatStateUsing(fn ($state): string => match ((string) $state) {
                        '1' => 'I',
                        '2' => 'II',
                        '3' => 'Kasacja',
                        default => 'Dowolna',
                    })
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Priorytet')
                    ->sortable(),
                TextColumn::make('selection_mode')
                    ->label('Wybór')
                    ->formatStateUsing(fn (?string $state): string => SentenceContentTemplate::selectionModeOptions()[$state] ?? '-')
                    ->toggleable(),
                TextColumn::make('conditions')
                    ->label('Warunki')
                    ->state(fn ($record): string => self::conditionsSummary($record))
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('section')
                    ->label('Sekcja')
                    ->options(SentenceContentTemplate::sectionOptions())
                    ->native(false),
                SelectFilter::make('instance')
                    ->label('Instancja')
                    ->options([
                        1 => 'I instancja',
                        2 => 'II instancja',
                        3 => 'Postępowanie kasacyjne',
                    ])
                    ->native(false),
                TernaryFilter::make('is_active')
                    ->label('Aktywny'),
            ])
            ->defaultSort('priority', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function conditionsSummary(SentenceContentTemplate $template): string
    {
        $labels = SentenceContentTemplate::conditionOptions();
        $parts = [];

        foreach ([
            'all_of' => 'Wszystkie',
            'any_of' => 'Jeden',
            'none_of' => 'Żaden',
        ] as $field => $label) {
            $conditions = collect($template->{$field} ?? [])
                ->map(fn (string $condition): string => $labels[$condition] ?? $condition)
                ->implode(', ');

            if ($conditions !== '') {
                $parts[] = "{$label}: {$conditions}";
            }
        }

        return implode(' | ', $parts) ?: '-';
    }
}
