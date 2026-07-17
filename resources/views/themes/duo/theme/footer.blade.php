@php
    $duoLineClasses = implode(' ', [
        '[--duo-hero-line-color:var(--color-primary-300)]',
        '[--duo-hero-line-width:2px]',
        '[--duo-hero-grid-bleed:1.5rem]',
        '[--duo-hero-grid-start-padding:1.5rem]',
        '[--duo-hero-grid-end-padding:1.5rem]',
        '[--duo-section-lines-y-bleed:3rem]',
        'sm:[--duo-hero-grid-bleed:28px]',
        'sm:[--duo-hero-grid-start-padding:2rem]',
        'sm:[--duo-hero-grid-end-padding:2rem]',
        'lg:[--duo-hero-grid-start-padding:4rem]',
        'lg:[--duo-hero-grid-end-padding:4rem]',
        'lg:[--duo-section-lines-y-bleed:6rem]',
        'xl:[--duo-hero-grid-start-padding:5rem]',
        'xl:[--duo-hero-grid-end-padding:5rem]',
        '2xl:[--duo-hero-grid-start-padding:6rem]',
        '2xl:[--duo-hero-grid-end-padding:6rem]',
    ]);
@endphp

<footer class="duo-footer relative isolate mt-auto bg-gray-800 text-gray-500 {{ $duoLineClasses }}">
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-0 z-0 size-0 border-r-[60px] border-t-[60px] border-r-transparent border-t-rose-700"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -bottom-px right-0 z-0 hidden size-[60px] bg-white [clip-path:polygon(100%_0,100%_100%,0_100%)] xl:block"></div>

    <div class="container relative isolate z-[1] mx-auto pt-12 pb-16 sm:pb-6">

        <div class="grid md:grid-cols-4 gap-8 pt-12">

            <div class="col-span-2">
                <x-preda.logo class="fill-gray-50" color="gray-50" />
                <x-website.element.headquarters class="mt-10 hidden" />
            </div>


            <div class="2xl:text-sm text-xs">
                <div class="text-gray-300 font-semibold">
                    Oddział w Głogowie
                </div>
                <div>
                    ul. Szewska 7<br>67-200 Głogów
                </div>
            </div>


            <div class="2xl:text-sm text-xs">
                <div class="text-gray-300 font-semibold">
                    Oddział w Zielonej Górze
                </div>
                <div>
                    plac Słowiański 15<br>65-001 Zielona Góra
                </div>

            </div>


        </div>

        <div class="pt-24 md:flex md:items-center md:justify-between">
            <div class="flex gap-x-6 md:order-2">
                <a href="https://www.facebook.com/Kancelaria.Preda" target="_blank" class="text-secondary-600 hover:text-blue-600">
                    <span class="sr-only">Facebook</span>
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-6">
                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" fill-rule="evenodd" />
                    </svg>
                </a>
                <a href="https://www.instagram.com/kancelaria.preda/" target="_blank" class="text-secondary-600 hover:text-pink-500">
                    <span class="sr-only">Instagram</span>
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-6">
                        <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" fill-rule="evenodd" />
                    </svg>
                </a>
            </div>
            <div class="mt-8 w-full text-xs/6 md:order-1 md:mt-0">

                <nav class="mb-3 flex items-center gap-x-4 md:hidden" aria-label="Linki prawne">
                    <a href="{{ route('polityka-prywatnosci') }}" class="hover:text-gray-400">Polityka prywatności</a>
                    <a href="{{ route('mapa-strony') }}" class="hover:text-gray-400">Mapa strony</a>
                </nav>

                &copy; {{ date("Y") }} PRĘDA Kancelaria Adwokacka

                <span class="hidden md:inline-block">

                    <span class="mx-2">&middot;</span>
                    <a href="{{ route('polityka-prywatnosci') }}" class="hover:text-gray-400">Polityka prywatności</a>
                    <span class="mx-2">&middot;</span>
                    <a href="{{ route('mapa-strony') }}" class="hover:text-gray-400">Mapa strony</a>

                </span>


            </div>
        </div>
    </div>

</footer>

<div class="hidden h-2 min-[1600px]:block" aria-hidden="true"></div>
