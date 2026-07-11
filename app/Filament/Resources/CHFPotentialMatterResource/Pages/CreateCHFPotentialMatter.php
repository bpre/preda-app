<?php

namespace App\Filament\Resources\CHFPotentialMatterResource\Pages;

use App\Filament\Resources\CHFPotentialMatterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCHFPotentialMatter extends CreateRecord
{

    protected static ?string $title = 'Nowa potencjalna sprawa';

    protected static string $resource = CHFPotentialMatterResource::class;
}
