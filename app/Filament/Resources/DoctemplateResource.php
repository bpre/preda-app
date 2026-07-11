<?php
/*
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Doctemplate;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Forms\Components\MentionsRichEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DoctemplateResource\Pages;
use App\Filament\Resources\DoctemplateResource\RelationManagers;

class DoctemplateResource extends Resource
{
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'szablony-dokumentow';
    protected static ?string $model = Doctemplate::class;
    protected static ?string $navigationLabel = 'Szablony dokumentów';
    protected static ?string $modelLabel = 'Szablon dokumentu';
    protected static ?string $pluralModelLabel = 'Szablony dokumentów';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administracja';
    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('label')->label('Nazwa'),

                MentionsRichEditor::make('body')
                    ->label('')
                    ->columnSpan(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Szablon')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctemplates::route('/'),
            'create' => Pages\CreateDoctemplate::route('/create'),
            'edit' => Pages\EditDoctemplate::route('/{record}/edit'),
        ];
    }
}
*/
