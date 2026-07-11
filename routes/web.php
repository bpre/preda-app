<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('preda.domains.public'))
    ->group(base_path('routes/public.php'));

Route::domain(config('preda.domains.kancelaria'))
    ->group(base_path('routes/kancelaria.php'));

Route::domain(config('preda.domains.portal'))
    ->group(base_path('routes/portal.php'));
