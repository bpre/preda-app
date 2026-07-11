<?php

namespace App\Filament\Website\Resources\Leads;

use BackedEnum;
use Filament\Tables\Table;
use App\Models\Website\Lead;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Website\Resources\Leads\Pages\EditLead;
use App\Filament\Website\Resources\Leads\Pages\ViewLead;
use App\Filament\Website\Resources\Leads\Pages\ListLeads;
use App\Filament\Website\Resources\Leads\Pages\CreateLead;
use App\Filament\Website\Resources\Leads\Schemas\LeadForm;
use App\Filament\Website\Resources\Leads\Tables\LeadsTable;
use App\Filament\Website\Resources\Leads\RelationManagers\StatusChangesRelationManager;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static ?string $slug = 'umowy-do-analizy';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Umowy do analizy';
    protected static ?string $pluralModelLabel = 'Umowy do analizy';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $hasTitleCaseModelLabel = false;


    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StatusChangesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'view'   => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
