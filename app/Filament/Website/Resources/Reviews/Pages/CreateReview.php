<?php

namespace App\Filament\Website\Resources\Reviews\Pages;

use App\Filament\Website\Resources\Reviews\ReviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;
}
