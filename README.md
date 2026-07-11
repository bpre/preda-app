# preda-app

Nowa, scalana aplikacja PREDA oparta na Laravelu i Filamencie.

Docelowo aplikacja rozdziela publiczna strone, CMS, CRM, panel pracy kancelarii oraz portal klienta na osobne panele/subdomeny, przy wspolnych danych tam, gdzie ma to sens.

## Lokalne domeny

Domyslna konfiguracja lokalna uzywa portu `8010` i domen:

- `preda-app.test`
- `ewidencja.preda-app.test`
- `crm.preda-app.test`
- `cms.preda-app.test`
- `portal.preda-app.test`

Dodaj wpis do `/etc/hosts`:

```sh
sudo sh -c 'printf "\n# preda-app local domains\n127.0.0.1 preda-app.test ewidencja.preda-app.test crm.preda-app.test cms.preda-app.test portal.preda-app.test\n" >> /etc/hosts'
```

## Uruchomienie

```sh
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8010
```

Adresy lokalne:

- strona publiczna: `http://preda-app.test:8010`
- kancelaria: `http://ewidencja.preda-app.test:8010/login`
- CRM: `http://crm.preda-app.test:8010/login`
- CMS: `http://cms.preda-app.test:8010/login`
- portal klienta: `http://portal.preda-app.test:8010/login`

## Panele

- `kancelaria` - panel operacyjnej pracy kancelarii.
- `crm` - panel pozyskania klienta i obslugi leadow.
- `cms` - panel zarzadzania strona publiczna.
- `portal` - panel klienta, z osobnym modelem logowania `PortalUser` i tabela `portal_users`.

Uzytkownicy pracowniczy korzystaja z modelu `User`. Klienci portalu korzystaja z osobnego modelu `PortalUser`, osobnego guarda `portal` i osobnego brokera resetowania hasel.

## Testy

```sh
php artisan test
```
