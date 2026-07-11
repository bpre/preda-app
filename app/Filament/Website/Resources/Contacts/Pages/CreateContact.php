<?php

namespace App\Filament\Website\Resources\Contacts\Pages;

use App\Filament\Website\Resources\Contacts\ContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
