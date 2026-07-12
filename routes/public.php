<?php

use App\Http\Controllers\Website\Admin\GoogleBusinessProfileCallbackController;
use App\Http\Controllers\Website\Admin\GoogleBusinessProfileConnectController;
use App\Http\Controllers\Website\Pages\AnalizaController;
use App\Http\Controllers\Website\Pages\BankController;
use App\Http\Controllers\Website\Pages\BlogController;
use App\Http\Controllers\Website\Pages\CityCHFController;
use App\Http\Controllers\Website\Pages\CityEURController;
use App\Http\Controllers\Website\Pages\FAQController;
use App\Http\Controllers\Website\Pages\GdzieDzialamyController;
use App\Http\Controllers\Website\Pages\HomepageController;
use App\Http\Controllers\Website\Pages\KancelariaController;
use App\Http\Controllers\Website\Pages\KancelariaOfficeController;
use App\Http\Controllers\Website\Pages\KlauzuleNiedozwoloneController;
use App\Http\Controllers\Website\Pages\KontaktController;
use App\Http\Controllers\Website\Pages\KredytyEuroController;
use App\Http\Controllers\Website\Pages\KredytyFrankoweController;
use App\Http\Controllers\Website\Pages\MapaStronyController;
use App\Http\Controllers\Website\Pages\OpinieController;
use App\Http\Controllers\Website\Pages\OrzecznictwoController;
use App\Http\Controllers\Website\Pages\PodzialMajatkuController;
use App\Http\Controllers\Website\Pages\PolitykaPrywatnosciController;
use App\Http\Controllers\Website\Pages\PostController;
use App\Http\Controllers\Website\Pages\RozwodController;
use App\Http\Controllers\Website\Pages\SplaconyKredytController;
use App\Http\Controllers\Website\Pages\WyrokController;
use App\Http\Controllers\Website\Pages\WyrokiController;
use App\Http\Controllers\Website\Pages\ZawieszenieRatController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomepageController::class)->name('homepage');
Route::get('/wyroki', WyrokiController::class)->name('wyroki');
Route::redirect('/wyroki/euro', '/wyroki/kredyty-euro', 301);
Route::get('/wyroki/kredyty-euro', WyrokiController::class)->defaults('category', 'kredyty-euro')->name('wyroki-kredyty-euro');
Route::get('/wyroki/kredyty-frankowe', WyrokiController::class)->defaults('category', 'kredyty-frankowe')->name('wyroki-kredyty-frankowe');
Route::get('/wyroki/{category}', WyrokiController::class)->name('wyroki-splacone');
Route::get('/wyroki/{category}/{slug}', WyrokiController::class)->where('category', 'sad|bank|sedzia')->name('wyroki-kategoria');
Route::get('/wyrok/{slug}', WyrokController::class)->name('wyrok');
Route::get('/zawieszenie-rat', ZawieszenieRatController::class)->name('zawieszenie-rat');
Route::get('/kancelaria', KancelariaController::class)->name('kancelaria');
Route::get('/kontakt', KontaktController::class)->name('kontakt');
Route::get('/analiza', AnalizaController::class)->name('analiza');
Route::get('/faq', FAQController::class)->name('faq');
Route::get('/blog', BlogController::class)->name('blog');
Route::get('/orzecznictwo', OrzecznictwoController::class)->name('orzecznictwo');
Route::get('/blog/{slug}', PostController::class)->name('post');
Route::get('/orzecznictwo/{slug}', PostController::class)->name('orzeczenie');
Route::get('/polityka-prywatnosci', PolitykaPrywatnosciController::class)->name('polityka-prywatnosci');
Route::get('/gdzie-dzialamy', GdzieDzialamyController::class)->name('gdzie-dzialamy');
Route::get('/opinie', OpinieController::class)->name('opinie');
Route::get('/bank/{slug}', BankController::class)->name('bank');
Route::get('/mapa-strony', MapaStronyController::class)->name('mapa-strony');
Route::get('/klauzule-niedozwolone', KlauzuleNiedozwoloneController::class)->name('klauzule-niedozwolone');
Route::get('/kredyty-frankowe', KredytyFrankoweController::class)->name('kredyty-frankowe');
Route::get('/kredyty-euro', KredytyEuroController::class)->name('kredyty-euro');
Route::get('/rozwod', RozwodController::class)->name('rozwod');
Route::get('/podzial-majatku', PodzialMajatkuController::class)->name('podzial-majatku');
Route::get('/splacony-kredyt-frankowy', SplaconyKredytController::class)->name('splacony-kredyt');

Route::get('/kredyty-frankowe-{slug}', CityCHFController::class)->name('city-chf');
Route::get('/kredyt-euro-kancelaria-{slug}', CityEURController::class)->name('city-euro');

$oddzialy = [
    'glogow',
    'zielona-gora',
    // 'legnica',
    // 'leszno',
    // 'wroclaw'
];

foreach ($oddzialy as $oddzial) {
    Route::get("/kancelaria/$oddzial", KancelariaOfficeController::class)
        ->name($oddzial);
}

/* Przekierowania */

Route::get('/konsultacje', function () {
    return redirect('https://calendar.app.google/8wZMGof5vFbqMhND8', 301); // 301 = stałe przekierowanie
});
Route::get('/konsultacje/wiktoria-rajzynger', function () {
    return redirect('https://calendar.app.google/CduSKq9VRVB6yG9c7', 301); // 301 = stałe przekierowanie
});

Route::get('/klauzule-niedozwolone/{nazwa_banku}', function ($nazwa_banku) {
    return redirect("/bank/{$nazwa_banku}", 301); // 301 = stałe przekierowanie
});

$pages = [
    'uniewaznienie-umowy',
    'zwrot-splacony-kredyt',
    'ugoda-z-bankiem',
];

foreach ($pages as $page) {
    Route::get($page, function () use ($page) {
        return redirect("/blog/{$page}", 301);
    });
}

Route::prefix('website/integrations/google-business-profile')->name('website.integrations.google-business-profile.')->group(function () {
    Route::get('/connect', GoogleBusinessProfileConnectController::class)->middleware(['auth'])->name('connect');
    Route::get('/callback', GoogleBusinessProfileCallbackController::class)->name('callback');
});
