<?php

namespace App\Filament\Resources\MatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Czynności';

    protected static ?string $modelLabel = 'Czynność';
    protected static ?string $pluralModelLabel = 'Czynności';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->columnSpanFull()
                    ->default(now()),
                Textarea::make('description')
                    ->placeholder('Np.: „Wyznaczono termin rozprawy.”')
                    ->label('Opis czynności')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_visible_for_client')
                    ->label('Widoczna dla klienta?')
                    ->live()
                    ->columnSpanFull(),
                DatePicker::make('visible_for_client_from')
                    ->label('Od kiedy?')
                    ->visible(fn(Get $get) => $get('is_visible_for_client'))
                    ->columnSpanFull()
                    ->default(now())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Data')
                    ->sortable()
                    ->size('md')
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label('Opis czynności'),
                IconColumn::make('is_visible_for_client')
                    ->label('Widoczna dla klienta?')
                    ->boolean()
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Nowa czynność')
                    ->modalWidth('md'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edytuj czynność')
                    ->modalWidth('md')
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
