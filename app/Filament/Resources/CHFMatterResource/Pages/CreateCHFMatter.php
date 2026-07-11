<?php

namespace App\Filament\Resources\CHFMatterResource\Pages;

use App\Filament\Resources\CHFMatterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCHFMatter extends CreateRecord
{
    protected static string $resource = CHFMatterResource::class;

    protected static ?string $title = 'Nowa sprawa';

    protected static bool $canCreateAnother = false;
}
