<x-theme::app>

<main class="duo-error-page grid min-h-svh place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
  <div class="text-center">
    <p class="text-base font-semibold text-accent-600">401</p>
    <h1 class="mt-4 text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl">Brak autoryzacji.</h1>
    <p class="mt-6 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">Przykro nam, ale musisz się uwierzytelnić, aby uzyskać dostęp do tej strony.</p>
    <div class="mt-10 flex items-center justify-center gap-x-6">

        <x-button.primary-link
            href="{{ route('homepage') }}"
            title="Strona główna"
            class="ml-2"
        >
            <span>Przejdź do strony głównej</span>
        </x-button.primary-link>

        <x-button.primary-link
            muted
            href="{{ route('kontakt') }}"
            title="Kontakt"
        >
            <span>Skontaktuj się z nami</span>
        </x-button.primary-link>

    </div>
  </div>
</main>

</x-theme::app>
