<?php

namespace App\Filament\Website\Resources\Posts;

use App\Filament\Website\Resources\Posts\Pages\CreatePost;
use App\Filament\Website\Resources\Posts\Pages\EditPost;
use App\Filament\Website\Resources\Posts\Pages\ListPosts;
use App\Filament\Website\Resources\Posts\Schemas\PostForm;
use App\Filament\Website\Resources\Posts\Tables\PostsTable;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\Website\Post;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Artykuły';
    protected static ?string $pluralModelLabel = 'Artykuły';
    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
