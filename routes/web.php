<?php

use Illuminate\Support\Facades\Route;

Route::domain(config('preda.domains.public'))
    ->group(base_path('routes/public.php'));
