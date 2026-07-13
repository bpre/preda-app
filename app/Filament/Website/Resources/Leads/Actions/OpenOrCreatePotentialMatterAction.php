<?php

namespace App\Filament\Website\Resources\Leads\Actions;

use App\Filament\Resources\MatterResource;
use App\Models\Branch;
use App\Models\User;
use App\Models\Website\Lead;
use App\Services\Website\LeadPotentialMatterService;
use App\Support\Website\LeadStatuses;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;

class OpenOrCreatePotentialMatterAction
{
    public static function make(): Action
    {
        return Action::make('openOrCreatePotentialMatter')
            ->label(fn (Lead $record): string => $record->potential_matter_id
                ? 'Przejdź do potencjalnej sprawy'
                : 'Zakwalifikuj')
            ->icon(fn (Lead $record): string => $record->potential_matter_id
                ? 'heroicon-m-arrow-top-right-on-square'
                : 'heroicon-m-plus-circle')
            ->color(fn (Lead $record): string => $record->potential_matter_id ? 'info' : 'success')
            ->outlined(fn (Lead $record): bool => blank($record->potential_matter_id))
            ->modalHeading('Zakwalifikować lead?')
            ->modalDescription('Na podstawie danych leada zostanie utworzona potencjalna sprawa oraz kontakt kredytobiorcy.')
            ->modalWidth('lg')
            ->url(fn (Lead $record): ?string => $record->potentialMatter
                ? app(LeadPotentialMatterService::class)->urlForPotentialMatter($record->potentialMatter)
                : null)
            ->schema(fn (Lead $record): array => filled($record->potential_matter_id) ? [] : [
                Select::make('branch_id')
                    ->label('Oddział')
                    ->options(fn (): array => MatterResource::branchOptionsForMatter())
                    ->default(fn (): ?string => MatterResource::defaultBranch()?->getKey())
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        $branch = filled($state) ? Branch::query()->find($state) : null;

                        $set('responsible_user_id', $branch?->user_id);
                    })
                    ->live()
                    ->native(false)
                    ->required(),
                Select::make('responsible_user_id')
                    ->label('Referat')
                    ->options(fn (): array => User::responsible_lawyers()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->default(fn (): ?int => MatterResource::defaultBranch()?->user_id)
                    ->searchable()
                    ->native(false)
                    ->required(),
            ])
            ->modalSubmitActionLabel('Zakwalifikuj i otwórz')
            ->visible(fn (Lead $record): bool => self::isVisible($record))
            ->action(function (array $data, Lead $record) {
                $service = app(LeadPotentialMatterService::class);
                $actor = auth()->user() instanceof User ? auth()->user() : null;
                $hadPotentialMatter = filled($record->potential_matter_id);
                $branch = filled($data['branch_id'] ?? null)
                    ? Branch::query()->acceptingNewMatters()->findOrFail($data['branch_id'])
                    : null;
                $responsibleUser = filled($data['responsible_user_id'] ?? null)
                    ? User::query()->findOrFail($data['responsible_user_id'])
                    : null;

                $matter = $record->potentialMatter ?: $service->createForLead(
                    lead: $record,
                    actor: $actor,
                    branch: $branch,
                    responsibleUser: $responsibleUser,
                );
                $url = $service->urlForPotentialMatter($matter);

                if (! $hadPotentialMatter) {
                    Notification::make()
                        ->success()
                        ->title('Utworzono potencjalną sprawę')
                        ->body($matter->label)
                        ->send();
                }

                return redirect($url ?? '/');
            });
    }

    private static function isVisible(Lead $record): bool
    {
        $user = auth()->user();

        if ($record->potential_matter_id) {
            return (bool) $user?->can('view_c::h::f::potential::matter');
        }

        return ! LeadStatuses::isRejected($record->status)
            && (bool) $user?->can('create_c::h::f::potential::matter');
    }
}
