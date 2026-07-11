<div>

@include('print.zlecenia.elements.style')

<!-- KOMPARYCJA -->
@include('print.zlecenia.elements.komparycja')


@php

if($pozyczka) {

    $kredytowej = 'pożyczki';
    $kredytu = 'pożyczki';

} else {

    $kredytowej = 'kredytowej';
    $kredytu = 'kredytu';

}

@endphp

<!-- TREŚĆ UMOWY -->
    <div style="page-break-after: always;">
        <div style="border-top:1px solid #000" class="mt-5">

            <!-- 1 -->
            <dl>
                <dt class="bold">1.</dt><dd class="heading">Przedmiot umowy.</dd>
                <dt>1.1.</dt>
                <dd>
                    Klient zleca Kancelarii świadczenie usług prawnych w zakresie dochodzenia od banku roszczeń wynikających z opisanej wyżej umowy {{ $kredytowej }} (ustalenia nieważności umowy, ustalenia bezskuteczności poszczególnych postanowień umowy{{ $is_getin ? '' : ', zasądzenia zwrotu świadczeń zapłaconych nienależnie bankowi' }}).
                </dd>
                <dt>1.2.</dt>
                <dd>
                    Zakres usług obejmuje:
                    <br class="mt-5">
                    <strong>Etap I</strong>: przygotowanie i wysłanie do banku przedsądowego wezwania, przygotowanie i złożenie pozwu, przygotowanie i złożenie repliki na odpowiedź na pozew, reprezentowanie Klienta przed sądem I instancji;
                    <br class="mt-5">
                    <strong>Etap II</strong>: przygotowanie apelacji (jeśli będzie konieczne), przygotowanie odpowiedzi na apelację banku; reprezentowanie Klienta przed sądem II instancji;
                    <br class="mt-5">
                    <strong>Etap III</strong>: {{ $is_getin ? '' : 'rozliczenie nieważnej umowy oraz ' }}wykreślenie hipoteki (jeśli nie została wcześniej wykreślona).
                </dd>
                <dt>1.3.</dt>
                <dd>
                    Zakres usług nie obejmuje doradztwa podatkowego.
                </dd>
            </dl>

            <!-- 2 -->
            <dl>
                <dt class="bold">2.</dt><dd class="heading">Obowiązki klienta.</dd>
                <dt>2.1.</dt>
                <dd>
                    Klient zobowiązany jest do udzielania Kancelarii wszelkich informacji i dostarczania wszelkich wskazanych przez Kancelarię dokumentów niezbędnych do dochodzenia roszczeń, w szczególności do przedłożenia: umowy {{ $kredytowej }} wraz z aneksami i załącznikami, wskazanych przez Kancelarię zaświadczeń oraz korespondencji prowadzonej z bankiem.
                </dd>
                <dt>2.2.</dt>
                <dd>
                    Klient zobowiązany jest do niezwłocznego informowania Kancelarii o wszelkich zdarzeniach mogących mieć wpływ na realizację niniejszej umowy (w szczególności o zamiarze całkowitej spłaty {{ $kredytu }}), a także do niepodejmowania bez porozumienia z Kancelarią działań mogących mieć wpływ na zasadność i wysokość roszczeń dochodzonych na podstawie niniejszej umowy. W szczególności Klient zobowiązuje się, że bez porozumienia z Kancelarią nie będzie podpisywał aneksów do umowy {{ $kredytowej }}, porozumień, ugód, itp.
                </dd>
            </dl>

            <!-- 3 -->
            <dl>

                <dt class="bold">3.</dt>
                <dd class="heading">Terminy.</dd>

                <dt></dt>
                <dd>
                    Kancelaria zobowiązana jest wysłać przedsądowe wezwanie do banku nie później niż w terminie 2 tygodni od dostarczenia przez Klienta wszystkich dokumentów niezbędnych do dochodzenia roszczeń oraz przygotować pozew nie później niż w terminie 3 miesięcy od dostarczenia dokumentów.
                </dd>
            </dl>

            <!-- 4 -->
            <dl>

                <dt class="bold">4.</dt>
                <dd class="heading">Opłaty.</dd>

                <dt>4.1.</dt>
                <dd>
                    Wszelkie opłaty niezbędne do celowego dochodzenia roszczeń obciążają Klienta, w szczególności: opłaty od pozwu, od apelacji, opłaty skarbowe od pełnomocnictw, opłaty z tytułu wynagrodzenia biegłych, a także opłaty z tytułu zwrotu kosztów procesu stronie przeciwnej w przypadku przegrania sprawy lub częściowego przegrania sprawy.
                </dd>
                <dt>4.2.</dt>
                <dd>
                    Klient zobowiązuje się dokonywać opłat w terminach: wskazanych przez sąd, wynikających z obowiązujących przepisów lub wskazanych przez Kancelarię.
                </dd>
                <dt>4.3.</dt>
                <dd>
                    Kancelaria nie jest zobowiązana do dokonywania jakichkolwiek opłat w imieniu Klienta i nie ponosi odpowiedzialności za niedokonanie przez Klienta opłaty lub niedokonanie jej w terminie lub właściwej wysokości, jeśli o konieczności dokonania opłaty, jej wysokości i terminie Klient został należycie poinformowany.
                </dd>
            </dl>

            <!-- 5 -->
            <dl>
                <dt class="bold">5.</dt><dd class="heading">Wynagrodzenie podstawowe.</dd>
                <dt>5.1.</dt>
            </dl>
            <dl>
                <dd>
                    Klient zapłaci Kancelarii: za Etap I – <strong>{{ bp_currency($e->stage_one_fee) }}</strong>, za Etap II: <strong>{{ bp_currency($e->stage_two_fee) }}</strong>, za Etap III: <strong>0 zł</strong>@if($e->is_bonus), a ponadto premię w razie wygranej. @else.@endif
                </dd>
            </dl>
            @if($e->is_bonus)
                <dl>
                    <dt>5.2.</dt>
                    <dd>
                        Premia w razie wygranej przysługuje w wysokości:
                        <strong>{{ bp_currency($e->bonus_fee) }}</strong>.
                    </dd>
                </dl>
                <dl>
                    <dt>5.3.</dt>
                    <dd>
                        Premia za wygranie sprawy przysługuje w razie prawomocnego stwierdzenia przez sąd nieważności umowy lub bezskuteczności postanowień kształtujących mechanizm indeksacji / denominacji (w sentencji lub uzasadnieniu wyroku), a także w przypadku zawarcia ugody.
                    </dd>
                </dl>
            @endif
            <dl>
                <dt>5.{{ $e->is_bonus ? 4 : 2 }}.</dt>
                <dd>
                    Terminy płatności określa harmonogram zawarty w pkt 13 umowy.
                </dd>
            </dl>
            <dl>
                <dt>5.{{ $e->is_bonus ? 5 : 3 }}.</dt>
                <dd>
                    Wszystkie kwoty wskazane w niniejszej umowie są kwotami brutto (zawierają podatek VAT).
                </dd>
            </dl>

            <!-- 6 -->
            <dl>
                <dt class="bold">6.</dt><dd class="heading">Wynagrodzenie za rozprawy.</dd>
                <dt></dt>
                <dd>
                    Za każdą rozprawę Klient zapłaci kancelarii wynagrodzenie w kwocie  <strong>{{ bp_currency($e->hearing_fee) }}</strong>.
                </dd>
            </dl>

            <!-- 7 -->
            <dl>
                <dt class="bold">7.</dt><dd class="heading">Wynagrodzenie dodatkowe – koszty zastępstwa procesowego.</dd>
                <dt>7.1.</dt>
                <dd>
                    W sprawach prowadzonych na podstawie niniejszej umowy koszty zastępstwa procesowego zasądzone prawomocnie od banku na rzecz Klienta lub wynikające z ugody zawartej przez Klienta stanowią w całości dodatkowe wynagrodzenie Kancelarii – w wysokości wynikającej z wyroków, postanowień lub z treści ugody.
                </dd>

                <dt>7.2.</dt>
                <dd>
                    Zawierając z bankiem ugodę Klient zobowiązuje się zawrzeć w niej postanowienie przyznające Kancelarii koszty zastępstwa procesowego w stawce nie niższej niż stawka minimalna wynikająca z rozporządzenia Ministra Sprawiedliwości w sprawie opłat za czynności adwokackie. W przypadku zawarcia przez Klienta ugody z bankiem niezawierającej takiego postanowienia, Klient zobowiązuje się zapłacić na rzecz kancelarii wynagrodzenie dodatkowe w kwocie odpowiadającej kosztom zastępstwa procesowego w stawce minimalnej wynikającej z rozporządzenia Ministra Sprawiedliwości w sprawie opłat za czynności adwokackie, w terminie 14 dni od zawarcia ugody.
                </dd>

                <dt>7.3.</dt>
                <dd>
                    Klient upoważnia Kancelarię do odbioru zasądzonych kosztów zastępstwa procesowego bezpośrednio od banku. Jeśli koszty zastępstwa procesowego zasądzone od banku zostaną wypłacone Klientowi, Klient zobowiązuje się przekazać je Kancelarii niezwłocznie, nie później jednak niż w terminie 3 dni od ich otrzymania.
                </dd>
                @if(!$is_getin)
                    <dt>7.4.</dt>
                    <dd>
                        Jeżeli po prawomocnym ustaleniu nieważności umowy {{ $pozyczka ? 'pożyczki' : 'kredytowej' }} zajdzie konieczność wszczęcia dodatkowego postępowania sądowego o zapłatę, w celu rozliczenia nieważnej umowy, w szczególności w związku z wyborem określonej taktyki procesowej lub dokonywaniem przez Klienta dalszych spłat rat w toku postępowania o ustalenie nieważności umowy, za prowadzenie takiego dodatkowego postępowania Kancelarii będą przysługiwały wyłącznie koszty zastępstwa procesowego zasądzone przez sąd lub wynikające z zawartej ugody oraz wynagrodzenie za rozprawy.
                    </dd>
                @endif
            </dl>

            <!-- 8 -->
            <dl>
                <dt class="bold">8.</dt><dd class="heading">{{  $is_getin ? 'Postępowanie upadłościowe.' : 'Dodatkowe postępowanie.' }}</dd>
                <dt></dt>
                <dd>
                    @if($is_getin)
                    Zakres objętych niniejszą umową usług prawnych nie obejmuje reprezentowania Klienta w postępowaniu upadłościowym oraz w postępowaniu z powództwa banku.
                    @else
                    Jeżeli po prawomocnym ustaleniu nieważności umowy {{ $kredytowej }} zajdzie konieczność wszczęcia dodatkowego postępowania sądowego o zapłatę, w celu zasądzenia na rzecz Klienta należnych mu kwot, w szczególności w związku z wyborem określonej taktyki procesowej lub dokonywaniem przez Klienta dalszych spłat rat w toku postępowania o ustalenie nieważności umowy, za prowadzenie takiego dodatkowego postępowania Kancelarii będą przysługiwały wyłącznie koszty zastępstwa procesowego zasądzone przez sąd lub wynikające z zawartej ugody oraz wynagrodzenie za rozprawy.
                    @endif
                </dd>
            </dl>

            <!-- 9 -->
            <dl>
                <dt class="bold">9.</dt><dd class="heading">Poufność i prawa autorskie.</dd>
                <dt>9.1.</dt>
                <dd>
                    Kancelaria zobowiązuje się do zachowania w tajemnicy wszelkich informacji uzyskanych przy świadczeniu pomocy prawnej na rzecz Klienta.
                </dd>
                <dt>9.2.</dt>
                <dd>
                    W zakresie niezbędnym do prawidłowego wykonania niniejszej umowy, Klient upoważnia Kancelarię do przekazywania informacji o sprawie osobom trzecim, przy pomocy których Kancelaria świadczy usługi objęte niniejszą umową, w szczególności pełnomocnikom substytucyjnym, aplikantom oraz analitykowi finansowemu.
                </dd>
                <dt>9.3.</dt>
                <dd>
                    Wszelkie informacje, które Klient otrzyma od Kancelarii, są informacjami poufnymi. W szczególności za informacje takie uznawane są: propozycje rozwiązań prawnych, koncepcji, założeń, projekty pism procesowych, pisma procesowe i wszelkie inne dokumenty wytworzone przez Kancelarię przy wykonywaniu umowy. Klient zobowiązuje się zachować w poufności informacje otrzymane od Kancelarii oraz nie wykorzystywać ich, ani nie przekazywać ich osobom trzecim, w celach innych, niż związane z zawarciem i wykonaniem niniejszej umowy. Klient zobowiązuje się do nierozpowszechniania informacji otrzymanych od Kancelarii, w szczególności zawierających rozwiązania prawne, w sieci Internet. Klient nie nabywa praw autorskich do rezultatów pracy Kancelarii mogących stanowić utwory w rozumieniu ustawy o prawie autorskim i prawach pokrewnych.
                </dd>
            </dl>

            <!-- 10 -->
            <dl>
                <dt class="bold">10.</dt><dd class="heading">Oświadczenia.</dd>
                <dt>10.1.</dt>
                <dd>
                    Klient upoważnia Kancelarię do decydowania o sposobie prowadzenia sprawy, w szczególności do decydowania o wyborze optymalnej taktyki procesowej. Dokonując wyboru taktyki procesowej Kancelaria zobowiązana jest uwzględniać sytuację faktyczną i prawną Klienta, a także jego uzasadnione sugestie. Zawarcie ugody zawsze jest decyzją Klienta.
                </dd>
            </dl>
            <dl>
                <dt>10.2.</dt>
                <dd>
                    Klient oświadcza, że wyraża zgodę na reprezentowanie go przed sądami przez: adw. Bartosza Prędę, adw. Wiktorię Rajzynger, adw. Joannę Krajewską oraz apl. adw. Beatę Mital-Goryczkę. Reprezentowanie Klienta przed sądami przez inne osoby wymaga zgody klienta.
                </dd>
            </dl>
            <dl>
                <dt>10.3.</dt>
                <dd>
                    Klient oświadcza, że jest świadomy, iż umowa o świadczenie usług prawnych jest umową starannego działania i Kancelaria, zobowiązując się do zachowania należytej staranności i standardów profesjonalnej obsługi prawnej, nie może zagwarantować uzyskania określonego rezultatu.
                </dd>
            </dl>
            <dl>
                <dt>10.4.</dt>
                <dd>
                    Klient oświadcza, iż został poinformowany przez Kancelarię, że dochodzenie roszczeń objętych niniejszą umową wiąże się z ryzykiem przegrania sprawy. Ryzyko to wynika przede wszystkim z rozbieżności występujących w orzecznictwie.
                </dd>
            </dl>
            <dl>
                <dt>10.5.</dt>
                <dd>
                    Klient oświadcza, iż został poinformowany przez Kancelarię, że w przypadku przegrania sprawy, na Kliencie spoczywał będzie obowiązek pokrycia kosztów procesu, w tym zapłaty kosztów zastępstwa procesowego na rzecz banku.
                </dd>
            </dl>

            <!-- 11 -->
            <dl>
                <dt class="bold">11.</dt><dd class="heading">Zakończenie umowy.</dd>
                <dt>11.1.</dt>
                <dd>
                    Klient może wypowiedzieć umowę w każdym czasie. Jeżeli wypowiedzenie umowy nastąpi po wysłaniu przez Kancelarię przedsądowego wezwania do banku, opłata za Etap I nie podlega zwrotowi. Wypowiedzenie umowy nie zwalnia Klienta z obowiązku zapłaty na rzecz Kancelarii wynagrodzenia w pełnej wysokości wynikającej z niniejszej umowy (w szczególności premii i kosztów zastępstwa procesowego), jeżeli do uzyskania korzystnego rozstrzygnięcia lub zawarcia ugody doszło na skutek działań podjętych przez Kancelarię. W razie wątpliwości przyjmuje się, że zawarcie ugody na skutek przyjęcia propozycji banku złożonej po wysłaniu przedsądowego wezwania przez Kancelarię, stanowi skutek działań podjętych przez kancelarię.
                </dd>
            </dl>
            <dl>
                <dt>11.2.</dt>
                <dd>
                    Kancelaria ma prawo wypowiedzieć niniejszą umowę za 30-dniowym wypowiedzeniem, jeśli Klient narusza warunki umowy, w szczególności – mimo upomnienia nie płaci uzgodnionego wynagrodzenia lub nie przekazuje wymaganych dokumentów.
                </dd>
            </dl>
            <dl>
                <dt>11.3.</dt>
                <dd>
                    Jeśli umowa zostaje zawarta poza lokalem Kancelarii lub na odległość, Klient ma prawo odstąpić od niniejszej umowy w ciągu 14 dni od jej zawarcia, poprzez przesłanie do Kancelarii oświadczenia o odstąpieniu od umowy (w takim wypadku wzór oświadczenia stanowi załącznik do umowy).
                </dd>
            </dl>

            <!-- 12 -->
            <dl>
                <dt class="bold">12.</dt><dd class="heading">Inne postanowienia.</dd>
                <dt>12.1.</dt>
                <dd>
                    Jeżeli Klientem są dwie lub więcej osób, odpowiedzialność tych osób za zobowiązania wynikające z niniejszej umowy jest solidarna.
                </dd>
                <dt>12.2.</dt>
                <dd>
                    Strony ustalają, iż informacje o stanie sprawy przekazywane będą za pośrednictwem za pośrednictwem poczty elektronicznej, na adresy wskazane w treści niniejszej umowy.
                </dd>
                <dt>12.3.</dt>
                <dd>
                    Strony zobowiązane są do informowania się wzajemnie o każdej zmianie danych kontaktowych. W przypadku niedochowania powyższego obowiązku doręczenia na adresy wskazane w niniejszej umowie uważa się za skuteczne.
                </dd>
                <dt>12.4.</dt>
                <dd>
                    Zmiany niniejszej umowy, jak również jej wypowiedzenie, wymagają formy pisemnej pod rygorem nieważności.
                </dd>
                <dt>12.5.</dt>
                <dd>
                    Umowa została sporządzona w dwóch jednobrzmiących egzemplarzach, po jednym dla każdej ze stron.
                </dd>
            </dl>

            <!-- 13 -->
            <dl>
                <dt class="bold">13.</dt><dd class="heading">Harmonogram płatności.</dd>
                <dt></dt><dd>


                    <table>

                        <tr>
                            <td class="bold" colspan="2">Etap 1</td>
                            <td class="bold">Termin płatności</td>
                        </tr>

                        @for($i = 1; $i <= $e->installments; $i++)
                        <tr>
                            <td>Rata {{ $i }}</td>
                            <td>{{ bp_currency($e->stage_one_fee/$e->installments) }}</td>
                            <td>{{ bp_human_date(date("Y-m-d", strtotime("+".($i-1)." month", strtotime($e->first_installment_date))), 'dot') }}</td>
                        </tr>
                        @endfor

                        <tr>
                            <td class="bold">Etap 2</td>
                            <td colspan="3"></td>
                        </tr>

                        <tr>
                            <td>Rata 1</td>
                            <td>{{ bp_currency($e->stage_two_fee/2) }}</td>
                            <td>7 dni od wniesienia apelacji lub otrzymania apelacji banku</td>
                        </tr>
                        <tr>
                            <td>Rata 2</td>
                            <td>{{ bp_currency($e->stage_two_fee/2) }}</td>
                            <td>30 dni od terminu płatności I raty</td>
                        </tr>

                        <tr>
                            <td class="bold">Opłata za rozprawę</td>
                            <td>{{ bp_currency($e->hearing_fee) }}</td>
                            <td>najpóźniej w dniu rozprawy</td>
                        </tr>
                        <tr>
                            <td class="bold" colspan="2">Koszty zastępstwa procesowego</td>
                            <td>30 dni od uprawomocnienia się orzeczenia lub zawarcia ugody</td>
                        </tr>
                        @if($e->is_bonus)
                        <tr>
                            <td class="bold" colspan="2">Premia w razie wygranej</td>
                            <td>30 dni od uprawomocnienia się orzeczenia lub zawarcia ugody</td>
                        </tr>
                        @endif

                    </table>

                    <br>Płatności należy dokonywać na rachunek bankowy Kancelarii o numerze:
                    <br><span class="bold">97 1090 1290 0000 0001 3186 6931</span>


                </dd>
            </dl>


        </div>

        <table style="margin: 60px 0 0 20px; width: 95%">
            <tr>
                <td style="width: 60%; border:none; border-top: 1px solid #000" class="bold">Klient</td>
                <td style="width: 10%; min-width: 0; border:none;">&nbsp;</td>
                <td style="width: 30% !important; border:none; border-top: 1px solid #000" class="bold">Kancelaria</td>
            </tr>
        </table>
    </div>

@if($osobne_dla_kazdego_klienta)

    <!-- PEŁNOMOCNICTWO -->
    @include('print.zlecenia.elements.pelnomocnictwo-osobne')
    <!-- ODWOŁANIE PEŁNOMOCNICTWA -->
    {{-- @include('print.zlecenia.elements.odwolanie-osobne') --}}

@else

    <!-- PEŁNOMOCNICTWO -->
    @include('print.zlecenia.elements.pelnomocnictwo')
    <!-- ODWOŁANIE PEŁNOMOCNICTWA -->
    {{-- @include('print.zlecenia.elements.odwolanie') --}}

@endif




<!-- OŚWIADCZENIA -->
@include('print.zlecenia.elements.oswiadczenia')

<!-- RODO -->
@include('print.zlecenia.elements.rodo')


</div>
