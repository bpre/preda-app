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
            font-size: 15px;
            font-weight: bold;
            margin: 0.5cm 1cm 0.5cm 1.5cm;
        }
        .page_break {
            page-break-before: always;
        }
        .page_non_break {
            page-break-before: avoid;
        }

    </style>

    <div id="main">

    @php
        $i = 0;
    @endphp

    @foreach($records as $record)

        @if($record['print_envelope'] === TRUE)

            @php
                $i++
            @endphp


            <div class="page{{ $i === 1 ? '_non' : '' }}_break" style="margin-left: 10cm">

                <div style="height: 6cm">

                    @if($record['neostamp']['assigned'] === TRUE)

                        @if($record['neostamp']['file_exists'] === TRUE)

                            <img
                                src="{{ $record['neostamp']['path'] }}"
                                style="max-width: 7cm; margin-left: 2cm"
                            />

                        @endif

                    @endif

                </div>

                <br>

                {!! bp_non_breaking_spaces($record['label']) !!}

                <br>

                {!! bp_non_breaking_spaces($record['adres']) !!}

            </div>

        @endif

    @endforeach

    </div>

    @endif
</div>
