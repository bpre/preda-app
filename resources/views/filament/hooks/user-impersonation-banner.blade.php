@php
    use App\Services\UserImpersonationService;
    use Filament\Facades\Filament;
    use Illuminate\Support\Facades\Route;

    $impersonation = app(UserImpersonationService::class);
    $state = $impersonation->state();
    $panelId = Filament::getCurrentPanel()?->getId();
    $routeName = $panelId ? "impersonation.stop.{$panelId}" : null;
@endphp

@if ($state && in_array($panelId, ['kancelaria', 'crm', 'cms'], true) && $routeName && Route::has($routeName))
    <div
        class="bg-red-200 text-red-950 shadow-sm"
        style="background-color: rgb(254 202 202); width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); box-sizing: border-box; padding: 0.5rem 1rem;"
    >
        <div style="display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 1rem; font-size: 0.875rem; line-height: 1.25rem;">
            <div class="min-w-0" style="min-width: 0; flex: 1 1 auto;">
                <span class="font-semibold">Tryb diagnostyczny:</span>
                działasz jako
                <span class="font-semibold">{{ $state['impersonated_user_name'] ?? 'użytkownik' }}</span>.
                Konto administratora:
                <span class="font-semibold">{{ $state['impersonator_name'] ?? 'administrator' }}</span>.
            </div>

            <form method="POST" action="{{ route($routeName) }}" class="shrink-0" style="margin-left: auto; flex: 0 0 auto;">
                @csrf

                <button
                    type="submit"
                    class="font-semibold underline decoration-red-900/60 underline-offset-4 transition hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2 focus:ring-offset-red-300"
                    style="white-space: nowrap;"
                >
                    wyłącz tryb diagnostyczny
                </button>
            </form>
        </div>
    </div>
@endif
