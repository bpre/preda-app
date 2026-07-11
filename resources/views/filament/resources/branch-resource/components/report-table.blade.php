<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $reportCategory = request('report_category', 'CHF');
        $reportFrom = request('report_from');
        $reportTo = request('report_to');
        $report = \App\Support\Branches\BranchReport::make($getRecord(), $reportCategory, $reportFrom, $reportTo)->toArray();
        $months = $report['months'];
        $years = $report['years'];
        $totals = $report['totals'];
        $filters = $report['filters'];
        $mattersWithoutStart = $report['matters_without_start'];
        $exportParams = array_filter([
            'report_category' => $filters['category'],
            'report_from' => $filters['from'],
            'report_to' => $filters['to'],
        ], filled(...));
    @endphp

    <div class="space-y-6">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-3 rounded-md border border-gray-200 bg-white p-4 text-sm">
            <label class="min-w-40">
                <span class="mb-1 block text-xs font-medium text-gray-500">Zakres spraw</span>
                <select name="report_category" class="w-full rounded-md border-gray-300 text-sm">
                    @foreach(\App\Support\Branches\BranchReport::categoryOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span class="mb-1 block text-xs font-medium text-gray-500">Od</span>
                <input type="date" name="report_from" value="{{ $filters['from'] }}" class="rounded-md border-gray-300 text-sm">
            </label>

            <label>
                <span class="mb-1 block text-xs font-medium text-gray-500">Do</span>
                <input type="date" name="report_to" value="{{ $filters['to'] }}" class="rounded-md border-gray-300 text-sm">
            </label>

            <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                Zastosuj
            </button>

            <a href="{{ url()->current() }}" class="rounded-md px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">
                Wyczyść
            </a>

            <div class="ml-auto flex gap-2">
                <a href="{{ route('branches.report.export', ['branch' => $getRecord(), 'format' => 'xlsx', ...$exportParams]) }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    XLSX
                </a>
                <a href="{{ route('branches.report.export', ['branch' => $getRecord(), 'format' => 'pdf', ...$exportParams]) }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    PDF
                </a>
            </div>
        </form>

        @if($mattersWithoutStart->isNotEmpty())
            <div class="rounded-md border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700">
                <div class="font-bold">Sprawy bez daty rozpoczęcia</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($mattersWithoutStart as $matter)
                        <li>
                            <a href="{{ \App\Filament\Resources\MatterResource::getEditUrlForMatter($matter) }}" target="_blank" class="hover:underline">
                                {{ $matter->label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <h3 class="mb-3 text-base font-semibold">Raport miesięczny</h3>

            @if(empty($months))
                <div class="text-sm text-gray-500">Brak danych do raportu.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-gray-500">
                                <th class="py-2 text-left">Miesiąc</th>
                                <th class="py-2">Przyjęte</th>
                                <th class="py-2">Zakończone</th>
                                <th class="py-2">Wydatki</th>
                                <th class="py-2">Przychody</th>
                                <th class="py-2">Bilans</th>
                                <th class="py-2">Przyszłe</th>
                                <th class="py-2">Potencjalne</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($months as $month => $row)
                                @php($balance = $row['paid'] - $row['expense'])
                                <tr class="text-right">
                                    <td class="py-2 text-left font-medium">{{ $month }}</td>
                                    <td class="py-2">{{ $row['matters'] }}</td>
                                    <td class="py-2">{{ $row['ended'] }}</td>
                                    <td class="py-2">{{ bp_currency($row['expense']) }}</td>
                                    <td class="py-2">{{ bp_currency($row['paid']) }}</td>
                                    <td class="py-2 font-medium {{ $balance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ bp_currency($balance) }}
                                    </td>
                                    <td class="py-2">{{ bp_currency($row['future']) }}</td>
                                    <td class="py-2">{{ bp_currency($row['potential']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if(! empty($years))
            <div>
                <h3 class="mb-3 text-base font-semibold">Podsumowanie roczne</h3>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-gray-500">
                                <th class="py-2 text-left">Rok</th>
                                <th class="py-2">Przyjęte</th>
                                <th class="py-2">Zakończone</th>
                                <th class="py-2">Aktywne na koniec roku</th>
                                <th class="py-2">Wydatki</th>
                                <th class="py-2">Przychody</th>
                                <th class="py-2">Bilans</th>
                                <th class="py-2">Przyszłe</th>
                                <th class="py-2">Potencjalne</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($years as $year => $row)
                                @php($balance = $row['paid'] - $row['expense'])
                                <tr class="text-right font-medium">
                                    <td class="py-2 text-left">{{ $year }}</td>
                                    <td class="py-2">{{ $row['matters'] }}</td>
                                    <td class="py-2">{{ $row['ended'] }}</td>
                                    <td class="py-2">{{ $row['active_at_period_end'] }}</td>
                                    <td class="py-2">{{ bp_currency($row['expense']) }}</td>
                                    <td class="py-2">{{ bp_currency($row['paid']) }}</td>
                                    <td class="py-2 {{ $balance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ bp_currency($balance) }}
                                    </td>
                                    <td class="py-2">{{ bp_currency($row['future']) }}</td>
                                    <td class="py-2">{{ bp_currency($row['potential']) }}</td>
                                </tr>
                            @endforeach

                            @php($totalBalance = $totals['paid'] - $totals['expense'])
                            <tr class="border-t text-right font-bold">
                                <td class="py-3 text-left">Razem</td>
                                <td class="py-3">{{ $totals['matters'] }}</td>
                                <td class="py-3">{{ $totals['ended'] }}</td>
                                <td class="py-3 text-gray-400">-</td>
                                <td class="py-3">{{ bp_currency($totals['expense']) }}</td>
                                <td class="py-3">{{ bp_currency($totals['paid']) }}</td>
                                <td class="py-3 {{ $totalBalance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    {{ bp_currency($totalBalance) }}
                                </td>
                                <td class="py-3">{{ bp_currency($totals['future']) }}</td>
                                <td class="py-3">{{ bp_currency($totals['potential']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
