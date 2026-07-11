<?php

namespace App\Filament\Crm\Resources\LeadResource\Pages;

use App\Filament\Crm\Resources\LeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;

    protected static bool $canCreateAnother = false;
}
