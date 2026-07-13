<?php

namespace App\Filament\Website\Resources\Leads\Actions;

use App\Models\User;
use App\Models\Website\Lead;
use App\Services\Website\LeadPotentialMatterService;
use App\Support\Website\LeadStatuses;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class RejectLeadAction
{
    public static function make(): Action
    {
        return Action::make('rejectLead')
            ->label('Odrzuć lead')
            ->icon('heroicon-m-no-symbol')
            ->color('danger')
            ->modalHeading('Odrzuć lead')
            ->modalDescription('Lead zostanie oznaczony jako odrzucony. Ta akcja jest dostępna tylko przed kwalifikacją leada.')
            ->modalWidth('lg')
            ->fillForm(fn (Lead $record): array => [
                'reason' => $record->rejection_reason ?: LeadStatuses::REASON_NOT_PROMISING,
                'rejected_at' => $record->rejected_at ?? now(),
                'note' => $record->rejection_note,
            ])
            ->schema([
                Select::make('reason')
                    ->label('Powód odrzucenia')
                    ->options(LeadStatuses::rejectionReasons())
                    ->native(false)
                    ->required(),
                DateTimePicker::make('rejected_at')
                    ->label('Data odrzucenia')
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false)
                    ->native(false)
                    ->required(),
                Textarea::make('note')
                    ->label('Notatka')
                    ->rows(3)
                    ->placeholder('Opcjonalnie: krótka informacja uzupełniająca.'),
            ])
            ->modalSubmitActionLabel('Odrzuć lead')
            ->visible(fn (Lead $record): bool => ! LeadStatuses::isRejected($record->status)
                && blank($record->potential_matter_id))
            ->action(function (array $data, Lead $record): void {
                $actor = auth()->user() instanceof User ? auth()->user() : null;

                app(LeadPotentialMatterService::class)->rejectLead(
                    lead: $record,
                    reason: $data['reason'],
                    actor: $actor,
                    changedAt: $data['rejected_at'],
                    note: $data['note'] ?? null,
                );

                Notification::make()
                    ->success()
                    ->title('Lead został odrzucony')
                    ->send();
            });
    }
}
