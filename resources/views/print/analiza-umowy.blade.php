<div>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'RobotoCondensed';
            font-size: 13px;
            margin: 0.5cm 1cm 0.5cm 1.5cm;
        }
        h1, h2, h3 {
            page-break-after:avoid;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 36px;
            color: #37404f;
        }
        h2 {
            border-bottom: 1px solid #ccc;
            color: #d60b52;
        }
        h2 {
            margin: 50px 0 15px 0;
        }
        h3 {
            margin: 30px 0 15px 0;
        }
        dt {
            font-weight: bold;
            width: 30%;
            float: left;
            display: block;
        }
        dd {
            margin-left: 30%;
        }
        p {
            text-align: justify;
        }

    </style>
    <x-logo-print />

    <h1>Analiza umowy kredytowej</h1>

    <h2>Umowa</h2>

    <dl>
        <dt>Kredytobiorc{{ $kredytobiorcy->count() > 1 ? 'y' : 'a' }}:</dt>
        <dd>
            @foreach($kredytobiorcy as $osoba)
                @if($osoba)
                    {{ $osoba->label }}@if(!$loop->last),@endif
                @endif
            @endforeach
        </dd>
    </dl>
    <dl>
        <dt>Bank:</dt>
        <dd>{{ $bankUmowa }} @if($bankUmowa != $bankObecnie)<br />(obecnie: {{ $bankObecnie }})@endif</dd>
    </dl>
    <dl>
        <dt>Nr umowy:</dt>
        <dd>@if($nrUmowy != '')
                {{ $nrUmowy }}
            @else
                -
            @endif
        </dd>
    </dl>
    <dl>
        <dt>Data na umowie:</dt>
        <dd>{{ $record->date }}</dd>
    </dl>
    @if($typKredytu)
        <dl>
            <dt>Typ kredytu:</dt>
            <dd>
                {{ $typKredytu }}
            </dd>
        </dl>
    @endif

    @if($kwotaKredytu)
        <dl>
            <dt>Kwota kredytu:</dt>
            <dd>{{ $kwotaKredytu }}</dd>
        </dl>
    @endif

    @if($cel)
        <dl>
            <dt>Cel kredytu:</dt>
            <dd>
                {{ $cel }}@if($cel_um) ({{ $cel_um}})@endif

            </dd>
        </dl>
    @endif

    @if($liczbaRat)
        <dl>
            <dt>Liczba rat:</dt>
            <dd>{{ $liczbaRat }}@if($liczbaRat_um) ({{ $liczbaRat_um}})@endif</dd>
        </dl>
    @endif

    @if($rodzajRat)
        <dl>
            <dt>Rodzaj rat:</dt>
            <dd>{{ $rodzajRat }}@if($rodzajRat_um) ({{ $rodzajRat_um}})@endif</dd>
        </dl>
    @endif

    @if($oprocentowanie)
        <dl>
            <dt>Oprocentowanie:</dt>
            <dd>{{ bp_non_breaking_spaces($oprocentowanie) }}@if($oprocentowanie_um) ({{ bp_non_breaking_spaces($oprocentowanie_um)}})@endif</dd>
        </dl>
    @endif



    <h2>Postanowienia niedozwolone</h2>

        @if($klauzuleNiedozwolone)
            <p>{{ $klauzuleNiedozwolone }}</p>
        @endif

    @if($klauzulePouczenia)
    <h2>Pouczenia o ryzyku kursowym w umowie</h2>
        <p>{{ $klauzulePouczenia }}</p>
    @endif

    @if($inneKlauzule)
    <h2>Inne istotne postanowienia</h2>
        <p>{{ $inneKlauzule }}</p>
    @endif

    <h2>Ocena prawna</h2>

    @if($analiza)
        {!! bp_non_breaking_spaces($analiza) !!}
    @endif

    @if($uwagi)
        <h2>Uwagi</h2>
        {!! bp_non_breaking_spaces($uwagi) !!}
    @endif



</div>
