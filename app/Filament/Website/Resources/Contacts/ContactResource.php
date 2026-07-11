<?php

namespace App\Filament\Website\Resources\Contacts;

use App\Filament\Website\Resources\Contacts\Pages\CreateContact;
use App\Filament\Website\Resources\Contacts\Pages\EditContact;
use App\Filament\Website\Resources\Contacts\Pages\ListContacts;
use App\Filament\Website\Resources\Contacts\Schemas\ContactForm;
use App\Filament\Website\Resources\Contacts\Tables\ContactsTable;
use App\Models\Website\Contact;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $slug = 'sady-sedziowie';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'Sądy i sędziowie';
    protected static ?string $pluralModelLabel = 'Sądy i sędziowie';

    protected static ?string $modelLabel = 'Kontakt';

    protected static string|UnitEnum|null $navigationGroup = 'Zasoby';
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
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
            'index' => ListContacts::route('/'),
            'create' => CreateContact::route('/create'),
            'edit' => EditContact::route('/{record}/edit'),
        ];
    }
}
