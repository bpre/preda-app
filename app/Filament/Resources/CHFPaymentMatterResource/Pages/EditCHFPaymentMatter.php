<?php

namespace App\Filament\Resources\CHFPaymentMatterResource\Pages;

use App\Filament\Resources\CHFPaymentMatterResource;
use App\Models\Matter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCHFPaymentMatter extends EditRecord
{
    protected static string $resource = CHFPaymentMatterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),

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
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
