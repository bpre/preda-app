<div>

<!-- KOMPARYCJA -->
@include('print.zlecenia.elements.komparycja')

<!-- TREŚĆ UMOWY -->
    <div style="page-break-after: always;">
        <div style="border-top:1px solid #000" class="mt-5">

            <!-- 1 -->
            <dl>
                <dt class="bold">1.</dt><dd class="heading">Przedmiot umowy.</dd>
            </dl>
            <dl>
                <dt>1.1.</dt>
                <dd>
                    Klient zleca Kancelarii świadczenie usług prawnych w celu sądowego dochodzenia od banku roszczeń wynikających z opisanej wyżej umowy {{ $pozyczka ? 'pożyczki' : 'kredytowej' }} (ustalenia nieważności umowy, ustalenia bezskuteczności poszczególnych postanowień umowy{{ $is_getin ? '' : ', zasądzenia zwrotu świadczeń zapłaconych nienależnie bankowi' }}).
                </dd>
            </dl>
            <dl>
                <dt>1.2.</dt>
                <dd>
                    Zakres objętych niniejszą umową usług obejmuje:

                    <br>
                    <div class="mt-5"><strong>Etap I</strong>:</div>

                    <ol>
                        <li>przygotowanie i wysłanie do banku wezwania przedsądowego;</li>
                        <li>przygotowanie i złożenie pozwu;</li>
                        <li>przygotowanie i złożenie repliki na odpowiedź na pozew;</li>
                        <li>przygotowanie i złożenie pism w wykonaniu zobowiązania sądu;</li>
                        <li>reprezentowanie Klienta przed sądem I instancji (do wydania wyroku).</li>
                    </ol>

                    <strong>Etap II</strong>:

                    <ol>
                        <li>przygotowanie apelacji (jeśli będzie konieczne);
                        <li>przygotowanie odpowiedzi na apelację banku;</li>
                        <li>reprezentowanie Klienta przed sądem II instancji.</li>
                    </ol>

                    <strong>Etap III</strong>:

                    <ol>
                        <li>wykreślenie hipoteki (jeśli nie została wykreślona wcześniej){{ $is_getin ? '.' : ';' }}</li>
                        @if(!$is_getin)
                            <li>rozliczenie nieważnej umowy.</li>
                        @endif
                    </ol>

                </dd>
            </dl>


            <!-- 2 -->
            <dl>
                <dt class="bold">2.</dt><dd class="heading">Obowiązki klienta.</dd>
            </dl>
            <dl>
                <dt>2.1.</dt>
                <dd>
                    Klient zobowiązany jest do udzielania Kancelarii wszelkich informacji i dostarczania wszelkich wskazanych przez Kancelarię dokumentów niezbędnych do dochodzenia roszczeń, w szczególności do przedłożenia: umowy {{ $pozyczka ? 'pożyczki' : 'kredytowej' }} wraz z aneksami i załącznikami, wskazanych przez Kancelarię zaświadczeń oraz korespondencji prowadzonej z bankiem.
                </dd>
            </dl>
            <dl>
                <dt>2.2.</dt>
                <dd>
                    Klient zobowiązany jest do niezwłocznego informowania Kancelarii o wszelkich zdarzeniach mogących mieć wpływ na realizację niniejszej umowy (w szczególności o zamiarze całkowitej spłaty {{ $pozyczka ? 'pożyczki' : 'kredytu' }}), a także do niepodejmowania bez porozumienia z Kancelarią działań mogących mieć wpływ na zasadność i wysokość roszczeń dochodzonych na podstawie niniejszej umowy. W szczególności Klient zobowiązuje się, że bez porozumienia z Kancelarią nie będzie podpisywał aneksów do umowy {{ $pozyczka ? 'pożyczki' : 'kredytowej' }}, porozumień, ugód, itp.
                </dd>
            </dl>


            <!-- 3 -->
            <dl>
                <dt class="bold">3.</dt><dd class="heading">Terminy.</dd>
            </dl>
            <dl>
                <dt></dt>
                <dd>
                    Kancelaria zobowiązana jest wysłać przedsądowe wezwanie do banku nie później niż w terminie 2 tygodni od dostarczenia przez Klienta wszystkich dokumentów niezbędnych do dochodzenia roszczeń oraz przygotować pozew nie później niż w terminie 3 miesięcy od dostarczenia tych dokumentów.
                </dd>
            </dl>


            <!-- 4 -->
            <dl>
                <dt class="bold">4.</dt><dd class="heading">Opłaty.</dd>
            </dl>
            <dl>
                <dt>4.1.</dt>
                <dd>
                    Wszelkie opłaty niezbędne do celowego dochodzenia roszczeń obciążają Klienta, w szczególności: opłaty od pozwu, od apelacji, opłaty skarbowe od pełnomocnictw, opłaty z tytułu wynagrodzenia biegłych, a także opłaty z tytułu zwrotu kosztów procesu stronie przeciwnej w przypadku przegrania sprawy lub częściowego przegrania sprawy.
                </dd>
            </dl>
            <dl>
                <dt>4.2.</dt>
                <dd>
                    Klient zobowiązuje się dokonywać opłat w terminach: wskazanych przez sąd, wynikających z obowiązujących przepisów lub wskazanych przez Kancelarię.
                </dd>
            </dl>
            <dl>
                <dt>4.3.</dt>
                <dd>
                    Kancelaria nie jest zobowiązana do dokonywania jakichkolwiek opłat w imieniu Klienta i nie ponosi odpowiedzialności za niedokonanie przez Klienta opłaty lub niedokonanie jej w terminie lub właściwej wysokości, jeśli o konieczności dokonania opłaty, jej wysokości i terminie Klient został należycie poinformowany.
                </dd>
            </dl>


            <!-- 5 -->
            <dl>
                <dt class="bold">5.</dt><dd class="heading">Wynagrodzenie podstawowe.</dd>
            </dl>
            <dl>
                <dt>5.1.</dt>
                <dd>
                    Klient zapłaci Kancelarii <strong>opłatę wstępną</strong> w wysokości <strong>{{ bp_currency($e->stage_one_fee) }}</strong> – w terminie 7 dni od zawarcia niniejszej umowy.
                </dd>
            </dl>
            <dl>
                <dt>5.2.</dt>
                <dd>
                    W razie wygranej Klient zapłaci Kancelarii <strong>premię</strong> w wysokości: <strong>{{ bp_currency($e->bonus_fee) }}</strong>

                    @if($e->bonus_percent != 100)
                    , jednak <strong>nie więcej niż {{ $e->bonus_percent }}% korzyści Klienta</strong>
                    @endif

                    – w terminie 30 dni od uprawomocnienia się wyroku lub zawarcia ugody.
                </dd>
            </dl>
            <dl>
                <dt>5.3.</dt>
                <dd>
                    Premia za wygranie sprawy przysługuje w razie prawomocnego ustalenia przez sąd nieważności umowy (nieistnienia stosunku prawnego) lub bezskuteczności postanowień kształtujących mechanizm indeksacji / denominacji (w sentencji lub uzasadnieniu wyroku), a także w przypadku zawarcia ugody.
                </dd>
            </dl>
            <dl>
                <dt>5.4.</dt>
                <dd>
                    Przez „korzyści Klienta”, o których mowa w pkt 5.2, należy rozumieć kwotę, o którą zmieni się na korzyść Klienta bilans wzajemnych zobowiązań Klienta i banku. Kwota ta stanowi sumę: 1) kwoty, o którą obniży się saldo zadłużenia oraz 2) kwoty, którą bank zobowiązany będzie zapłacić na rzecz Klienta.
                </dd>
            </dl>
            <dl>
                <dt>5.5.</dt>
                <dd>
                    Wszystkie kwoty wskazane w niniejszej umowie są kwotami brutto (zawierają podatek VAT).
                </dd>
            </dl>


            <!-- 6 -->
            <dl>
                <dt class="bold">6.</dt><dd class="heading">Wynagrodzenie dodatkowe – koszty zastępstwa procesowego.</dd>
            </dl>
            <dl>
                <dt>6.1.</dt>
                <dd>
                    W sprawach prowadzonych na podstawie niniejszej umowy koszty zastępstwa procesowego zasądzone prawomocnie od banku na rzecz Klienta lub wynikające z ugody zawartej przez Klienta stanowią w całości dodatkowe wynagrodzenie Kancelarii – w wysokości wynikającej z wyroków, postanowień lub z treści ugody.
                </dd>
            </dl>
            <dl>
                <dt>6.2.</dt>
                <dd>
                    Zawierając z bankiem ugodę Klient zobowiązuje się zawrzeć w niej postanowienie przyznające Kancelarii koszty zastępstwa procesowego w stawce nie niższej niż stawka minimalna wynikająca z rozporządzenia Ministra Sprawiedliwości w sprawie opłat za czynności adwokackie. W przypadku zawarcia przez Klienta ugody z bankiem niezawierającej takiego postanowienia, Klient zobowiązuje się zapłacić na rzecz kancelarii wynagrodzenie dodatkowe w kwocie odpowiadającej kosztom zastępstwa procesowego w stawce minimalnej wynikającej z rozporządzenia Ministra Sprawiedliwości w sprawie opłat za czynności adwokackie, w terminie 14 dni od zawarcia ugody.
                </dd>
            </dl>
            <dl>
                <dt>6.3.</dt>
                <dd>
                    Klient upoważnia Kancelarię do odbioru zasądzonych kosztów zastępstwa procesowego bezpośrednio od banku. Jeśli koszty zastępstwa procesowego zasądzone od banku zostaną wypłacone Klientowi, Klient zobowiązuje się przekazać je Kancelarii niezwłocznie, nie później jednak niż w terminie 3 dni od ich otrzymania.
                </dd>
            </dl>


            <!-- 7 -->
            <dl>
                <dt class="bold">7.</dt><dd class="heading">
                    {{ $is_getin ? 'Postępowanie upadłościowe.' : 'Dodatkowe postępowanie.' }}
                </dd>
            </dl>

            @if($is_getin)
                <dl>
                    <dt></dt>
                    <dd>
                        Zakres objętych niniejszą umową usług prawnych nie obejmuje w szczególności reprezentowania Klienta w postępowaniu upadłościowym oraz w postępowaniu z powództwa banku.
                    </dd>
                </dl>
            @else
                <dl>
                    <dt></dt>
                    <dd>
                        Jeżeli po prawomocnym ustaleniu nieważności umowy {{ $pozyczka ? 'pożyczki' : 'kredytowej' }} zajdzie konieczność wszczęcia dodatkowego postępowania sądowego o zapłatę, w celu rozliczenia nieważnej umowy, w szczególności w związku z wyborem określonej taktyki procesowej lub dokonywaniem przez Klienta dalszych spłat rat w toku postępowania o ustalenie nieważności umowy, za prowadzenie takiego dodatkowego postępowania Kancelarii będą przysługiwały wyłącznie koszty zastępstwa procesowego zasądzone przez sąd lub wynikające z zawartej ugody.
                    </dd>
                </dl>
            @endif


            <!-- 8 -->
            <dl>
                <dt class="bold">8.</dt><dd class="heading">Poufność.</dd>
            </dl>
            <dl>
                <dt>8.1.</dt>
                <dd>
                    Kancelaria zobowiązuje się do zachowania w tajemnicy wszelkich informacji uzyskanych przy świadczeniu pomocy prawnej na rzecz Klienta.
                </dd>
            </dl>
            <dl>
                <dt>8.2.</dt>
                <dd>
                    W zakresie niezbędnym do prawidłowego wykonania niniejszej umowy, Klient upoważnia Kancelarię do przekazywania informacji o sprawie osobom trzecim, przy pomocy których Kancelaria świadczy usługi objęte niniejszą umową, w szczególności pełnomocnikom substytucyjnym, aplikantom oraz analitykowi finansowemu.
                </dd>
            </dl>
            <dl>
                <dt>8.3.</dt>
                <dd>
                    Wszelkie informacje, które Klient otrzyma od Kancelarii, są informacjami poufnymi. W szczególności za informacje takie uznawane są: propozycje rozwiązań prawnych, koncepcji, założeń, projekty pism procesowych, pisma procesowe i wszelkie inne dokumenty wytworzone przez Kancelarię przy wykonywaniu umowy. Klient zobowiązuje się zachować w poufności informacje otrzymane od Kancelarii oraz nie wykorzystywać ich, ani nie przekazywać ich osobom trzecim, w celach innych, niż związane z zawarciem i wykonaniem niniejszej umowy. Klient zobowiązuje się do nierozpowszechniania informacji otrzymanych od Kancelarii, w szczególności zawierających rozwiązania prawne, w sieci Internet. Klient nie nabywa praw autorskich do rezultatów pracy Kancelarii mogących stanowić utwory w rozumieniu ustawy o prawie autorskim i prawach pokrewnych.
                </dd>
            </dl>


            <!-- 9 -->
            <dl>
                <dt class="bold">9.</dt><dd class="heading">Oświadczenia.</dd>
            </dl>
            <dl>
                <dt>9.1.</dt>
                <dd>
                   Klient oświadcza, iż upoważnia Kancelarię do decydowania o sposobie prowadzenia sprawy, w szczególności do decydowania o wyborze optymalnej taktyki procesowej. Dokonując wyboru taktyki procesowej Kancelaria zobowiązana jest uwzględniać sytuację faktyczną i prawną Klienta, a także jego uzasadnione sugestie. Zawarcie ugody zawsze jest decyzją Klienta.
                </dd>
            </dl>
            <dl>
                <dt>9.2.</dt>
                <dd>
                    Klient oświadcza, że wyraża zgodę na reprezentowanie go przed sądami przez: adw. Bartosza Prędę, adw. Wiktorię Rajzynger, adw. Joannę Krajewską oraz apl. adw. Beatę Mital-Goryczkę. Reprezentowanie Klienta przed sądami przez inne osoby wymaga zgody klienta. Klient nie sprzeciwia się udziałowi pełnomocnika w rozprawie w formie zdalnej, w przypadku zgody sądu na rozprawę w trybie zdalnym.
                </dd>
            </dl>
            <dl>
                <dt>9.3.</dt>
                <dd>
                    Klient oświadcza, że jest świadomy, iż umowa o świadczenie usług prawnych jest umową starannego działania i Kancelaria, zobowiązując się do zachowania należytej staranności i standardów profesjonalnej obsługi prawnej, nie może zagwarantować uzyskania określonego rezultatu.
                </dd>
            </dl>
            <dl>
                <dt>9.4.</dt>
                <dd>
                    Klient oświadcza, iż został poinformowany przez Kancelarię, że dochodzenie roszczeń objętych niniejszą umową wiąże się z ryzykiem przegrania sprawy. Ryzyko to wynika przede wszystkim z rozbieżności występujących w orzecznictwie w tzw. „sprawach frankowych”.
                </dd>
            </dl>
            <dl>
                <dt>9.5.</dt>
                <dd>
                    Klient oświadcza, iż został poinformowany przez Kancelarię, że w przypadku przegrania sprawy, na Kliencie spoczywał będzie obowiązek pokrycia kosztów procesu, w tym zapłaty kosztów zastępstwa procesowego na rzecz banku.
                </dd>
            </dl>


            <!-- 10 -->
            <dl>
                <dt class="bold">10.</dt><dd class="heading">Zakończenie umowy.</dd>
            </dl>
            <dl>
                <dt>10.1.</dt>
                <dd>
                    Klient może wypowiedzieć umowę w każdym czasie. Jeżeli wypowiedzenie umowy nastąpi po wysłaniu przez Kancelarię przedsądowego wezwania do banku, opłata wstępna nie podlega zwrotowi.
                </dd>
            </dl>
            <dl>
                <dt>10.2.</dt>
                <dd>
                    Wypowiedzenie umowy nie zwalnia Klienta z obowiązku zapłaty na rzecz Kancelarii wynagrodzenia w pełnej wysokości wynikającej z niniejszej umowy (w szczególności premii i kosztów zastępstwa procesowego), jeżeli do zawarcia ugody lub uzyskania korzystnego rozstrzygnięcia doszło na skutek działań podjętych przez Kancelarię. W razie wątpliwości przyjmuje się, że zawarcie ugody na skutek przyjęcia propozycji banku złożonej po wysłaniu przedsądowego wezwania przez Kancelarię, stanowi skutek działań podjętych przez kancelarię.
                </dd>
            </dl>
            <dl>
                <dt>10.3.</dt>
                <dd>
                    Kancelaria ma prawo wypowiedzieć niniejszą umowę za 30-dniowym wypowiedzeniem, jeśli Klient narusza warunki umowy, w szczególności – mimo upomnienia nie płaci uzgodnionego wynagrodzenia lub nie przekazuje wymaganych dokumentów.
                </dd>
            </dl>
            <dl>
                <dt>10.4.</dt>
                <dd>
                    Jeśli umowa zostaje zawarta poza lokalem Kancelarii lub na odległość, Klient ma prawo odstąpić od niniejszej umowy w ciągu 14 dni od jej zawarcia, poprzez przesłanie do Kancelarii oświadczenia o odstąpieniu od umowy (wzór oświadczenia stanowi załącznik do umowy).
                </dd>
            </dl>



            <!-- 11 -->
            <dl>
                <dt class="bold">11.</dt><dd class="heading">Inne postanowienia.</dd>
            </dl>
            <dl>
                <dt>11.1.</dt>
                <dd>
                    Jeżeli Klientem są dwie lub więcej osób, odpowiedzialność tych osób za zobowiązania wynikające z niniejszej umowy jest solidarna.
                </dd>
            </dl>
            <dl>
                <dt>11.2.</dt>
                <dd>
                    Usługi prawne objęte niniejszą umową świadczyć będą adwokaci i radcowie prawni zatrudnieni lub pozostający w stosunku zlecenia z Kancelarią. Poszczególne czynności mogą wykonywać także aplikanci adwokaccy i radcowscy pod nadzorem adwokata lub radcy prawnego.
                </dd>
            </dl>
            <dl>
                <dt>11.3.</dt>
                <dd>
                    Strony ustalają, iż informacje o stanie sprawy przekazywane będą za pośrednictwem środków komunikacji elektronicznej, w szczególności za pośrednictwem poczty elektronicznej.
                </dd>
            </dl>
            <dl>
                <dt>11.4.</dt>
                <dd>
                    Strony zobowiązane są do informowania się wzajemnie o każdej zmianie danych kontaktowych. W przypadku niedochowania powyższego obowiązku doręczenia na adresy wskazane w niniejszej umowie uważa się za skuteczne.
                </dd>
            </dl>
            <dl>
                <dt>11.5.</dt>
                <dd>
                    Zmiany niniejszej umowy, jak również jej wypowiedzenie, wymagają formy pisemnej pod rygorem nieważności.
                </dd>
            </dl>
            <dl>
                <dt>11.6.</dt>
                <dd>
                    Umowa została sporządzona w dwóch jednobrzmiących egzemplarzach, po jednym dla każdej ze stron.
                </dd>
            </dl>

        </div>

        <!-- PODPISY -->
        @include('print.zlecenia.elements.podpisy')

    </div>

