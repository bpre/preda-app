<?php

namespace App\Filament\Website\Resources\SentenceContentTemplates;

use App\Filament\Website\Resources\SentenceContentTemplates\Pages\CreateSentenceContentTemplate;
use App\Filament\Website\Resources\SentenceContentTemplates\Pages\EditSentenceContentTemplate;
use App\Filament\Website\Resources\SentenceContentTemplates\Pages\ListSentenceContentTemplates;
use App\Filament\Website\Resources\SentenceContentTemplates\Schemas\SentenceContentTemplateForm;
use App\Filament\Website\Resources\SentenceContentTemplates\Tables\SentenceContentTemplatesTable;
use App\Models\Website\SentenceContentTemplate;
use App\Support\Website\WebsiteFeatures;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SentenceContentTemplateResource extends Resource
{
    protected static ?string $model = SentenceContentTemplate::class;

    protected static ?string $slug = 'szablony-generatora-wyrokow';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Generator wyroków';
    protected static ?string $pluralModelLabel = 'Szablony generatora wyroków';
    protected static ?string $modelLabel = 'Szablon generatora wyroków';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function isDiscovered(): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function form(Schema $schema): Schema
    {
        return SentenceContentTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SentenceContentTemplatesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function canViewAny(): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function canCreate(): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function canEdit(Model $record): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function canDelete(Model $record): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function canDeleteAny(): bool
    {
        return WebsiteFeatures::sentenceContentGeneratorEnabled();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSentenceContentTemplates::route('/'),
            'create' => CreateSentenceContentTemplate::route('/create'),
            'edit' => EditSentenceContentTemplate::route('/{record}/edit'),
        ];
    }
}
