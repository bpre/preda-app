<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section>
            <div class="space-y-2">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Ta strona służy do jednorazowego lub wielokrotnego uzupełnienia tabeli <strong>contact_matter</strong>
                    na podstawie istniejących relacji:
                    <strong>sprawa → kredyty → kontakty kredytu → kontakt</strong>.
                </p>

                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Import bierze pod uwagę wyłącznie kontakty z kategorią <strong>Kredytobiorca</strong>.
                    Nowe rekordy dostają domyślnie <strong>receives_notifications = false</strong>.
                </p>
            </div>
        </x-filament::section>

        <x-filament::section heading="Ostatni wynik importu">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="p-4 border rounded-xl">
                    <div class="text-sm text-gray-500">Sprawdzono spraw</div>
                    <div class="text-2xl font-semibold">{{ $summary['matters_checked'] ?? 0 }}</div>
                </div>

                <div class="p-4 border rounded-xl">
                    <div class="text-sm text-gray-500">Znaleziono kandydatów</div>
                    <div class="text-2xl font-semibold">{{ $summary['candidates_found'] ?? 0 }}</div>
                </div>

                <div class="p-4 border rounded-xl">
                    <div class="text-sm text-gray-500">Dodano rekordów</div>
                    <div class="text-2xl font-semibold">{{ $summary['inserted'] ?? 0 }}</div>
                </div>

                <div class="p-4 border rounded-xl">
                    <div class="text-sm text-gray-500">Pominięto istniejące</div>
                    <div class="text-2xl font-semibold">{{ $summary['skipped_existing'] ?? 0 }}</div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
