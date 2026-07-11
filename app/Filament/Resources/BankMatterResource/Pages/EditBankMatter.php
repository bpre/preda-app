<?php

namespace App\Filament\Resources\BankMatterResource\Pages;

use App\Filament\Resources\BankMatterResource;
use App\Models\Matter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBankMatter extends EditRecord
{
    protected static string $resource = BankMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('Folder sprawy')
                ->color('info')
                ->hidden(fn (Matter $record) => $record->gdrive == null)
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->url(function (Matter $record) {
                    return $record->gdrive;
                })
                ->extraAttributes([
                    'class' => 'matter-folder-action',
                    'target' => '_blank',
                ]),

            DeleteAction::make()->hidden(fn (Matter $record) => $record->hasAnyRelation()),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
