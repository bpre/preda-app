<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Filament\Resources\MatterResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MattersRelationManager extends RelationManager
{
    protected static string $relationship = 'chfMatters';

    protected static ?string $title = 'Sprawy';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {

        return $table
            ->columns([

                TextColumn::make('label')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->searchable()
                    ->label('Sprawa'),

                TextColumn::make('start')
                    ->label('Data rozpoczęcia'),
                TextColumn::make('end')
                    ->label('Data zakończenia')
                    ->placeholder('-'),

            ])
            ->defaultSort('start', 'desc')
            ->recordUrl(fn ($record): string => MatterResource::getUrl('edit', ['record' => $record]));
    }
}
