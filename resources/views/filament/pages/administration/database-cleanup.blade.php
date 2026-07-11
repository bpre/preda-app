<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Zadania wykonane dawniej niż rok temu
            </x-slot>

            <div class="space-y-2">
                <div class="text-3xl font-semibold text-gray-950 dark:text-white">
                    {{ number_format($this->oldCompletedTasksCount, 0, ',', ' ') }}
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data wykonania wcześniejsza niż {{ $this->oldCompletedTasksCutoffDate }}.
                </p>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end">
                    {{ $this->getAction('deleteOldCompletedTasks', false) }}
                </div>
            </x-slot>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Notyfikacje starsze niż miesiąc
            </x-slot>

            <div class="space-y-2">
                <div class="text-3xl font-semibold text-gray-950 dark:text-white">
                    {{ number_format($this->oldNotificationsCount, 0, ',', ' ') }}
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data utworzenia wcześniejsza niż {{ $this->oldNotificationsCutoffDate }}.
                </p>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end">
                    {{ $this->getAction('deleteOldNotifications', false) }}
                </div>
            </x-slot>
        </x-filament::section>

        <x-filament::section class="md:col-span-2">
            <x-slot name="heading">
                Optymalizacja tabel
            </x-slot>

            <div class="space-y-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Odbudowuje tabele po czyszczeniu, żeby MySQL mógł odzyskać miejsce zaalokowane wcześniej dla usuniętych rekordów.
                </p>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Dotyczy tabel: <strong>tasks</strong>, <strong>notifications</strong>, <strong>filament_comments</strong>.
                </p>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end">
                    {{ $this->getAction('optimizeTables', false) }}
                </div>
            </x-slot>
        </x-filament::section>
    </div>
</x-filament-panels::page>
