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

class MarkLeadAsIncorrectlyQualifiedAction
{
    public static function make(): Action
    {
        return Action::make('markLeadAsIncorrectlyQualified')
            ->label('Oznacz jako błędnie zakwalifikowany')
            ->icon('heroicon-m-exclamation-triangle')
            ->color('danger')
            ->modalHeading('Oznaczyć jako błędnie zakwalifikowany?')
            ->modalDescription('Lead zostanie oznaczony jako odrzucony, a powiązana potencjalna sprawa zostanie zamknięta i zarchiwizowana. Ta akcja jest dostępna tylko dla administratora.')
            ->modalWidth('lg')
            ->fillForm(fn (Lead $record): array => [
                'reason' => $record->rejection_reason ?: LeadStatuses::REASON_OTHER,
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
                    ->placeholder('Opcjonalnie: dlaczego kwalifikacja była błędna.'),
            ])
            ->modalSubmitActionLabel('Oznacz')
            ->visible(fn (Lead $record): bool => self::isVisible($record))
            ->action(function (array $data, Lead $record): void {
                $actor = auth()->user() instanceof User ? auth()->user() : null;

                app(LeadPotentialMatterService::class)->markLeadAsIncorrectlyQualified(
                    lead: $record,
                    reason: $data['reason'],
                    actor: $actor,
                    changedAt: $data['rejected_at'],
                    note: $data['note'] ?? null,
                );

                Notification::make()
                    ->success()
                    ->title('Lead oznaczony jako błędnie zakwalifikowany')
                    ->send();
            });
    }

    private static function isVisible(Lead $record): bool
    {
        return auth()->user()?->isAdmin() === true
            && ! LeadStatuses::isRejected($record->status)
            && filled($record->potential_matter_id)
            && $record->potentialMatter
            && ! $record->potentialMatter->is_matter;
    }
}
