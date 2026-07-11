<?php

namespace App\Filament\Resources\LetterNotificationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Models\LetterNotification;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use App\Filament\Resources\LetterNotificationResource;
use Livewire\Attributes\On;

class ListLetterNotifications extends ListRecords
{
    use InteractsWithAdvancedTablePresetTabs;


    protected static string $resource = LetterNotificationResource::class;

    public function hydrate(): void
    {
        $this->refreshLetterNotificationPresetViews();
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getPresetViewCounts(): array
    {
        return LetterNotification::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereIn('status', [
                LetterNotification::STATUS_PENDING,
                LetterNotification::STATUS_MISSING_RECIPIENT,
                LetterNotification::STATUS_DRAFT,
                LetterNotification::STATUS_QUEUED,
                LetterNotification::STATUS_SENDING,
                LetterNotification::STATUS_FAILED,
                LetterNotification::STATUS_CANCELLED,
            ])
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
    }


    #[On('refresh-letter-notification-preset-views')]
    public function refreshLetterNotificationPresetViews(): void
    {
        unset($this->cachedTabs);
    }

    public function getTabs(): array
    {
        $counts = $this->getPresetViewCounts();

        return [
            'new' => PresetTab::make()
                ->label('Nowe')
                ->badge((string) ($counts[LetterNotification::STATUS_PENDING] ?? 0))
                ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_PENDING))
                ->icon('heroicon-o-inbox')
                ->color('primary')
                ->favorite()
                ->default(),

            'missing_recipient' => PresetTab::make()
                ->label('Brak odbiorcy')
                ->badge((string) ($counts[LetterNotification::STATUS_MISSING_RECIPIENT] ?? 0))
                ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_MISSING_RECIPIENT))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->favorite(),

            'drafts' => PresetTab::make()
                ->label('Szkice')
                ->badge((string) ($counts[LetterNotification::STATUS_DRAFT] ?? 0))
                ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_DRAFT))
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->favorite(),

            'queued' => PresetTab::make()
                ->label('Do wysyłki')
                ->badge((string) (($counts[LetterNotification::STATUS_QUEUED] ?? 0) + ($counts[LetterNotification::STATUS_SENDING] ?? 0)))
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    LetterNotification::STATUS_QUEUED,
                    LetterNotification::STATUS_SENDING,
                ]))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->favorite(),

            'failed' => PresetTab::make()
                ->label('Błędy')
                ->badge((string) ($counts[LetterNotification::STATUS_FAILED] ?? 0))
                ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_FAILED))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->favorite(),

            'sent' => PresetTab::make()
                ->label('Wysłane')
                ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_SENT))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->favorite(),

            'ignored' => PresetTab::make()
                ->label('Zignorowane')
                ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_IGNORED))
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->favorite(),

            // 'cancelled' => PresetTab::make()
            //     ->label('Anulowane')
            //     ->badge((string) ($counts[LetterNotification::STATUS_CANCELLED] ?? 0))
            //     ->modifyQueryUsing(fn ($query) => $query->where('status', LetterNotification::STATUS_CANCELLED))
            //     ->icon('heroicon-o-minus-circle')
            //     ->color('gray')
            //     ->favorite(),

            'all' => PresetTab::make()
                ->label('Wszystkie')
                ->icon('heroicon-o-list-bullet')
                ->favorite(),
        ];
    }
}
