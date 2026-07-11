<h2>{{ $title }}</h2>

@if(empty($rows))
    <p>Brak danych do raportu.</p>
@else
    <table>
        <thead>
            <tr>
                <th>{{ $firstColumn }}</th>
                <th>Przyjęte</th>
                <th>Zakończone</th>
                @if($includeActiveAtPeriodEnd)
                    <th>Aktywne na koniec roku</th>
                @endif
                <th>Wydatki</th>
                <th>Przychody</th>
                <th>Bilans</th>
                <th>Przyszłe</th>
                <th>Potencjalne</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $label => $row)
                @php($balance = $row['paid'] - $row['expense'])
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $row['matters'] }}</td>
                    <td>{{ $row['ended'] }}</td>
                    @if($includeActiveAtPeriodEnd)
                        <td>{{ $row['active_at_period_end'] }}</td>
                    @endif
                    <td>{{ bp_currency($row['expense']) }}</td>
                    <td>{{ bp_currency($row['paid']) }}</td>
                    <td>{{ bp_currency($balance) }}</td>
                    <td>{{ bp_currency($row['future']) }}</td>
                    <td>{{ bp_currency($row['potential']) }}</td>
                </tr>
            @endforeach

            @if($totals)
                @php($totalBalance = $totals['paid'] - $totals['expense'])
                <tr class="total">
                    <td>Razem</td>
                    <td>{{ $totals['matters'] }}</td>
                    <td>{{ $totals['ended'] }}</td>
                    @if($includeActiveAtPeriodEnd)
                        <td>-</td>
                    @endif
                    <td>{{ bp_currency($totals['expense']) }}</td>
                    <td>{{ bp_currency($totals['paid']) }}</td>
                    <td>{{ bp_currency($totalBalance) }}</td>
                    <td>{{ bp_currency($totals['future']) }}</td>
                    <td>{{ bp_currency($totals['potential']) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif
