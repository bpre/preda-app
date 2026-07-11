<?php

namespace App\Filament\Website\Resources\Pipedrives\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Database\Query\Builder;

class PipedrivesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Klient')
                    ->weight(FontWeight::Bold)
                    ->size('md')
                    ->searchable(),
                TextColumn::make('sex')
                    ->label('Płeć')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Miejscowość')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),

                TextColumn::make('bank')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Rok')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Kwota')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Waluta')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('first')
                    ->label('Pierwszy kontakt')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('stage')
                    ->label('Etap')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('reviewed')
                    ->label('Data weryfikacji')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('review_status')
                    ->label('Status weryfikacji')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('offer_request')
                    ->label('Prośba o ofertę')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('remove_request')
                    ->label('Prośba o usunięcie')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable()
            ])
            ->defaultSort('created_at')
            ->filters([

                SelectFilter::make('review_status_view')
                    ->label('Status weryfikacji')
                    ->options([
                        'reviewed'     => 'Przejrzane',
                        'rejected'     => 'Odrzucone',
                        'transfered'   => 'Przekazane',
                        'not_reviewed' => 'Nieprzejrzane',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'reviewed'     => $query->whereNotNull('review_status'),
                            'not_reviewed' => $query->whereNull('review_status'),
                            'rejected'     => $query->where('review_status', 'rejected'),
                            'transfered'   => $query->where('review_status', 'transfered'),
                            default        => $query,
                        };
                    }),

                Filter::make('remove_request')
                    ->label('Prośba o usunięcie')
                    ->toggle()
                    ->query(fn (Builder $query): Builder =>
                        $query->whereNotNull('remove_request')
                    ),

                Filter::make('offer_request')
                    ->label('Prośba o ofertę')
                    ->toggle()
                    ->query(fn (Builder $query): Builder =>
                        $query->whereNotNull('offer_request')
                    ),

            ])
            ->recordActions([
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
