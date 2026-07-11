<div>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'RobotoCondensed';
            font-size: 13px;
            margin: 0cm 0.5cm 0cm 1.2cm;
        }
        dd {
            margin: -18px 3px 0 30px;
            line-height: 100%;
        }
        dl {
            page-break-inside:avoid;
            margin: 0;
        }
        table {
            width: 100%;
            border-spacing: 0;
            margin: 0;
            font-size: 11px;
        }
        td {
            border: 1px solid #000;
            border-top: 0;
            border-left: 0;
            padding: 5px;
            line-height: 100%;
            min-width: 120px;
        }
        table tr:first-child td {
            border-top: 1px solid #000;
        }
        table tr td:first-child {
            border-left: 1px solid #000;
        }
        thead td {
            text-align: center;
        }
        /* tbody td {
            text-align: right;
        } */
        .bold {
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        dl dt.bold {
            margin-top: 5px;
        }
        .mt-5 {
            margin-top: 5px;
        }
        .heading {
            font-weight: bold;
            margin-bottom: 5px;
        }
        h1 {
            margin: 30px 0;
            font-size: 20px;
        }
    </style>
    <div>
        <div class="center">
            <span class="bold">Umowa o świadczenie usług prawnych</span>
            <br>zawarta w dniu {{ bp_human_date($e['date'], 'dot') }} r.
        </div>

        @if($kredytobiorcy)
            <div class="bold">Klient</div>
            @foreach($kredytobiorcy as $klient)
                {{ $klient->label }}, PESEL: {{ $klient->pesel  }}, {{ $klient->adr }}{{ $klient->email ? ', '.$klient->email : '' }}<br>
            @endforeach
        @else
            <span class="text-red-500">BRAK KLIENTÓW!</span>
        @endif

        <div class="mt-5 bold">Kancelaria</div>
        <div>
            PRĘDA Kancelaria Adwokacka - Adwokat Bartosz Pręda, ul. Szewska 7, 67-200 Głogów
            <br>NIP: 6922321750, nr rachunku bankowego: 97 1090 1290 0000 0001 3186 6931, reprezentowana przez {{  $reprezentant }}<br>
        </div>

        @if($e->credits)
            <div class="mt-5 bold">
                {{ $e->credits->count() > 1 ? 'Umowy kredytowe' : 'Umowa kredytowa' }}
            </div>
            @foreach($e->credits as $umowa)
                Nr: {{ $umowa->number }} z dnia {{ bp_human_date($umowa->date, 'dot') }} r. zawarta z: {{ $umowa->former_banks->organization }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK UMÓW!</span>
        @endif
    </div>

<!-- TREŚĆ UMOWY -->
    <div style="page-break-after: always;">
        <div style="border-top:1px solid #000" class="mt-5">

            <!-- 1 -->
            <dl>
                <dt class="bold">1.</dt><dd class="heading">Przedmiot umowy.</dd>
                <dt>1.1.</dt>
                <dd>
                    Klient zleca Kancelarii świadczenie usług prawnych w zakresie dochodzenia od banku roszczeń wynikających z opisanej wyżej umowy kredytowej (ustalenia nieważności umowy, ustalenia bezskuteczności poszczególnych postanowień umowy{{ $is_getin ? '' : ', zasądzenia zwrotu świadczeń zapłaconych nienależnie bankowi' }}).
                </dd>
                <dt>1.2.</dt>
                <dd>
                    Zakres objętych niniejszą umową usług obejmuje:
                    <br class="mt-5">
                    <strong>Etap I</strong>: przygotowanie wniosku do banku o wydanie zaświadczenia, w przypadku niewydania przez bank zaświadczenia w terminie -
                    przygotowanie na wniosek klienta reklamacji, {{ $is_getin ? 'ustalenie sumy dokonanych spłat' : 'oszacowanie roszczeń w związku z nieważnością umowy oraz bezskutecznością poszczególnych jej postanowień' }}, przygotowanie i wysłanie do banku przedsądowego wezwania,
                    przygotowanie i złożenie pozwu (nie później niż w terminie 3 miesięcy od dostarczenia przez Klienta wszystkich dokumentów), reprezentowanie Klienta przed sądem I instancji, przygotowywanie dalszych pism procesowych po wniesieniu pozwu;
                    <br class="mt-5">
                    <strong>Etap II</strong>: reprezentowanie Klienta przed sądem II instancji;
                    <br class="mt-5">
                    <strong>Etap III</strong>: {{ $is_getin ? '' : 'rozliczenie nieważnej umowy oraz ' }}wykreślenie hipoteki.
                </dd>
                @if($is_getin)
                    <dt>1.3.</dt>
                    <dd>
                        Zakres objętych niniejszą umową usług prawnych nie obejmuje w szczególności reprezentowania Klienta w postępowaniu upadłościowym oraz w postępowaniu z powództwa banku.
                    </dd>
                @endif
            </dl>

            <!-- 2 -->
            <dl>
                <dt class="bold">2.</dt><dd class="heading">Obowiązki klienta.</dd>
                <dt>2.1.</dt>
                <dd>
                    Klient zobowiązany jest do udzielania Kancelarii wszelkich informacji i dostarczania wszelkich wskazanych przez Kancelarię dokumentów niezbędnych do dochodzenia roszczeń, w szczególności do przedłożenia: umowy kredytowej wraz z aneksami i załącznikami, wskazanych przez Kancelarię zaświadczeń oraz korespondencji prowadzonej z bankiem.
                </dd>
                <dt>2.2.</dt>
                <dd>
                    Klient zobowiązany jest do niezwłocznego informowania Kancelarii o wszelkich zdarzeniach mogących mieć wpływ na realizację niniejszej umowy (w szczególności o zamiarze całkowitej spłaty kredytu), a także do niepodejmowania bez porozumienia z Kancelarią działań mogących mieć wpływ na zasadność i wysokość roszczeń dochodzonych na podstawie niniejszej umowy. W szczególności Klient zobowiązuje się, że bez porozumienia z Kancelarią nie będzie podpisywał aneksów do umowy kredytowej, porozumień, ugód, itp.
                </dd>
            </dl>

            <!-- 3 -->
            <dl>
                <dt class="bold">3.</dt><dd class="heading">Opłaty.</dd>
                <dt>3.1.</dt>
                <dd>
                    Wszelkie opłaty niezbędne do celowego dochodzenia roszczeń obciążają Klienta, w szczególności: opłaty od pozwu, od apelacji, opłaty skarbowe od pełnomocnictw, opłaty z tytułu wynagrodzenia biegłych, a także opłaty z tytułu zwrotu kosztów procesu stronie przeciwnej w przypadku przegrania sprawy lub częściowego przegrania sprawy.
                </dd>
                <dt>3.2.</dt>
                <dd>
                    Klient zobowiązuje się dokonywać opłat w terminach: wskazanych przez sąd, wynikających z obowiązujących przepisów lub wskazanych przez Kancelarię.
                </dd>
                <dt>3.3.</dt>
                <dd>
                    Kancelaria nie jest zobowiązana do dokonywania jakichkolwiek opłat w imieniu Klienta i nie ponosi odpowiedzialności za niedokonanie przez Klienta opłaty lub niedokonanie jej w terminie lub właściwej wysokości, jeśli o konieczności dokonania opłaty, jej wysokości i terminie Klient został należycie poinformowany.
                </dd>
            </dl>

            <!-- 4 -->
            <dl>
                <dt class="bold">4.</dt><dd class="heading">Wynagrodzenie podstawowe.</dd>
                <dt>4.1.</dt>
            </dl>
            <dl>
                <dd>
                    Klient zapłaci Kancelarii za Etap I: <strong>{{ bp_currency($e->stage_one_fee) }}</strong>, za Etap II: <strong>{{ bp_currency($e->stage_two_fee) }}</strong>, za Etap III: <strong>0 zł</strong>@if($e->is_bonus), a ponadto premię w razie wygranej. @else.@endif
                </dd>
            </dl>
            @if($e->is_bonus)
                <dl>
                    <dt>4.2.</dt>
                    <dd>
                        Premia w razie wygranej przysługuje w wysokości:
                        @if(false)
                            <strong>{{ $e->bonus_percent }}%</strong> kwoty kredytu (określonej w umowie lub aneksie do umowy), jednak nie niższej niż <strong>{{ bp_currency($e->bonus_minimum) }}</strong>.
                        @else
                            <strong>{{ bp_currency($e->bonus_fee) }}</strong>.
                        @endif
                    </dd>
                </dl>

                <dl>
                    <dt>4.3.</dt>
                    <dd>
                        Premia za wygranie sprawy przysługuje w razie prawomocnego ustalenia przez sąd nieważności umowy lub bezskuteczności postanowień kształtujących mechanizm indeksacji / denominacji (w sentencji lub uzasadnieniu wyroku), a także w przypadku zawarcia ugody.
                    </dd>
                </dl>

                @if($e->label == '2026: Bezpieczny start (z premią)' && $e->bonus_percent < 100)

                    <dl>
                        <dt>4.4.</dt>
                        <dd>
                            Jeżeli premia przysługująca Kancelarii w razie wygranej, określona w pkt 4.2, przekroczyłaby 35% korzyści Klienta, wówczas premia ulega <strong>obniżeniu</strong> do kwoty
                            stanowiącej <strong>35% korzyści</strong> Klienta.
                        </dd>
                    </dl>

                    <dl>
                        <dt>4.5.</dt>
                        <dd>
                            Przez „korzyści Klienta”, o których mowa w pkt 4.4 należy rozumieć kwotę, o którą zmieni się na korzyść Klienta bilans wzajemnych zobowiązań Klienta i banku. Kwota ta stanowi sumę:
                            1) kwoty, o którą obniży się saldo zadłużenia oraz 2) kwoty, którą bank zobowiązany będzie zapłacić na rzecz Klienta.
                        </dd>
                    </dl>
                @endif
            @endif
            <dl>
                <dt>4.{{ $e->is_bonus ?
                            ($e->label == '2026: Bezpieczny start (z premią)' && $e->bonus_percent < 100 ? 6 : 4)
                            : 2 }}.
                </dt>
                <dd>
                    Terminy płatności określa harmonogram zawarty w pkt 11 umowy.
                </dd>
            </dl>
            <dl>
                <dt>4.{{ $e->is_bonus ?
                            ($e->label == '2026: Bezpieczny start (z premią)' && $e->bonus_percent < 100 ? 7 : 5)
                             : 3 }}.
                </dt>
                <dd>
                    Wszystkie kwoty wskazane w niniejszej umowie są kwotami brutto (zawierają podatek VAT).
                </dd>
            </dl>

            <!-- 5 -->
            <dl>
                <dt class="bold">5.</dt>
                <dd class="heading">
                    @if($e->hearing_fee == 0)
                        Rozprawy.
                    @else
                        Wynagrodzenie za rozprawy.
                    @endif
                </dd>
            </dl>

            @if($e->hearing_fee == 0)

                <dl>
                    <dt></dt>
                    <dd>
                        Za stawiennictwo na rozprawie Kancelarii nie przysługuje dodatkowe wynagrodzenie. Klient nie sprzeciwia się udziałowi pełnomocnika w rozprawie w formie zdalnej, w przypadku zgody sądu na rozprawę w trybie zdalnym.
                    </dd>
                </dl>

            @else

                <dl>
                    <dt>5.1.</dt>
                    <dd>
                        Za każdą rozprawę, bez względu na jej formę (stacjonarna, on-line) oraz czas trwania, Klient zapłaci kancelarii wynagrodzenie w kwocie  <strong>{{ bp_currency($e->hearing_fee) }}</strong>.
                    </dd>
                    <dt>5.2.</dt>
                    <dd>
                        Suma opłat za wszystkie rozprawy <strong>nie może przekroczyć 1.999 zł</strong>.
                    </dd>
                </dl>

            @endif

            <!-- 6 -->
            <dl>
                <dt class="bold">6.</dt><dd class="heading">Wynagrodzenie dodatkowe – koszty zastępstwa procesowego.</dd>
                <dt>6.1.</dt>
                <dd>
                    W sprawach prowadzonych na podstawie niniejszej umowy koszty zastępstwa procesowego zasądzone prawomocnie od banku na rzecz klienta lub wynikające z ugody stanowią w całości dodatkowe wynagrodzenie Kancelarii – w wysokości wynikającej z wyroków, postanowień lub z treści ugody.
                </dd>

                <dt>6.2.</dt>
                <dd>
                    Zawierając z bankiem ugodę Klient zobowiązuje się zawrzeć w niej postanowienie przyznające Kancelarii koszty zastępstwa procesowego w stawce nie niższej niż stawka minimalna wynikająca z rozporządzenia Ministra Sprawiedliwości w sprawie opłat za czynności adwokackie. W przypadku zawarcia przez Klienta ugody z bankiem niezawierającej takiego postanowienia, Klient zobowiązuje się zapłacić na rzecz kancelarii wynagrodzenie dodatkowe w kwocie odpowiadającej kosztom zastępstwa procesowego w stawce minimalnej wynikającej z rozporządzenia Ministra Sprawiedliwości w sprawie opłat za czynności adwokackie, w terminie 14 dni od zawarcia ugody.
                </dd>



                <dt>6.3.</dt>
                <dd>
                    Klient upoważnia Kancelarię do odbioru zasądzonych kosztów zastępstwa procesowego bezpośrednio od banku. Jeśli koszty zastępstwa procesowego zasądzone od banku zostaną wypłacone Klientowi, Klient zobowiązuje się niezwłocznie przekazać je Kancelarii.
                </dd>
                @if(!$is_getin)
                    <dt>6.4.</dt>
                    <dd>
                        Jeżeli po prawomocnym ustaleniu nieważności umowy kredytowej zajdzie konieczność wszczęcia dodatkowego postępowania sądowego o zapłatę,
                        w celu rozliczenia nieważnej umowy, w szczególności w związku z wyborem określonej taktyki procesowej lub dokonywaniem przez Klienta dalszych spłat rat w toku
                        postępowania o ustalenie nieważności umowy, za prowadzenie takiego dodatkowego postępowania Kancelarii będą przysługiwały wyłącznie koszty zastępstwa procesowego
                        zasądzone przez sąd lub wynikające z zawartej ugody{{ $e->hearing_fee > 0 ? ' oraz wynagrodzenie za rozprawy' : '' }}.
                    </dd>
                @endif
            </dl>

            <!-- 7 -->
            <dl>
                <dt class="bold">7.</dt><dd class="heading">Poufność i prawa autorskie.</dd>
                <dt>7.1.</dt>
                <dd>
                    Kancelaria zobowiązuje się do zachowania w tajemnicy wszelkich informacji uzyskanych przy świadczeniu pomocy prawnej na rzecz Klienta.
                </dd>
                <dt>7.2.</dt>
                <dd>
                    W zakresie niezbędnym do prawidłowego wykonania niniejszej umowy, Klient upoważnia Kancelarię do przekazywania informacji o sprawie osobom trzecim, przy pomocy których Kancelaria świadczy usługi objęte niniejszą umową, w szczególności pełnomocnikom substytucyjnym, aplikantom oraz analitykowi finansowemu.
                </dd>
                <dt>7.3.</dt>
                <dd>
                    Wszelkie informacje, które Klient otrzyma od Kancelarii, są informacjami poufnymi. W szczególności za informacje takie uznawane są: propozycje rozwiązań prawnych, koncepcji, założeń, projekty pism procesowych, pisma procesowe i wszelkie inne dokumenty wytworzone przez Kancelarię przy wykonywaniu umowy. Klient zobowiązuje się zachować w poufności informacje otrzymane od Kancelarii oraz nie wykorzystywać ich, ani nie przekazywać ich osobom trzecim, w celach innych, niż związane z zawarciem i wykonaniem niniejszej umowy. Klient zobowiązuje się do nierozpowszechniania informacji otrzymanych od Kancelarii, w szczególności zawierających rozwiązania prawne, w sieci Internet.
                </dd>
                <dt>7.4.</dt>
                <dd>
                    Klient nie nabywa praw autorskich do rezultatów pracy Kancelarii mogących stanowić utwory w rozumieniu ustawy o prawie autorskim i prawach pokrewnych.
                </dd>
            </dl>

            <!-- 8 -->
            <dl>
                <dt class="bold">8.</dt><dd class="heading">Oświadczenia.</dd>
                <dt>8.1.</dt>
                <dd>
                    Klient oświadcza, iż upoważnia Kancelarię do decydowania o sposobie prowadzenia sprawy, w szczególności do decydowania o wyborze optymalnej taktyki procesowej. Dokonując wyboru taktyki procesowej Kancelaria zobowiązana jest uwzględniać sytuację faktyczną i prawną Klienta, a także jego uzasadnione sugestie. Zawarcie ugody zawsze jest decyzją Klienta.
                </dd>
            </dl>
            <dl>
                <dt>8.2.</dt>
                <dd>
                    Klient oświadcza, że wyraża zgodę na reprezentowanie go przed sądami przez: adw. Bartosza Prędę, adw. Wiktorię Rajzynger, adw. Joannę Krajewską oraz apl. adw. Beatę Mital-Goryczkę. Reprezentowanie Klienta przed sądami przez inne osoby wymaga zgody klienta.
                </dd>
            </dl>
            <dl>
                <dt>8.3.</dt>
                <dd>
                    Klient oświadcza, że jest świadomy, iż umowa o świadczenie usług prawnych jest umową starannego działania i Kancelaria, zobowiązując się do zachowania należytej staranności i standardów profesjonalnej obsługi prawnej, nie może zagwarantować uzyskania określonego rezultatu.
                </dd>
            </dl>
            <dl>
                <dt>8.4.</dt>
                <dd>
                    Klient oświadcza, iż został poinformowany przez Kancelarię, że dochodzenie roszczeń objętych niniejszą umową wiąże się z ryzykiem przegrania sprawy. Ryzyko to wynika przede wszystkim z rozbieżności występujących w orzecznictwie w tzw. „sprawach frankowych”.
                </dd>
            </dl>
            <dl>
                <dt>8.5.</dt>
                <dd>
                    Klient oświadcza, iż został poinformowany przez Kancelarię, że w przypadku przegrania sprawy, na Kliencie spoczywał będzie obowiązek pokrycia kosztów procesu, w tym zapłaty kosztów zastępstwa procesowego na rzecz banku.
                </dd>
            </dl>

            <!-- 9 -->
            <dl>
                <dt class="bold">9.</dt><dd class="heading">Zakończenie umowy.</dd>
                <dt>9.1.</dt>
                <dd>
                    Klient może wypowiedzieć umowę w każdym czasie. Wypowiedzenie umowy nie zwalnia Klienta z obowiązku zapłaty wynagrodzenia na rzecz Kancelarii. W przypadku wypowiedzenia umowy przed wydaniem wyroku przez sąd I instancji - Klient nie jest zobowiązany do zapłaty na rzecz Kancelarii wynagrodzenie za Etap II. Jeżeli jednak na skutek działań podjętych przez Kancelarię Klient uzyska korzystne dla siebie rozstrzygnięcie, wówczas nie zwalnia go to z obowiązku zapłaty na rzecz Kancelarii wynagrodzenia w pełnej wysokości wynikającej z niniejszej umowy (w szczególności premii i kosztów zastępstwa procesowego).
                </dd>
            </dl>
            <dl>
                <dt>9.2.</dt>
                <dd>
                    Kancelaria ma prawo wypowiedzieć niniejszą umowę za 30-dniowym wypowiedzeniem, jeśli Klient narusza warunki umowy, w szczególności – mimo upomnienia nie płaci uzgodnionego wynagrodzenia lub nie przekazuje wymaganych dokumentów. W przypadku wypowiedzenia umowy przez Kancelarię z powodu naruszania warunków umowy przez Klienta, Klient obowiązany jest zapłacić wynagrodzenie za wykonane już czynności.
                </dd>
            </dl>
            <dl>
                <dt>9.3.</dt>
                <dd>
                    Umowa ulega rozwiązaniu w przypadku zawarcia przez Klienta ugody z bankiem. Zawarcie ugody nie zwalnia Klienta z obowiązku zapłaty wynagrodzenia na rzecz Kancelarii. W przypadku zawarcia ugody przed wydaniem wyroku przez sąd I instancji - Klient nie jest zobowiązany do zapłaty na rzecz Kancelarii wynagrodzenie za etap II.
                </dd>
            </dl>
            <dl>
                <dt>9.4.</dt>
                <dd>
                    Jeśli umowa zostaje zawarta poza lokalem Kancelarii lub na odległość, Klient ma prawo odstąpić od niniejszej umowy w ciągu 14 dni od jej zawarcia, poprzez przesłanie do Kancelarii oświadczenia o odstąpieniu od umowy (wzór oświadczenia stanowi załącznik do umowy).
                </dd>
            </dl>

            <!-- 10 -->
            <dl>
                <dt class="bold">10.</dt><dd class="heading">Inne postanowienia.</dd>
                <dt>10.1.</dt>
                <dd>
                    Jeżeli Klientem są dwie lub więcej osób, odpowiedzialność tych osób za zobowiązania wynikające z niniejszej umowy jest solidarna.
                </dd>
                <dt>10.2.</dt>
                <dd>
                    Usługi prawne objęte niniejszą umową świadczyć będą adwokaci i radcowie prawni zatrudnieni lub pozostający w stosunku zlecenia z Kancelarią. Poszczególne czynności mogą wykonywać także aplikanci adwokaccy i radcowscy pod nadzorem adwokata lub radcy prawnego.
                </dd>
                <dt>10.3.</dt>
                <dd>
                    Strony ustalają, iż informacje o stanie sprawy przekazywane będą za pośrednictwem środków komunikacji elektronicznej, w szczególności za pośrednictwem poczty elektronicznej.
                </dd>
                <dt>10.4.</dt>
                <dd>
                    Strony zobowiązane są do informowania się wzajemnie o każdej zmianie danych kontaktowych. W przypadku niedochowania powyższego obowiązku doręczenia na adresy wskazane w niniejszej umowie uważa się za skuteczne.
                </dd>
                <dt>10.5.</dt>
                <dd>
                    Zmiany niniejszej umowy, jak również jej wypowiedzenie, wymagają formy pisemnej pod rygorem nieważności.
                </dd>
                <dt>10.6.</dt>
                <dd>
                    Umowa została sporządzona w dwóch jednobrzmiących egzemplarzach, po jednym dla każdej ze stron.
                </dd>
            </dl>

            <!-- 11 -->
            <dl>
                <dt class="bold">11.</dt><dd class="heading">Harmonogram płatności.</dd>
                <dt></dt><dd>


                    <table>

                        <tr>
                            <td class="bold" colspan="2">

                                @if($e->stage_two_fee == 0)
                                    Rodzaj opłaty
                                @else
                                    Etap 1
                                @endif

                            </td>
                            <td class="bold">Termin płatności</td>
                        </tr>



                            @for($i = 1; $i <= $e->installments; $i++)
                            <tr>

                                @if($e->installments > 1)

                                    <td>Rata {{ $i }}</td>
                                    <td>{{ bp_currency($e->stage_one_fee/$e->installments) }}</td>

                                @else

                                    <td>Opłata wstępna</td>
                                    <td>{{ bp_currency($e->stage_one_fee) }}</td>

                                @endif

                                <td>{{ bp_human_date(date("Y-m-d", strtotime("+".($i-1)." month", strtotime($e->first_installment_date))), 'dot') }}</td>
                            </tr>
                            @endfor


                        @if($e->stage_two_fee > 0)

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
                                <td>30 dni od terminu płatności I raty</td>
                            </tr>

                        @endif


                        @if($e->hearing_fee > 0)

                            <tr>
                                <td class="bold">Opłata za rozprawę</td>
                                <td>{{ bp_currency($e->hearing_fee) }}</td>
                                <td>najpóźniej w dniu rozprawy</td>
                            </tr>

                        @endif

                        <tr>
                            <td class="bold" colspan="2">Koszty zastępstwa procesowego</td>
                            <td>30 dni od uprawomocnienia się orzeczenia lub zawarcia ugody</td>
                        </tr>
                        @if($e->is_bonus)
                        <tr>
                            <td class="bold" colspan="2">Premia w razie wygranej</td>
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

