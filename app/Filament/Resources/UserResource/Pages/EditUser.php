<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\PanelAccess;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?array $panelAccess = null;

    public function getTitle(): string|Htmlable
    {
        return $this->record->name ?: 'Użytkownik';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('panel_access', $data)) {
            $this->panelAccess = PanelAccess::normalizePanelIds((array) $data['panel_access']);

            unset($data['panel_access']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->panelAccess === null) {
            return;
        }

        PanelAccess::syncDirect($this->record, $this->panelAccess);
    }

    protected function getHeaderActions(): array
    {
        return [
            UserResource::impersonateAction(),
            DeleteAction::make(),
        ];
    }
}
