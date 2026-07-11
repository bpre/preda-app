<?php

namespace App\Filament\Website\Resources\Users\Pages;

use App\Filament\Website\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
