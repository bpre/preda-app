<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\Pages;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCHFPotentialMatter extends CreateRecord
{
    protected static ?string $title = 'Nowa potencjalna sprawa';

    protected static string $resource = CHFPotentialMatterResource::class;
}
