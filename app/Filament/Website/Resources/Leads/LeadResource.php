<?php

namespace App\Filament\Website\Resources\Leads;

use App\Filament\Website\Resources\Leads\Pages\CreateLead;
use App\Filament\Website\Resources\Leads\Pages\EditLead;
use App\Filament\Website\Resources\Leads\Pages\ListLeads;
use App\Filament\Website\Resources\Leads\Pages\ViewLead;
use App\Filament\Website\Resources\Leads\RelationManagers\MailgunEventsRelationManager;
use App\Filament\Website\Resources\Leads\RelationManagers\StatusChangesRelationManager;
use App\Filament\Website\Resources\Leads\Schemas\LeadForm;
use App\Filament\Website\Resources\Leads\Tables\LeadsTable;
use App\Models\Website\Lead;
use App\Support\Crm\MarketingAgencyAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $slug = 'leady';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Leady';

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leady';

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
        if (MarketingAgencyAccess::usesRestrictedLeadView()) {
            return [];
        }

        return [
            StatusChangesRelationManager::class,
            MailgunEventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