<!-- PEŁNOMOCNICTWO -->

<div style="page-break-after: always">

    <h1 style="text-align: center; margin-bottom: 0">Pełnomocnictwo</h1>

    <p style="margin-bottom: 10px; margin-top: 0; text-align: center">udzielone w dniu {{ bp_human_date($e->date, 'dot') }} r.
        @if($miejsce_podpisania == 'Głogów')w Głogowie
        @elseif($miejsce_podpisania == 'Legnica')w Legnicy
        @elseif($miejsce_podpisania == 'Leszno')w Lesznie
        @elseif($miejsce_podpisania == 'Wrocław')we Wrocławiu
        @elseif($miejsce_podpisania == 'Zielona Góra')w Zielonej Górze
        @endif
        (woj.
        @if($miejsce_podpisania == 'Głogów' || $miejsce_podpisania == 'Legnica' || $miejsce_podpisania == 'Wrocław')dolnośląskie
        @elseif($miejsce_podpisania == 'Zielona Góra')lubuskie
        @elseif($miejsce_podpisania == 'Leszno')wielkopolskie
        @endif
        , Polska)</p>

    <div style="margin-left: 0px">
        @if($kredytobiorcy)

            <div class="bold">Mocodawc{{ $kredytobiorcy->count() === 1 ? 'a' : 'y' }}</div>
            @foreach($kredytobiorcy as $klient)
                {{ $klient->label }}, PESEL: {{ $klient->pesel  }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK MOCODAWCÓW!</span>
        @endif

        @if($e->credits)
            <div class="mt-5 bold">
                {{ $e->credits->count() > 1 ? 'Umowy kredytowe' : 'Umowa kredytowa' }}
            </div>
            @foreach($e->credits as $umowa)
                Nr: {{ $umowa->number }} z dnia {{ bp_human_date($umowa->date, 'dot') }} r. zawarta z: {{ $umowa->former_banks->organization }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK UMÓW!</span>
        @endif
    </div>

    <dl class="mt-5">
        <dt>1.</dt>
        <dd>
            {{ $kredytobiorcy->count() === 1 ? 'Mocodawca upoważnia' : 'Mocodawcy upoważniają' }}
            @if(false)
            __________________________________________________
            @else
            {{ $korporacja }} {{  $pelnomocnik }}
            {{-- adwokat Wiktorię Rajzynger --}}
            @endif z kancelarii PRĘDA Kancelaria Adwokacka do dochodzenia w {{ $kredytobiorcy->count() === 1 ? 'jego' : 'ich' }} imieniu wszelkich roszczeń związanych z {{ $e->credits->count() === 1 ? 'opisaną wyżej umową kredytową' : 'każdą z opisanych wyżej umów kredytowych' }}, w szczególności roszczenia o ustalenie nieważności umowy, o ustalenie bezskuteczności poszczególnych postanowień umowy, ustalenie braku obowiązku zapłaty wynagrodzenia za korzystanie z kapitału i tym podobnych roszczeń, roszczeń o zapłatę, w tym również wynikających ze stwierdzenia nieważności umowy, a także do prowadzenia postępowania o wykreślenie hipoteki zabezpieczającej wierzytelności wynikające z {{ $e->credits->count() === 1 ? 'opisanej wyżej umowy kredytowej' : 'każdej z opisanych wyżej umów kredytowych' }}.
        </dd>
    </dl>
    <dl class="mt-5">
        <dt>2.</dt>
        <dd>
            Niniejsze pełnomocnictwo upoważnia do wszelkich czynności procesowych, w tym do reprezentacji przed sądem I i II instancji oraz przed Sądem Najwyższym, do reprezentowania w postępowaniu egzekucyjnym, a nadto do wszelkich czynności pozaprocesowych i polubownych, odbioru świadczeń, wskazania numeru rachunku bankowego, na które świadczenia mają być przelane, odbioru wszelkiej korespondencji w sprawach dotyczących przedmiotu pełnomocnictwa, odbioru dokumentacji związanej z umową kredytu o numerze wyżej wskazanym od banku oraz udzielania dalszych pełnomocnictw.
        </dd>
    </dl>
    <dl class="mt-5" style="margin-bottom: 70px">
        <dt>3.</dt>
        <dd>
            {{ $kredytobiorcy->count() === 1 ? 'Mocodawca' : 'Mocodawcy' }} – zgodnie z art. 104 ust. 3 Ustawy z dnia 29.09.1997 r. Prawo Bankowe (Dz. U. 2016.1988) – {{ $kredytobiorcy->count() === 1 ? 'upoważnia' : 'upoważniają' }} {{ $e->credits[0]->current_banks->organization }} do ujawnienia i przekazywania Pełnomocnikowi wszelkich dokumentów i informacji objętych tajemnicą bankową dotyczących {{ $e->credits->count() === 1 ? 'opisanej na wstępie umowy kredytowej' : 'opisanych na wstępie umów kredytowych' }}, niezbędnych do wykonania niniejszego pełnomocnictwa, w tym zwłaszcza do dochodzenia roszczeń.
        </dd>
    </dl>

    @if($kredytobiorcy)

        @foreach($kredytobiorcy->sortBy('sort') as $klient)
            <div style="width: 40%; margin: 0 25px; float: left; border-top: 1px solid #000;" class="bold">
                <small>{{ $klient->label }}</small>
            </div>
            @if($loop->iteration % 2 == 0)<br><br><br><br>@endif
        @endforeach

    @endif


</div>


<!-- OŚWIADCZENIA -->

<div  style="page-break-after: always;">

    <p style="margin-bottom: 10px; margin-top: 0; text-align: right">{{  $miejsce_podpisania }}, {{ bp_human_date($e->date, 'dot') }} r.</p>


    <div class="mt-5" style="margin-left: 0px">
        @if($kredytobiorcy)

            <div class="bold">Kredytobiorc{{ $kredytobiorcy->count() === 1 ? 'a' : 'y' }}</div>
            @foreach($kredytobiorcy->sortBy('sort') as $klient)
                {{ $klient->label }}, PESEL: {{ $klient->pesel  }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK MOCODAWCÓW!</span>
        @endif

        @if($e->credits)
            <div class="mt-5 bold">
                {{ $e->credits->count() > 1 ? 'Umowy kredytowe' : 'Umowa kredytowa' }}
            </div>
            @foreach($e->credits as $umowa)
                Nr: {{ $umowa->number }} z dnia {{ bp_human_date($umowa->date, 'dot') }} r. zawarta z: {{ $umowa->former_banks->organization }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK UMÓW!</span>
        @endif
    </div>

    <h1 style="text-align: center">
        Oświadczenie o świadomości skutków nieważności umowy
    </h1>

        <p>Oświadczam, że w pełni rozumiem i akceptuję wszelkie konsekwencje nieważności wskazanej wyżej umowy kredytowej (dalej: „Umowa kredytowa”, przy czym przez pojęcie to należy rozumieć także ewentualne aneksy do umowy).	W szczególności mam świadomość, że:</p>

        <dl class="mt-5">
            <dt>1.</dt>
            <dd>
                w Umowie kredytowej znajdują się klauzule abuzywne w rozumieniu art. 385<sup>1</sup> § 1 k.c., bez których Umowa kredytowa nie mogłaby być wykonywana, co pociąga za sobą jej nieważność. Oznacza to, że Umowa kredytowa traktowana powinna być tak, jakby nigdy nie została zawarta i nie wywołuje skutków od samego początku;
            </dd>
            <dt>2.</dt>
            <dd>
                skutkiem nieważności Umowy kredytowej jest to, że strony tej umowy mają obowiązek zwrócić sobie to, co na podstawie umowy świadczyły;
            </dd>
            <dt>3.</dt>
            <dd>
                bank ma obowiązek zwrotu zapłaconych rat oraz innych opłat wynikających z Umowy kredytowej, a ja – jako kredytobiorca – mam obowiązek zwrotu na rzecz banku udostępnionego mi kapitału kredytu;
            </dd>
            <dt>4.</dt>
            <dd>
                rozliczenie stron nieważnej Umowy kredytowej może nastąpić poprzez potrącenie wierzytelności i rozliczenie różnicy;
            </dd>
            <dt>5.</dt>
            <dd>
                zgodnie z aktualnym stanowiskiem Sądu Najwyższego bank może żądać zwrotu swojego świadczenia od momentu, w którym kredytobiorca zakwestionował względem banku związanie postanowieniami umowy;
            </dd>
            <dt>6.</dt>
            <dd>
                bank może wystąpić przeciwko mnie o zwrot całego wypłaconego kapitału kredytu;
            </dd>
            <dt>7.</dt>
            <dd>
                bank może wystąpić przeciwko mnie z roszczeniem przekraczającym kwotę udostępnionego mi kapitału, w szczególności z roszczeniem o tzw. „wynagrodzenie za korzystanie z kapitału”, z roszczeniem o waloryzację kapitału, itp.;
            </dd>
            <dt>8.</dt>
            <dd>
                Trybunał Sprawiedliwości Unii Europejskiej w wyroku z 15.06.2023 r., wydanym w sprawie C-520/21, orzekł iż żądanie przez instytucję kredytową od konsumenta rekompensaty wykraczającej poza zwrot kapitału wypłaconego z tytułu wykonania tej umowy oraz poza zapłatę ustawowych odsetek za zwłokę od dnia wezwania do zapłaty, jest sprzeczne z przepisami dyrektywy 93/13;
            </dd>
            <dt>9.</dt>
            <dd>
                mogę zapobiec nieważności Umowy kredytowej i jej skutkom poprzez wyrażenie zgody na stosowanie zawartych w niej niedozwolonych postanowień już od momentu zawarcia umowy (w takim przypadku Umowa kredytowa wiązałaby mnie nadal, zgodnie z jej pierwotnym brzmieniem).
            </dd>
        </dl>
        <p style="margin-bottom: 70px">
            Mając świadomość wszystkich wskazanych wyżej kwestii, stanowczo odmawiam potwierdzenia wszelkich klauzul niedozwolonych zawartych w Umowie kredytowej. Oświadczam, że nie godzę się na ich stosowanie i związanie Umową kredytową. Akceptuję wszystkie skutki nieważności umowy. Moją wolą jest, by Sąd ustalił, że Umowa kredytowa jest nieważna.
        </p>





    @if($kredytobiorcy)

        @foreach($kredytobiorcy->sortBy('sort') as $klient)
            <div style="width: 40%; margin: 0 25px; float: left; border-top: 1px solid #000;" class="bold">
                <small>{{ $klient->label }}</small>
            </div>
            @if($loop->iteration % 2 == 0)<br><br><br><br>@endif
        @endforeach

    @endif


</div>


<!-- RODO -->

@include('print.zlecenia.elements.rodo')


</div>
