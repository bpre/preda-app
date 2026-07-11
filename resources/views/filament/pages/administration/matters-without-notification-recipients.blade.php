<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Poniżej znajdują się przyjęte sprawy, które nie mają przypisanego ani jednego klienta
                z włączoną opcją <strong>receives_notifications = 1</strong> i uzupełnionym adresem e-mail.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="px-3 py-2 text-left">Sprawa</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Prawnik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->matters as $matter)
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    {{ $matter->label }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $matter->status }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $matter->lawyer?->name }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">
                                    Wszystkie przyjęte sprawy mają co najmniej jednego odbiorcę powiadomień.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
