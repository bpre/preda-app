<?php

namespace App\Filament\Website\Resources\Leads\Pages;

use App\Filament\Website\Resources\Leads\LeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}
