<x-theme::app>

<main class="duo-error-page grid min-h-svh place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
  <div class="text-center">
    <p class="text-base font-semibold text-accent-600">504</p>
    <h1 class="mt-4 text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl">Przekroczono czas oczekiwania.</h1>
    <p class="mt-6 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">Przykro nam, ale serwer zbyt długo przetwarzał żądanie. Spróbuj ponownie za chwilę.</p>
    <div class="mt-10 flex items-center justify-center gap-x-6">

        <x-button.primary-link
            href="{{ route('analiza') }}"
            title="Sprawdź swój kredyt"
            class="ml-2"
        >
            <span>Przejdź do strony głównej</span>
        </x-button.primary-link>

        <x-button.secondary-link
            href="tel:+48666580580"
            title="Zadzwoń: 666 580 580"
        >
            <span>Skontaktuj się z nami</span>
        </x-button.secondary-link>

    </div>
  </div>
</main>

</x-theme::app>
