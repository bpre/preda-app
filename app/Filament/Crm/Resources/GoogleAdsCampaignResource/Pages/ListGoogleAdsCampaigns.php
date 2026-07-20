<?php

namespace App\Filament\Crm\Resources\GoogleAdsCampaignResource\Pages;

use App\Filament\Crm\Resources\GoogleAdsCampaignResource;
use App\Services\Integrations\GoogleAdsCampaignSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListGoogleAdsCampaigns extends ListRecords
{
    protected static string $resource = GoogleAdsCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncGoogleAdsCampaigns')
                ->label('Synchronizuj z Google')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('success')
                ->outlined()
                ->action(function (): void {
                    try {
                        $result = app(GoogleAdsCampaignSyncService::class)->syncCampaigns();

                        Notification::make()
                            ->success()
                            ->title('Synchronizacja Google Ads zakończona')
                            ->body(self::syncSummary($result))
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Synchronizacja Google Ads nie powiodła się')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
            Action::make('syncGoogleAdsCampaignHistory')
                ->label('Synchronizuj historię')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading('Synchronizować historię Google Ads?')
                ->modalDescription('Pobrane zostaną miesięczne metryki kampanii za maksymalnie 11 lat wstecz.')
                ->action(function (): void {
                    try {
                        $result = app(GoogleAdsCampaignSyncService::class)->syncMonthlyMetrics();

                        Notification::make()
                            ->success()
                            ->title('Historia Google Ads została zsynchronizowana')
                            ->body("Dodano rekordów miesięcznych: {$result['created']}, zaktualizowano: {$result['updated']}, pominięto: {$result['skipped']}. Zakres: {$result['from']} - {$result['to']}.")
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Synchronizacja historii Google Ads nie powiodła się')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
            Action::make('backfillLeadCampaigns')
                ->label('Powiąż leady')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->action(function (): void {
                    $linkedLeads = app(GoogleAdsCampaignSyncService::class)->backfillLeadCampaignIds();

                    Notification::make()
                        ->success()
                        ->title('Powiązanie leadów zakończone')
                        ->body("Powiązano leady: {$linkedLeads}.")
                        ->send();
                }),
        ];
    }

    private static function syncSummary(array $result): string
    {
        $monthly = $result['monthly_metrics'] ?? [];

        return "Kampanie - dodano: {$result['created']}, zaktualizowano: {$result['updated']}, pominięto: {$result['skipped']}. "
            ."Metryki miesięczne - dodano: ".($monthly['created'] ?? 0).", zaktualizowano: ".($monthly['updated'] ?? 0).", pominięto: ".($monthly['skipped'] ?? 0).". "
            ."Powiązano leady: {$result['linked_leads']}.";
    }
}
