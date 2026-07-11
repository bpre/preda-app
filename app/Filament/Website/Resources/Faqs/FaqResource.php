<?php

namespace App\Filament\Website\Resources\Faqs;

use App\Filament\Website\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Website\Resources\Faqs\Pages\EditFaq;
use App\Filament\Website\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Website\Resources\Faqs\Schemas\FaqForm;
use App\Filament\Website\Resources\Faqs\Tables\FaqsTable;
use App\Models\Website\Faq;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static ?string $slug = 'faq';

    protected static ?string $recordTitleAttribute = 'question';

    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $pluralModelLabel = 'FAQ';


    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FaqForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FaqsTable::configure($table);
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
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}
