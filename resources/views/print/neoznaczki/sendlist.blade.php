@php
use App\Models\ContactLetter;
use  App\Filament\Resources\NeostampResource;
@endphp
<div>
    @if($records)
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'PTSerif';
            font-size: 12px;
            margin-top: 1cm;
            /* margin: 0.5cm; */
        }
        .page_break {
            page-break-before: always;
        }
        .page_non_break {
            page-break-before: avoid;
        }
        p {
            line-height: 100%;
        }
        table {
            font-size: 8px;
            line-height: 100%;
            width: 100%;
            border-left: 1px solid #000;
        }

        th {
            text-align: center;
        }

        th, td {
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: normal;
        }

        thead {
            border-top: 1px solid #000;
        }
        tbody td {
            font-size: 12px;
            padding: 5px 3px;
        }

    </style>

    <div id="main">

    <p>
        <strong>Przesyłka listowa polecona</strong>
    </p>

    <p>
        <strong>Nadawca:</strong><br>
        PRĘDA Kancelaria Adwokacka<br>
        ul. Szewska 7, 67-200 Głogów
    </p>

    <table cellspacing="0">
        <thead>
            <tr>
                <th style="width: 4%">Lp.</th>
                <th style="width: 22%">Adresat<br>(imię nazwisko lub nazwa)</th>
                <th style="width: 17%">Dokładne miejsce doręczenia</th>
                <th style="width: 8%">Kwota zadekl. wartości</th>
                <th style="width: 8%">Masa</th>
                <th style="width: 12%">Nr nadawczy</th>
                <th style="width: 8%">Uwagi</th>
                <th style="width: 12%">Opłata</th>
                <th style="width: 9%;">Kwota pobrania</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
            </tr>
        </thead>

        <tbody>
    @php
        $i = 1;
    @endphp

    @foreach($records as $record)

        <tr>

            <td style="text-align: center; padding: 30px 0">{{ $i }}.</td>
            <td>

                {!! bp_non_breaking_spaces($record['label']) !!}

            </td>
            <td>

                {!! bp_non_breaking_spaces($record['adres']) !!}

            </td>
            <td></td>
            <td></td>
            <td>

                @if(isset($record['neostamp']['label']))
                    {{ $record['neostamp']['label'] }}
                @endif

            </td>
            <td></td>
            <td></td>
            <td></td>

        </tr>

    @endforeach

        </tbody>
    </table>

    <p style="font-size: 6px; text-align: right">{{  date("Y-m-d H:i:s") }}</p>

    </div>

    @endif
</div>
