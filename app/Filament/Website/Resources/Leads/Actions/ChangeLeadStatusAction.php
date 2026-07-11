<?php

namespace App\Filament\Website\Resources\Leads\Actions;

use App\Models\Website\Lead;
use App\Support\Website\LeadStatuses;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ChangeLeadStatusAction
{
    public static function make(): Action
    {
        return Action::make('changeLeadStatus')
            ->label('Zmień status')
            ->icon('heroicon-m-arrow-path')
            ->color('warning')
            ->modalHeading('Zmień status leada')
            ->modalWidth('lg')
            ->fillForm(fn (Lead $record): array => [
                'status' => LeadStatuses::normalize($record->status),
                'changed_at' => $record->status_changed_at ?? now(),
            ])
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(LeadStatuses::options())
                    ->native(false)
                    ->required(),
                DateTimePicker::make('changed_at')
                    ->label('Data zmiany statusu')
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false)
                    ->native(false)
                    ->required(),
                Textarea::make('note')
                    ->label('Notatka')
                    ->rows(3)
                    ->placeholder('Opcjonalnie: krótka informacja o zmianie statusu.'),
            ])
            ->modalSubmitActionLabel('Zapisz status')
            ->action(function (array $data, Lead $record): void {
                $record->changeStatus(
                    $data['status'],
                    $data['changed_at'],
                    auth()->id(),
                    $data['note'] ?? null,
                );

                Notification::make()
                    ->success()
                    ->title('Status leada został zmieniony')
                    ->send();
            });
    }
}
