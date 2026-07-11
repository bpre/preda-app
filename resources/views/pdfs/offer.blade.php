<!doctype html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<html lang="pl">
<head>
    <meta charset="utf-8">

    <style>
        @page { margin: 50px 80px 70px 80px; }

        *, HTML { font-family: 'RobotoCondensed', DejaVu Sans, sans-serif; font-size: 13px; color: #111; }
        .muted { color: #666; }
        .small { font-size: 10px; }

        .header { margin-bottom: 18px; }
        .logo { height: 38px; float: right; }
        .title { font-size: 24px; font-weight: 700; margin: 0; color: #324158; }
        .subtitle { font-size: 18px; font-weight: 700; margin: 0 0 30px 0; color: #324158; }

        .grid { width: 100%; }
        .col { width: 48.5%; vertical-align: top; }
        .gap { width: 3%; }

        .card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
        }

        .badge {
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 10px;
            margin-bottom: 8px;
        }

        table.pricing {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0 10px 0;
        }
        table.pricing td {
            padding: 4px 6px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        table.pricing td.label { color: #444; font-size: 13px; }
        table.pricing td.value { white-space: nowrap; font-size: 13px; }

        /* Stopka i CTA */
        .footer {
            position: fixed;
            left: 28px;
            right: 28px;
            bottom: 22px;
            height: 44px;
        }
        .cta {
            display: block;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            padding: 12px 14px;
            font-weight: 800;
            font-size: 12px;

            /* DomPDF bywa kapryśny z gradientami/cieniami, więc prosto: */
            border: 1px solid #111;
            color: #111;
        }

        h2 {
            color: #C70036;
            margin-top: 50px;
        }

        h3 {
            font-weight: weight;
            color: #C70036;
            font-size: 14px;
            margin: 10px 0;
        }

        .normal {
            font-weight: normal;
        }

        hr {
            border: 0;
            border-top: 1px solid #aaa;
            margin: 30px 0;
            height: 0;
        }

        table.credit {
            border-top: 1px solid #aaa; border-bottom: 1px solid #aaa; width: 100%; margin-top: 20px;
        }
        table.credit td {
            padding: 1px 6px 1px 6px;
            border-bottom: 1px solid #eee;
        }

        table.credit tr:last-child td {
            border-bottom: none;
        }

        table.credit td.value {
            font-weight: bold;
        }

        p {
            margin: 0;
            line-height: 100%;
            margin-bottom: 8px;
        }

    </style>
</head>

@php

    switch($offer->sex) {

        case 'male':

            $persona_a = 'Pana';
            $persona_u = 'Panu';
            $persona_z = 'Pana';
            $zaplaca = 'zapłaci Pan';
            $ma_persona = 'ma Pan';
            $chcialby_persona = 'chciałby Pan';

            break;

        case 'female':

            $persona_a = 'Pani';
            $persona_u = 'Pani';
            $persona_z = 'Panią';
            $zaplaca = 'zapłaci Pani';
            $ma_persona = 'ma Pani';
            $chcialby_persona = 'chciałaby Pani';
            $brak_umowy_persona = 'Jeżeli nie mamy jeszcze Pani umowy, uprzejmie prosimy o przesłanie jej nam do bezpłatnej analizy';

            break;

        default:

            $persona_a = 'Państwa';
            $persona_u = 'Państwu';
            $persona_z = 'Państwa';
            $zaplaca = 'zapłacą Państwo';
            $ma_persona = 'mają Państwo';
            $chcialby_persona = 'chcieliby Państwo';
            $brak_umowy_persona = 'Jeżeli nie mamy jeszcze Państwa umowy, uprzejmie prosimy o przesłanie jej nam do bezpłatnej analizy';
    }

@endphp

<body>
    <div class="header">

        <img class="logo" src="{{ public_path('images/logo.navy.png') }}" alt="Logo">
        <div class="title">Oferta</div>
        <div class="params">

            <table class="credit">

                <tr>
                    <td style="width: 35%">Oferta przygotowana dla:</td>
                    <td class="value">{{ $offer->name }}</td>
                </tr>

                <tr>
                    <td style="width: 35%">Data przygotowania oferty:</td>
                    <td class="value">{{ (new DateTime($offer->offer_confirmed_at))->format('d.m.Y') }}</td>
                </tr>

                <tr>
                    <td>Bank na umowie:</td>
                    <td class="value">
                        @if(!empty($offer->bank))
                            {{ $offer->bank }}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td>Rok zawarcia umowy:</td>
                    <td class="value">
                        @if(!empty($offer->year))
                            {{ $offer->year }}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td>Kwota kredytu:</td>
                    <td class="value">
                        @if(!empty($offer->amount))
                            {{ number_format((float) $offer->amount, 0, ',', ' ') }} zł
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="font-weight: bold; vertical-align: top;">Orientacyjna korzyść:</td>
                    <td>
                        standardowo korzyść stanowi równowartość 80-120% kwoty kredytu<br>
                        w {{ $persona_a }} przypadku szacunkowo: <strong>{{ number_format((float) $offer->amount*0.8, 0, ',', ' ') }} - {{ number_format((float) $offer->amount*1.2, 0, ',', ' ') }} zł</strong>

                        <div style="margin-top: 8px">
                            (zależne od czasu kredytowania, kursu wypłaty, historii spłat, terminu spłaty - szczegółowo wyliczymy dysponując zaświadczeniem z banku)
                        </div>
                    </td>
                </tr>

                @if(false)
                <tr>
                    <td style="padding: 0 6px">
                        @if(!empty($offer->bank))
                        Bank: {{ $offer->bank }}
                        @endif
                    </td>
                    <td style="width: 32%; padding: 0 6px">
                        @if(!empty($offer->year))
                        Rok zawarcia umowy: {{ $offer->year }}
                        @endif
                    </td>
                    <td style="width: 33%; padding: 0 6px">
                        @if(!empty($offer->amount))
                        Kwota kredytu: {{ number_format((float) $offer->amount, 0, ',', ' ') }} zł
                        @endif
                    </td>
                </tr>
                @endif

            </table>

        </div>
    </div>


        <p>
            Oferujemy {{ $persona_u }} dwa warianty współpracy z kancelarią:
        </p>

        <ul>
            <li style="margin-bottom: 8px">
                wariant <strong>„Bezpieczny start”</strong> - dla osób, które nie chcą angażować dużych środków na początku procesu (istotnym elementem wynagrodzenia kancelarii jest premia za wygranie sprawy);
            </li>
            <li>
                wariant <strong>„Bez premii od korzyści”</strong> - dla osób, które akceptują wyższe opłaty, bo chcą maksymalizować korzyści z wygrania sprawy (brak premii od korzyści za wygranie sprawy).
            </li>
        </ul>

        <p>
            Poniżej przedstawiamy wszystkie parametry przygotowanej dla {{ $persona_a }} oferty. Wszystkie kwoty są kwotami brutto (zawierają podatek VAT).
        </p>

    <table class="pricing">

        <tr style="border-bottom: 2px solid #C70036;">
            <td style="color: #C70036">Wariant</td>
            <td style="width: 32%; color: #C70036; font-weight: bold; font-size: 16px">„Bezpieczny start”</td>
            <td style="width: 30%; color: #C70036; font-weight: bold; font-size: 16px">„Bez premii od korzyści”</td>
        </tr>

        <tr>
            <td class="label">Opłata wstępna</td>
            <td class="value">
                <strong>{{ number_format((float) $offer->start_wstepna, 0, ',', ' ') }} zł</strong>
            </td>
            <td class="value">
                <strong>{{ number_format((float) $offer->max_wstepna, 0, ',', ' ') }} zł</strong> (możliwe raty)
            </td>
        </tr>
        <tr>
            <td class="label">Obsługa apelacji</td>
            <td class="value">
                <strong>0 zł</strong>
            </td>
            <td class="value">
                <strong>{{ number_format((float) $offer->max_druga_instancja, 0, ',', ' ') }} zł</strong> (jeśli będzie apelacja)
            </td>
        </tr>
        <tr>
            <td class="label">Opłata za rozprawę</td>
            <td class="value">
                <strong>0 zł</strong>
            </td>
            <td class="value">
                <strong>{{ number_format((float) $offer->max_rozprawa, 0, ',', ' ') }} zł</strong>
                (max. {{ number_format((float) $offer->max_rozprawy_limit, 0, ',', ' ') }} zł)
            </td>
        </tr>
        <tr>
            <td class="label">Premia</td>
            <td class="value">
                <strong>{{ number_format((float) $offer->start_premia, 0, ',', ' ') }} zł</strong> (płatna tylko po wygranej)
            </td>
            <td class="value">
                <strong>0 zł</strong>
            </td>
        </tr>
        <tr>
            <td class="label">Górna granica premii</td>
            <td class="value"><strong>{{ (int) $offer->start_procent_limit }}%</strong> uzyskanych korzyści</td>
            <td class="value">nie dotyczy</td>
        </tr>

        <tr>
            <td style="label">Maksymalne zaangażowanie finansowe przed prawomocnym wyrokiem</td>
            <td class="value">
                <strong>{{ number_format((float) $offer->start_wstepna, 0, ',', ' ') }} zł</strong>
            </td>
            <td class="value">
                <strong>{{ number_format((float) $offer->max_razem_max, 0, ',', ' ') }} zł</strong>
            </td>
        </tr>

    </table>


    <p>
        <strong>
            W obu wariantach koszty zastępstwa procesowego zasądzone od banku lub ustalone w ugodzie stanowią dodatkowe wynagrodzenie kancelarii (płaci je bank).
        </strong>
    </p>

    <h3 style="margin-top: 40px">
        Kiedy przysługuje premia za wygranie sprawy?
    </h3>

        <p>
            Premia za wygranie sprawy przysługuje w razie prawomocnego ustalenia przez sąd nieważności (w sentencji lub uzasadnieniu wyroku), a także w przypadku zawarcia ugody (decyzja o zawarciu ugody zawsze należy do {{ $persona_a }}).
        </p>
        <p>
            W przypadku przegrania sprawy premia nie przysługuje.
        </p>

    <h3>
        Jak należy rozumieć „korzyści”?
    </h3>

        <p>
            Przez „korzyści” należy rozumieć kwotę, o którą zmieni się na {{ $persona_a }} korzyść bilans {{ $persona_a }} zobowiązań względem banku. W praktyce jest to suma kwoty, o którą obniży się saldo zadłużenia oraz kwoty, którą bank zobowiązany będzie zapłacić na {{ $persona_a }} rzecz.
        </p>
        <p>
            <strong>Przykład:</strong> Saldo zadłużenia kredytobiorcy wykonującego umowę kredytową wynosi 100 000 zł. Na skutek unieważnienia umowy kredytobiorca nie tylko nie musi już nic płacić bankowi (obniżenie salda o 100 000 zł), ale dodatkowo jeszcze bank musi zapłacić kredytobiorcy 50 000 zł. Korzyść kredytobiorcy wynosi w takim wypadku 150 000 zł (100 000 zł obniżenie salda + 50 000 zł do zapłaty przez bank).
        </p>

    <h3>
        Co oznacza „Górna granica premii” w wariancie „Bezpieczny start”?
    </h3>

        <p>
            Dla pełnego bezpieczeństwa Klienta wprowadziliśmy mechanizm bezpiecznika. Premia przysługująca kancelarii nigdy nie może przekroczyć 35% korzyści Klienta.
        </p>
        <p>
            <strong>Przykład 1:</strong> Korzyść z wygrania sprawy wyniosła 50 000 zł. Premia kancelarii wyniesie w takim wypadku 17 500 zł, a nie {{ number_format((float) $offer->start_premia, 0, ',', ' ') }} zł, bo 35% z 50 000 zł &#61; 17 500 zł (bezpiecznik w postaci limitu 35% korzyści działa). Korzyść Klienta wyniesie natomiast 32 500 zł (czyli 65%).
        </p>
        <p>
            <strong>Przykład 2:</strong> Korzyść z wygrania sprawy wyniosła 100 000 zł. Premia kancelarii wyniesie w takim wypadku {{ number_format((float) $offer->start_premia, 0, ',', ' ') }} zł, bo 35% ze 100 000 zł = 35 000 zł (bezpiecznik w postaci limitu 35% nie znajdzie tu zastosowania - niższa jest kwota premii ustalona kwotowo). Korzyść Klienta wyniesie w tym wypadku {{ number_format(100000 - (float) $offer->start_premia, 0, ',', ' ') }} zł (czyli {{ (100000 - (float) $offer->start_premia)/1000 }}% całej korzyści).
        </p>

    <h3>
        Co oznacza „Limit opłat za rozprawy” w wariancie „Bez premii od korzyści”?
    </h3>

        <p>
            Wskazany limit oznacza, że łącznie za rozprawy nigdy nie {{ $zaplaca }} więcej niż 1 999 zł.
        </p>
        <p>
            <strong>Przykład:</strong> Sprawa okazała się bardzo skomplikowana i było łącznie 5 rozpraw. Licząc po 500 zł za rozprawę, wychodzi suma: 2 500 zł. Dzięki „Limitowi opłat za rozprawy” Klient zapłaci za rozprawy maksymalnie 1 999 zł.
        </p>

    <h3>
        Jakie są terminy płatności?
    </h3>

        <p>
            Opłata wstępna płatna jest w terminie 7 dni od zawarcia umowy z kancelarią.
        </p>
        <p>
            W wariancie „Bez premii od korzyści” opłata wstępna może zostać uregulowana w 4 miesięcznych ratach, po 3 000 zł każda rata. Pierwsza rata -  w terminie 7 dni od zawarcia umowy z kancelarią, kolejne - w odstępach miesięcznych.
        </p>
        <p>
            Obsługa apelacji w wariancie „Bez premii od korzyści” - po doręczeniu apelacji banku lub wniesieniu apelacji w imieniu kredytobiorcy.
        </p>
        <p>
            Płatność za rozprawę w wariancie „Bez premii od korzyści” - w dniu rozprawy.
        </p>
        <p>
            Premia płatna w terminie 30 dni od uprawomocnienia wyroku / podpisania ugody.
        </p>

    <h3>
        Dlaczego koszty zastępstwa procesowego stanowią dodatkowe wynagrodzenie kancelarii?
    </h3>

        <p>
            Pozwala nam to istotnie obniżyć wysokość opłaty wstępnej w każdym z wariantów. Innymi słowy - gdyby koszty zastępstwa procesowe przypadały Klientowi, opłaty na wstępie musiałyby być dużo wyższe.
        </p>
        <p>
            Koszty zastępstwa procesowego płaci bank (nie zwiększa to kwot płaconych przez {{ $persona_z }} z własnej kieszeni).
        </p>

    <h3>
        Jakie inne koszty trzeba jeszcze ponieść?
    </h3>

        <p style="margin-bottom: 0">
            Aby pozwać bank, oprócz wynagrodzenia kancelarii, konieczne jest jeszcze poniesienie przez {{ $persona_z }} następujących kosztów:
        </p>
        <ul style="margin-top: 0">
            <li style="margin-top: 0">
                opłaty od pozwu: 1.000 zł (płatna na rzecz sądu);
            </li>
            <li>
                opłaty skarbowej od pełnomocnictwa: 17 zł od każdej osoby.
            </li>
        </ul>
        <p>
            W przypadku wygrania sprawy opłata od pozwu i opłata skarbowa od pełnomocnictwa zostaną {{ $persona_u }} zwrócone.
        </p>

    <h3>
        Dalsze kroki:
    </h3>

        <p>
            Jeśli {{ $ma_persona }} pytania do naszej oferty lub {{ $chcialby_persona }} nawiązać współpracę z naszą kancelarią - uprzejmie prosimy o kontakt telefoniczny lub mailowy bezpośrednio z adwokatem Bartoszem Prędą:
        </p>
        <ul>
            <li style="margin-bottom: 8px; float: left">
                telefon: <a href="+48666580580">511 003 001</a>
            </li>
            <li style="margin-left: 250px">
                e-mail: <a href="mailto:bartosz.preda@preda.info">bartosz.preda@preda.info</a>
            </li>
        </ul>
        <p>
            Jeżeli nie mamy jeszcze {{ $persona_a }} umowy, uprzejmie prosimy o przesłanie jej nam do bezpłatnej analizy, przez formularz na naszej stronie internetowej: <a href="https://preda.info/analiza">https://preda.info/analiza</a>.
        </p>
        <p>
            Przygotowana dla {{ $persona_a }} oferta ważna jest przez 10 dni, tj. do dnia: {{ (new DateTime($offer->offer_confirmed_at))->modify('+10 days')->format('d.m.Y') }}.
        </p>













    </h3>

</p>

</p>




</body>
</html>
