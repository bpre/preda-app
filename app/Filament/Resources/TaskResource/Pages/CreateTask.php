<?php

namespace App\Filament\Resources\TaskResource\Pages;

use Filament\Actions;
use App\Filament\Resources\TaskResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

}
