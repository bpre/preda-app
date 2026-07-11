<?php

namespace App\Filament\Website\Resources\Reviews;

use App\Filament\Website\Resources\Reviews\Pages\CreateReview;
use App\Filament\Website\Resources\Reviews\Pages\EditReview;
use App\Filament\Website\Resources\Reviews\Pages\ListReviews;
use App\Filament\Website\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Website\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Website\Review;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $slug = 'opinie';

    protected static ?string $navigationLabel = 'Opinie';
    protected static ?string $pluralModelLabel = 'Opinie';
    protected static ?string $modelLabel = 'Opinia';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
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
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
