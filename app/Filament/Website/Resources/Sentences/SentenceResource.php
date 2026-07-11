<?php

namespace App\Filament\Website\Resources\Sentences;

use App\Filament\Website\Resources\Sentences\Pages\CreateSentence;
use App\Filament\Website\Resources\Sentences\Pages\EditSentence;
use App\Filament\Website\Resources\Sentences\Pages\ListSentences;
use App\Filament\Website\Resources\Sentences\Schemas\SentenceForm;
use App\Filament\Website\Resources\Sentences\Tables\SentencesTable;
use App\Models\Website\Sentence;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SentenceResource extends Resource
{
    protected static ?string $model = Sentence::class;

    protected static ?string $slug = 'wyroki';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Wyroki';
    protected static ?string $pluralModelLabel = 'Wyroki';
    protected static ?string $modelLabel = 'Wyrok';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SentenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SentencesTable::configure($table);
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
            'index' => ListSentences::route('/'),
            'create' => CreateSentence::route('/create'),
            'edit' => EditSentence::route('/{record}/edit'),
        ];
    }
}
