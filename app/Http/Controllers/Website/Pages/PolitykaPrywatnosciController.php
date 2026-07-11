<?php

namespace App\Http\Controllers\Website\Pages;

use App\Models\Website\Page;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Facades\Website\Seo;

class PolitykaPrywatnosciController extends Controller
{
    public function __invoke()
    {

        Seo::title('Polityka prywatności');
        Seo::description('Polityka prywatności strony preda.info. Informacje o plikach cookies i zasadach przetwarzania danych związanych z przeglądaniem strony.');

        return view('pages.polityka-prywatnosci', [
            'h1'=> 'Polityka prywatności',
            'h2' => 'Zobacz, jak przetwarzamy Twoje dane',
            'content' => '

<h2>Administrator danych</h2>

<p>Administratorem danych osobowych (dalej „Administrator”) zbieranych za pośrednictwem strony internetowej preda.info jest PRĘDA Kancelaria Adwokacka - Adwokat Bartosz Pręda z siedzibą w Głogowie (ul. Szewska 7, 67-200 Głogów), NIP: 692 232 17 50, e-mail: kancelaria@preda.info.</p>

<h2>Podstawa prawna przetwarzania danych</h2>

<p>Administrator przetwarza dane zgodnie z obowiązującymi przepisami, w szczególności zgodnie z przepisami Rozporządzenia Parlamentu Europejskiego i Rady (UE) 2016/679 z dnia 27 kwietnia 2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE (dalej „RODO”).</p>

<h2>Zakres przetwarzania danych</h2>

<p>Administrator przetwarza dane w następującym zakresie:</p>

<ul>
<li>zawarcie i wykonanie umowy (zakres danych: imię, nazwisko, adres, numer telefonu, e-mail) – art. 6 ust. 1 lit. b RODO,</li>

<li>nawiązania współpracy (zakres danych: imię, nazwisko, adres, numer telefonu, adres e-mail, inne dane podane przez użytkownika) – art. 6 ust. 1 lit a RODO,</li>

<li>dochodzenia należności (zakres danych: imię, nazwisko, adres, e-mail, inne dane niezbędne do udowodnienia istnienia roszczenia bądź obrony praw) – art. 6 ust. 1 lit. f RODO,</li>

<li>wypełnienia obowiązków prawnych ciążących na Administratorze w związku
z prowadzeniem działalności gospodarczej (zakres danych: wszelkie dane uzyskane od użytkownika) – art. 6 ust. 1 lit. c RODO,</li>

<li>prowadzenia własnych działań marketingowych i promocyjnych (zakres danych: imię, nazwisko, adres, e-mail, numer telefonu) art. 6 ust. 1 lit. f RODO,</li>

<li>przesyłania informacji handlowych drogą elektroniczną zgodnie z art. 10 ust. 2 ustawy o świadczeniu usług drogą elektroniczną z dnia 18 lipca 2002 r. (Dz. U. z 2017 r., poz. 1219 ze zm.), w tym przesyłanie newslettera (zakres danych: imię, nazwisko, adres, numer telefonu, e-mail) – art. 6 ust. 1 lit. a RODO,</li>

<li>używanie telekomunikacyjnych urządzeń końcowych i automatycznych systemów wywołujących dla celów marketingu bezpośredniego zgodnie z art. 172 ustawy w z dnia 16 lipca 2004 r. Prawo telekomunikacyjne (Dz. U. z 2017 r. poz.1907 ze zm.).</li>

</ul>

<h2>Sposób zbierania danych</h2>

<p>Administrator zbiera lub może zbierać dane osobowe za pośrednictwem formularzy dostępnych na stronie internetowej lub podane przez użytkownika podczas bezpośredniego kontaktu (osobistego, mailowego, telefonicznego): dane identyfikacyjne (m.in. imię, nazwisko, data i miejsce urodzenia), dane kontaktowe (numer telefonu, adres, adres e-mail), dane dotyczące zatrudnienia, inne dane przekazane przez użytkownika w trakcie kontaktu z Administratorem.</p>

<p>Przesłanie formularza na stronie internetowej zawierającego dane osobowe wymaga wyrażenia zgody na przetwarzanie danych. Podanie danych osobowych jest dobrowolne. Brak zgody na przetwarzanie danych uniemożliwia przesłanie formularza.</p>

<p>Przeglądanie zawartości strony internetowej nie wymaga podawania danych osobowych innych niż pozyskiwane automatycznie informacje o parametrach połączenia.</p>

<h2>Brak profilowania  danych</h2>

Administrator nie profiluje danych osobowych użytkowników.</p>

<h2>Czas przetwarzania danych osobowych</h2>

<p>Dane osobowe będą przetwarzane przez okres niezbędny do realizacji umów zawartych za pośrednictwem strony, w tym także po ich wykonaniu z uwagi na możliwość skorzystania przez strony z przysługujących im praw wynikających z umowy, a także ze względu na ewentualne dochodzenie należności. Administrator przechowuje dane osobowe użytkowników również w przypadku, gdy jest to konieczne do wypełnienia ciążących na nim obowiązków prawnych, rozwiązania sporów, wyegzekwowania zobowiązań użytkownika, utrzymywania bezpieczeństwa, zapobiegania oszustwom i nadużyciom. Okres przetwarzania danych w powyższych przypadkach określany jest indywidualnie, nie może przekroczyć jednak 10 lat od momentu realizacji powyższych celów.</p>

<h2>Uprawnienia użytkownika</h2>

<p>Użytkownik ma prawo do:</p>

<ul>

<li>dostępu do treści danych,</li>

<li>sprostowania/zaktualizowania danych,</li>

<li>usunięcia danych,</li>

<li>ograniczenia przetwarzania danych,</li>

<li>przenoszenia danych,</li>

<li>wniesienia sprzeciwu wobec przetwarzania danych,</li>

<li>cofnięcia wyrażonej zgody w dowolnym momencie, przy czym cofnięcie zgody pozostaje bez wpływu na zgodność z prawem przetwarzania, którego dokonano na podstawie zgody przed jej cofnięciem,</li>

<li>wniesienia skargi do organu nadzoru, tj. Prezesa Urzędu Ochrony Danych Osobowych.</li>

</ul>

<h2>Realizacja uprawnień użytkownika</h2>

<p>W celu realizacji uprawnień użytkownika opisanych w niniejszej Polityce prywatności użytkownik powinien wysłać wiadomość do Administratora za pośrednictwem poczty elektronicznej (na adres: kancelaria@preda.info) lub poczty tradycyjnej (na adres: ul. Szewska 7, 67-200 Głogów). Rozpatrzenie zgłoszenia następuje niezwłocznie, nie później jednak niż w terminie miesiąca od momentu otrzymania zgłoszenia, chyba że z uwagi na skomplikowany charakter żądania rozpatrzenie żądania użytkownika w terminie miesiąca nie jest możliwe. W takim wypadku Administrator poinformuje użytkownika o przedłużeniu terminu i wskaże termin rozpatrzenia zgłoszenia, nie dłuższy jednak niż 2 miesiące.</p>

<h2>Udostępnianie informacji</h2>

<p>W celu realizacji umowy Administrator może udostępniać zebrane dane do podmiotów obejmujących: pracowników, współpracowników, firmy kurierskie, operatorów pocztowych, systemów płatności on-line, podmioty świadczących na naszą rzecz usługi IT. W takich sytuacjach ilość przekazywanych danych ograniczona jest do wymaganego minimum. Dane osobowe mogą zostać udostępnione właściwym organom władzy publicznej, jeżeli wymagają tego obowiązujące przepisy prawa.</p>

<h2>Środki techniczne</h2>

<p>Administrator dokłada wszelkich starań, aby zabezpieczyć dane osobowe i ochronić je przed działaniem osób trzecich, a także wykonuje nadzór nad bezpieczeństwem danych przez cały okres ich przetwarzania w sposób zapewniający ochronę przed nieautoryzowanym dostępem osób trzecich, uszkodzeniem, zniekształceniem, zniszczeniem lub utratą.</p>

<h2>Brak przekazywania danych osobowych poza Europejski Obszar Gospodarczy</h2>

<p>Dane osobowe nie są przekazywane do państw spoza Europejski Obszar Gospodarczy. Administrator korzysta z serwerów do przechowywania danych ulokowanych w państwach należących do Europejskiego Obszaru Gospodarczego.</p>

<h2>Pliki Cookies</h2>

<p>Strona internetowa Administratora używa plików cookies. Są to niewielkie pliki tekstowe wysyłane przez serwer www i przechowywane przez oprogramowanie komputera przeglądarki. Kiedy przeglądarka ponownie połączy się ze stroną, strona rozpoznaje rodzaj urządzenia, z którego łączy się użytkownik. Parametry pozwalają na odczytanie informacji w nich zawartych jedynie serwerowi, który je utworzył. Cookies ułatwiają więc korzystanie z wcześniej odwiedzonych stron. Gromadzone informacje dotyczą adresu IP, typu wykorzystywanej przeglądarki, języka, rodzaju systemu operacyjnego, dostawcy usług internetowych, informacji o czasie i dacie, lokalizacji oraz informacji przesyłanych do witryny za pośrednictwem formularza kontaktowego.</p>

<p>Zebrane dane służą do monitorowania i sprawdzenia, w jaki sposób użytkownik korzysta ze strony internetowej, aby usprawniać funkcjonowanie serwisu zapewniając bardziej efektywną i bezproblemową nawigację. Cookies identyfikuje użytkownika, co pozwala na dopasowanie treści strony, z której korzysta, do jego potrzeb. Zapamiętując jego preferencje, umożliwia odpowiednie dopasowanie skierowanych do niego reklam.</p>

<p>Na stronie Administratora wykorzystywane są następujące pliki cookies:</p>

<ul>

<li>„niezbędne” pliki cookies, umożliwiające korzystanie z usług dostępnych w ramach strony, np. uwierzytelniające pliki cookies wykorzystywane do usług wymagających uwierzytelniania w ramach strony; pliki cookies służące do zapewnienia bezpieczeństwa, np. wykorzystywane do wykrywania nadużyć w zakresie uwierzytelniania w ramach strony;</li>

<li>„wydajnościowe” pliki cookies, umożliwiające zbieranie informacji o sposobie korzystania ze stron internetowych serwisu;</li>

<li>„funkcjonalne” pliki cookies, umożliwiające „zapamiętanie” wybranych przez użytkownika ustawień i personalizację interfejsu użytkownika, np. w zakresie wybranego języka lub regionu, z którego pochodzi użytkownik, rozmiaru czcionki, wyglądu strony internetowej itp.;</li>

<li>„reklamowe” pliki cookies, umożliwiające dostarczanie użytkownikom treści reklamowych bardziej dostosowanych do ich zainteresowań.</li>
</ul>

<p>Użytkownik w każdej chwili ma możliwość wyłączenia lub przywrócenia opcji gromadzenia cookies poprzez zmianę ustawień w przeglądarce internetowej.</p>

            '
        ]);
    }
}
