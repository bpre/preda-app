<?php

namespace App\Filament\Resources\MatterResource\RelationManagers;

use App\Models\Activity;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Notatki i czynności';

    protected static ?string $modelLabel = 'Notatka / czynność';
    protected static ?string $pluralModelLabel = 'Notatki i czynności';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->columnSpanFull()
                    ->default(now()),
                Select::make('type')
                    ->label('Typ')
                    ->options(Activity::TYPE_LABELS)
                    ->default(Activity::TYPE_NOTE)
                    ->native(false)
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->placeholder(fn (): string => $this->isPotentialMatter()
                        ? 'Np. „Klient zadzwonił i poprosił o ponowne przesłanie oferty.”'
                        : 'Np. „Wyznaczono termin rozprawy.”')
                    ->label(fn (): string => $this->isPotentialMatter() ? 'Notatka / opis kontaktu' : 'Opis czynności')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Toggle::make('is_visible_for_client')
                    ->label('Widoczna dla klienta?')
                    ->live()
                    ->hidden(fn (): bool => $this->isPotentialMatter())
                    ->columnSpanFull(),
                DatePicker::make('visible_for_client_from')
                    ->label('Od kiedy?')
                    ->visible(fn (Get $get): bool => ! $this->isPotentialMatter() && (bool) $get('is_visible_for_client'))
                    ->columnSpanFull()
                    ->default(now())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->label('Opis')
                    ->searchable()
                    ->wrap()
                    ->size('md')
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Activity::typeLabel($state))
                    ->color(fn (?string $state): string => match ($state) {
                        Activity::TYPE_PHONE_CALL, Activity::TYPE_MEETING => 'info',
                        Activity::TYPE_SMS, Activity::TYPE_EMAIL => 'warning',
                        Activity::TYPE_POST_MEETING_NOTE => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('creator.name')
                    ->label('Dodał(a)')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_visible_for_client')
                    ->label('Widoczna dla klienta?')
                    ->hidden(fn (): bool => $this->isPotentialMatter())
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj notatkę / czynność')
                    ->modalHeading(fn (): string => $this->isPotentialMatter() ? 'Nowa notatka / czynność CRM' : 'Nowa notatka / czynność')
                    ->mutateFormDataUsing(fn (array $data): array => [
                        ...$data,
                        'created_by' => auth()->id(),
                    ])
                    ->modalWidth('md'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edytuj notatkę / czynność')
                    ->modalWidth('md')
                    ->iconButton()
                    ->mutateRecordDataUsing(function (array $data, Activity $record): array {
                        $record->markAsReadBy(auth()->user());

                        return $data;
                    }),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function isPotentialMatter(): bool
    {
        return $this->ownerRecord
            && $this->ownerRecord->category === 'CHF'
            && ! $this->ownerRecord->is_matter;
    }
}
