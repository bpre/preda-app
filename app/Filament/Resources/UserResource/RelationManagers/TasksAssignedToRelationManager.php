<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\TaskResource;
use Filament\Resources\RelationManagers\RelationManager;

class TasksAssignedToRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks_assigned_to';
    protected static ?string $title = 'Zadania użytkownika';

    public function form(Schema $schema): Schema
    {
        return TaskResource::form($schema);
    }

    public function table(Table $table): Table
    {

        return TaskResource::table($table);

        // return $table
        //     ->recordTitleAttribute('Zadanie')
        //     ->columns([
        //         TextColumn::make('label'),
        //     ])
        //     ->filters([
        //         //
        //     ])
        //     ->headerActions([
        //         Tables\Actions\CreateAction::make(),
        //     ])
        //     ->actions([
        //         Tables\Actions\EditAction::make()->iconButton(),
        //         Tables\Actions\DeleteAction::make()->iconButton(),
        //     ])
        //     ->bulkActions([
        //         Tables\Actions\BulkActionGroup::make([
        //             Tables\Actions\DeleteBulkAction::make(),
        //         ]),
        //     ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return TaskResource::infolist($schema);
    }
}
