<?php

namespace App\Filament\Pages\Administration;

use App\Models\Task;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseCleanup extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected string $view = 'filament.pages.administration.database-cleanup';

    protected static ?string $navigationLabel = 'Czyszczenie bazy';

    protected static ?string $title = 'Czyszczenie bazy';

    protected static string|\UnitEnum|null $navigationGroup = 'Administracja';

    protected static ?int $navigationSort = 100;

    protected static ?string $slug = 'czyszczenie-bazy';

    public int $oldCompletedTasksCount = 0;

    public int $oldNotificationsCount = 0;

    public string $oldCompletedTasksCutoffDate = '';

    public string $oldNotificationsCutoffDate = '';

    public function mount(): void
    {
        $this->refreshCounts();
    }

    public function deleteOldCompletedTasksAction(): Action
    {
        return Action::make('deleteOldCompletedTasks')
            ->label('Usuń stare zadania')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Usunąć wykonane zadania starsze niż rok?')
            ->modalDescription('Usunięte zostaną zadania z datą wykonania wcześniejszą niż wskazana na stronie. Operacji nie można cofnąć.')
            ->modalSubmitActionLabel('Usuń zadania')
            ->disabled(fn (): bool => $this->oldCompletedTasksCount === 0)
            ->action(function (): void {
                $this->deleteOldCompletedTasks();
            });
    }

    public function deleteOldNotificationsAction(): Action
    {
        return Action::make('deleteOldNotifications')
            ->label('Usuń stare notyfikacje')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Usunąć notyfikacje starsze niż miesiąc?')
            ->modalDescription('Usunięte zostaną notyfikacje z datą utworzenia wcześniejszą niż wskazana na stronie. Operacji nie można cofnąć.')
            ->modalSubmitActionLabel('Usuń notyfikacje')
            ->disabled(fn (): bool => $this->oldNotificationsCount === 0)
            ->action(function (): void {
                $this->deleteOldNotifications();
            });
    }

    public function optimizeTablesAction(): Action
    {
        return Action::make('optimizeTables')
            ->label('Optymalizuj tabele')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Zoptymalizować tabele po czyszczeniu?')
            ->modalDescription('Operacja uruchomi OPTIMIZE TABLE dla tabel związanych z czyszczeniem. W MySQL może to chwilowo przebudować albo zablokować tabele.')
            ->modalSubmitActionLabel('Optymalizuj tabele')
            ->action(function (): void {
                $this->optimizeTables();
            });
    }

    public function deleteOldCompletedTasks(): void
    {
        $cutoff = now()->subYear();

        [$deletedTasks, $deletedComments, $deletedNotifications] = DB::transaction(function () use ($cutoff): array {
            $oldTaskIds = function ($query) use ($cutoff): void {
                $query
                    ->select('id')
                    ->from('tasks')
                    ->whereNotNull('done_at')
                    ->where('done_at', '<', $cutoff);
            };

            $deletedComments = DB::table(config('filament-comments.table_name', 'filament_comments'))
                ->where('subject_type', Task::class)
                ->whereIn('subject_id', $oldTaskIds)
                ->delete();

            $deletedNotifications = DB::table('notifications')
                ->whereIn('data->task', $oldTaskIds)
                ->delete();

            $deletedTasks = DB::table('tasks')
                ->whereNotNull('done_at')
                ->where('done_at', '<', $cutoff)
                ->delete();

            return [$deletedTasks, $deletedComments, $deletedNotifications];
        });

        $this->refreshCounts();

        Notification::make()
            ->success()
            ->title('Usunięto stare zadania')
            ->body("Zadania: {$deletedTasks}. Komentarze: {$deletedComments}. Notyfikacje zadań: {$deletedNotifications}.")
            ->send();
    }

    public function deleteOldNotifications(): void
    {
        $deleted = DB::table('notifications')
            ->where('created_at', '<', now()->subMonth())
            ->delete();

        $this->refreshCounts();

        Notification::make()
            ->success()
            ->title('Usunięto stare notyfikacje')
            ->body("Liczba usuniętych rekordów: {$deleted}.")
            ->send();
    }

    public function optimizeTables(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            Notification::make()
                ->warning()
                ->title('Optymalizacja pominięta')
                ->body('OPTIMIZE TABLE jest dostępne tylko dla MySQL/MariaDB.')
                ->send();

            return;
        }

        $tables = $this->optimizableTables();

        if ($tables === []) {
            Notification::make()
                ->warning()
                ->title('Brak tabel do optymalizacji')
                ->send();

            return;
        }

        $quotedTables = collect($tables)
            ->map(fn (string $table): string => '`'.str_replace('`', '``', $table).'`')
            ->implode(', ');

        DB::statement("OPTIMIZE TABLE {$quotedTables}");

        Notification::make()
            ->success()
            ->title('Optymalizacja zakończona')
            ->body('Zoptymalizowano tabele: '.implode(', ', $tables).'.')
            ->send();
    }

    protected function refreshCounts(): void
    {
        $oldCompletedTasksCutoff = now()->subYear();
        $oldNotificationsCutoff = now()->subMonth();

        $this->oldCompletedTasksCutoffDate = $oldCompletedTasksCutoff->format('d.m.Y');
        $this->oldNotificationsCutoffDate = $oldNotificationsCutoff->format('d.m.Y');

        $this->oldCompletedTasksCount = Task::query()
            ->whereNotNull('done_at')
            ->where('done_at', '<', $oldCompletedTasksCutoff)
            ->count();

        $this->oldNotificationsCount = DB::table('notifications')
            ->where('created_at', '<', $oldNotificationsCutoff)
            ->count();
    }

    protected function optimizableTables(): array
    {
        return collect([
            'tasks',
            'notifications',
            config('filament-comments.table_name', 'filament_comments'),
        ])
            ->unique()
            ->filter(fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(config('filament-shield.super_admin.name', 'super_admin')) === true;
    }
}
