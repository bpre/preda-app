<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Poniżej znajdują się kontakty z kategorią <strong>Kredytobiorca</strong>, które nie mają uzupełnionego pola
                <strong>płeć</strong>. Z tego miejsca można szybko uzupełnić dane pojedynczo albo zbiorczo.
            </p>

            {{ $this->table }}
        </div>
    </x-filament::section>
</x-filament-panels::page>
