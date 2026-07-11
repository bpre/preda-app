<?php

namespace App\Filament\Resources\BankMatterResource\Pages;

use App\Filament\Resources\BankMatterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBankMatter extends CreateRecord
{
    protected static string $resource = BankMatterResource::class;

    protected static ?string $title = 'Nowa sprawa z powództwa banku';
}
