<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\Website\PracticeContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class RememberWebsitePracticeContext
{
    public function handle(Request $request, Closure $next): Response
    {
        PracticeContext::abortIfInactiveForRequest($request);
        PracticeContext::rememberForRequest($request);
        View::share('practiceContext', PracticeContext::current($request));

        return $next($request);
    }
}
