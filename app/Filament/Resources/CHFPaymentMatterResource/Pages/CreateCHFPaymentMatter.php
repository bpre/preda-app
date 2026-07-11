<?php

namespace App\Filament\Resources\CHFPaymentMatterResource\Pages;

use App\Filament\Resources\CHFPaymentMatterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCHFPaymentMatter extends CreateRecord
{
    protected static string $resource = CHFPaymentMatterResource::class;

    protected static ?string $title = 'Nowa sprawa o zapłatę';
}
