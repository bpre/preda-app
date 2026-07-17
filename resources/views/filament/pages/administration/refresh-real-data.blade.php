<x-filament-panels::page>
    <div class="space-y-4">
        @if ($this->availabilityErrors() !== [])
            <x-filament::section>
                <x-slot name="heading">
                    Import niedostępny
                </x-slot>

                <ul class="space-y-1 text-sm text-danger-600 dark:text-danger-400">
                    @foreach ($this->availabilityErrors() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-filament::section>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <x-filament::section>
                <x-slot name="heading">
                    Baza docelowa
                </x-slot>

                <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                    {{ $this->targetSchema }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Źródło Kancelarii
                </x-slot>

                <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                    {{ $this->kancelariaSource }}
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Źródło strony/CMS
                </x-slot>

                <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                    {{ $this->websiteSource }}
                </div>
            </x-filament::section>
        </div>

        @if ($lastError)
            <x-filament::section>
                <x-slot name="heading">
                    Ostatni błąd
                </x-slot>

                <p class="text-sm text-danger-600 dark:text-danger-400">
                    {{ $lastError }}
                </p>
            </x-filament::section>
        @endif

        @if ($lastResult)
            <x-filament::section>
                <x-slot name="heading">
                    Ostatni import
                </x-slot>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Tabele</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ number_format($lastResult['imported_tables'], 0, ',', ' ') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Rekordy</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ number_format($lastResult['imported_rows'], 0, ',', ' ') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Baza docelowa</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ $lastResult['after']['target_schema'] }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif

        @if ($preview)
            @php($totals = $this->previewTotals())

            <x-filament::section>
                <x-slot name="heading">
                    Podgląd importu
                </x-slot>

                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Tabele</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $totals['tables'] }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Rekordy źródłowe</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $totals['source_rows'] }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Rekordy docelowe</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $totals['target_rows'] }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Błędy</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $totals['errors'] }}</div>
                    </div>
                </div>

                @if (($preview['errors'] ?? []) !== [])
                    <div class="mt-4 rounded-lg border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
                        <ul class="space-y-1">
                            @foreach ($preview['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                <th class="px-3 py-2 font-medium">Źródło</th>
                                <th class="px-3 py-2 font-medium">Cel</th>
                                <th class="px-3 py-2 text-right font-medium">Źródło</th>
                                <th class="px-3 py-2 text-right font-medium">Cel</th>
                                <th class="px-3 py-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['mappings'] as $mapping)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-3 py-2 font-mono text-xs">{{ $mapping['source'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $mapping['target'] }}</td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $mapping['source_rows'] === null ? 'brak' : number_format($mapping['source_rows'], 0, ',', ' ') }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $mapping['target_rows'] === null ? 'brak' : number_format($mapping['target_rows'], 0, ',', ' ') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span @class([
                                            'font-medium',
                                            'text-success-600 dark:text-success-400' => $mapping['status'] === 'OK',
                                            'text-warning-600 dark:text-warning-400' => $mapping['status'] !== 'OK',
                                        ])>
                                            {{ $mapping['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">
                    Podgląd importu
                </x-slot>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Uruchom sprawdzenie importu, żeby zobaczyć zgodność tabel i liczby rekordów.
                </p>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