<!-- PREMIA - PRZYKŁADY -->
@if($e->bonus_percent != 100)

<div style="page-break-after: always;">

    <div class="center">
        <span class="bold">Załącznik do umowy o świadczenie usług prawnych</span> (wariant z premią)
        <br>zawartej w dniu {{ bp_human_date($e['date'], 'dot') }} r.
    </div>

    <p>

        <br><br>
        Dla pełnego bezpieczeństwa Klienta wprowadziliśmy w naszej umowie mechanizm bezpiecznika. Premia przysługująca kancelarii nigdy nie może przekroczyć 35% korzyści Klienta.

        <br><br>
        <strong>Przykład 1:</strong><br>
        W umowie ustalono premię w wysokości 25.000 zł. Korzyść z wygrania sprawy wyniosła 50.000 zł. Premia kancelarii wyniesie w takim wypadku 17.500 zł, a nie 25.000 zł, bo 35% z 50.000 zł = 17.500 zł (bezpiecznik w postaci limitu 35% korzyści działa).

        <br><br>
        <strong>Przykład 2:</strong><br>
        W umowie ustalono premię w wysokości 25.000 zł. Korzyść z wygrania sprawy wyniosła 100.000 zł. Premia kancelarii wyniesie w takim wypadku 25.000 zł, bo 35% ze 100.000 zł = 35.000 zł (bezpiecznik w postaci limitu 35% nie znajdzie tu zastosowania - niższa jest kwota premii ustalona kwotowo).


        <br><br><br>
        <strong>Jak należy rozumieć „korzyści Klienta”, od których liczymy 35%?</strong>

        <br><br>
        Korzyść klienta = obniżenie salda + kwota zasądzona do zwrotu

        <br><br>
        <strong>Przykład:</strong><br>
        Saldo zadłużenia {{ $pozyczka ? 'pożyczkobiorcy' : 'kredytobiorcy' }} w momencie złożenia pozwu wynosi 100.000 zł. Na skutek unieważnienia umowy {{ $pozyczka ? 'pożyczkobiorca' : 'kredytobiorca' }} nie tylko nie musi już nic płacić bankowi (obniżenie salda o 100.000 zł), ale dodatkowo jeszcze bank musi zapłacić {{ $pozyczka ? 'pożyczkobiorcy' : 'kredytobiorcy' }} 50.000 zł. Korzyść {{ $pozyczka ? 'pożyczkobiorcy' : 'kredytobiorcy' }} wynosi w takim wypadku 150.000 zł, bo:

        <br><br>
        100.000 zł (obniżenie salda) + 50.000 zł (kwota zasądzona do zwrotu) = 150.000 zł.

    </p>

    <br><br><br><br>

    @if($kredytobiorcy)

        @foreach($kredytobiorcy->sortBy('sort') as $klient)
            <div style="width: 40%; margin: 0 25px; float: left; border-top: 1px solid #000;" class="bold">
                <small>{{ $klient->label }}</small>
            </div>
            @if($loop->iteration % 2 == 0)<br><br><br><br>@endif
        @endforeach

    @endif

</div>
@endif

<!-- PEŁNOMOCNICTWO -->
@include('print.zlecenia.elements.pelnomocnictwo')

<!-- OŚWIADCZENIA -->
@include('print.zlecenia.elements.oswiadczenia')

<!-- RODO -->
@include('print.zlecenia.elements.rodo')

<!-- POTRĄCZENIE -->
@include('print.zlecenia.elements.potracenie-wybor')

</div>
