<footer class="mt-auto">
    <div class="mx-auto container pb-16 sm:pb-24 lg:pb-32">

        <div class="xl:grid xl:grid-cols-3 xl:gap-8 xl:border-t xl:border-primary-900/10 xl:pt-12">

            <div class="hidden xl:block">
                <x-preda.logo class="fill-primary-700" color="primary-700" />
                <x-website.element.headquarters class="mt-10" />
            </div>


            <!-- FOOTER NAV (mobile only) -->
            <nav aria-label="Menu w stopce" class="mb-0 w-full text-secondary-700 xl:hidden" x-data="{ active: null, toggle(id){ this.active = this.active === id ? null : id } }">
            <ul class="divide-y divide-primary-200">
                <!-- Sekcja: O nas -->
                <li class="bg-white">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-4 text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                    :aria-expanded="active === 'oddzialy'"
                    :aria-controls="'panel-oddzialy'"
                    @click="toggle('oddzialy')"
                    @keydown.enter.prevent="toggle('oddzialy')"
                    @keydown.space.prevent="toggle('oddzialy')"
                >
                    <span class="font-medium">Oddziały</span>
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-200" :class="{ 'rotate-180' : active === 'oddzialy' }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                </button>
                <div
                    id="panel-oddzialy"
                    x-cloak
                    x-show="active === 'oddzialy'"
                    x-collapse
                    x-transition.opacity.duration.200ms
                    class="px-4 pb-4"
                >
                    <ul class="grid gap-1">
                        @foreach($offices as $office)
                            <li>
                                <a
                                    href="{{ route($office->slug) }}"
                                    class="flex items-center rounded-xl px-3 py-2.5 text-sm/6 text-secondary-600 transition-colors duration-200 hover:bg-secondary-100 hover:text-secondary-900 focus-visible:bg-secondary-100 focus-visible:text-secondary-900"
                                >
                                    {{ $office->city }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                </li>

                <!-- Sekcja: Usługi -->
                <li class="bg-white">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-4 text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                    :aria-expanded="active === 'kancelaria'"
                    :aria-controls="'panel-kancelaria'"
                    @click="toggle('kancelaria')"
                    @keydown.enter.prevent="toggle('kancelaria')"
                    @keydown.space.prevent="toggle('kancelaria')"
                >
                    <span class="font-medium">Kancelaria</span>
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-200" :class="{ 'rotate-180' : active === 'kancelaria' }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                </button>
                <div
                    id="panel-kancelaria"
                    x-cloak
                    x-show="active === 'kancelaria'"
                    x-collapse
                    x-transition.opacity.duration.200ms
                    class="px-4 pb-4"
                >
                    <ul class="grid gap-1">
                        @foreach($kancelaria as $link)
                        <li>
                            <x-link.footer
                                href="{{ route($link[1]) }}"
                                class="flex items-center rounded-xl px-3 py-2.5 transition-colors duration-200 hover:bg-secondary-100 focus-visible:bg-secondary-100"
                            >
                                {{ $link[0] }}
                            </x-link.footer>
                        </li>
                        @endforeach
                    </ul>
                </div>
                </li>

                <!-- Sekcja: oferta -->
                <li class="bg-white">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-4 text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                    :aria-expanded="active === 'oferta'"
                    :aria-controls="'panel-oferta'"
                    @click="toggle('oferta')"
                    @keydown.enter.prevent="toggle('oferta')"
                    @keydown.space.prevent="toggle('oferta')"
                >
                    <span class="font-medium">Oferta</span>
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-200" :class="{ 'rotate-180' : active === 'oferta' }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                </button>
                <div
                    id="panel-oferta"
                    x-cloak
                    x-show="active === 'oferta'"
                    x-collapse
                    x-transition.opacity.duration.200ms
                    class="px-4 pb-4"
                >
                    <ul class="grid gap-1">
                        @foreach($oferta as $link)
                        <li>
                            <x-link.footer
                                href="{{ route($link[1]) }}"
                                class="flex items-center rounded-xl px-3 py-2.5 transition-colors duration-200 hover:bg-secondary-100 focus-visible:bg-secondary-100"
                            >
                                {{ $link[0] }}
                            </x-link.footer>
                        </li>
                        @endforeach
                    </ul>
                </div>
                </li>

                <!-- Sekcja: wiedza -->
                <li class="bg-white">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-4 text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                    :aria-expanded="active === 'wiedza'"
                    :aria-controls="'panel-wiedza'"
                    @click="toggle('wiedza')"
                    @keydown.enter.prevent="toggle('wiedza')"
                    @keydown.space.prevent="toggle('wiedza')"
                >
                    <span class="font-medium">Wiedza</span>
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-200" :class="{ 'rotate-180' : active === 'wiedza' }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                </button>
                <div
                    id="panel-wiedza"
                    x-cloak
                    x-show="active === 'wiedza'"
                    x-collapse
                    x-transition.opacity.duration.200ms
                    class="px-4 pb-4"
                >
                    <ul class="grid gap-1">
                        @foreach($wiedza as $link)
                        <li>
                            <x-link.footer
                                href="{{ route($link[1]) }}"
                                class="flex items-center rounded-xl px-3 py-2.5 transition-colors duration-200 hover:bg-secondary-100 focus-visible:bg-secondary-100"
                            >
                                {{ $link[0] }}
                            </x-link.footer>
                        </li>
                        @endforeach
                    </ul>
                </div>
                </li>
            </ul>
            </nav>

            <div class="mt-16 mb-12 hidden xl:grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">
                <div class="md:grid md:grid-cols-2 md:gap-8">
                    <div>

                        <h3 class="text-sm/6 font-semibold text-primary-950">Oddziały</h3>
                        <ul role="list" class="mt-6 space-y-4">

                        @foreach($offices as $office)
                            <div class="office-item">
                                <li>
                                    <a href="{{ route($office->slug) }}" class="text-sm/6 text-secondary-600 hover:text-secondary-900">{{ $office->city }}</a>
                                </li>
                            </div>
                        @endforeach

                        </ul>

                    </div>
                    <div class="mt-10 md:mt-0">
                        <h3 class="text-sm/6 font-semibold text-primary-950">Kancelaria</h3>
                        <ul role="list" class="mt-6 space-y-4">

                            @foreach($kancelaria as $link)

                            <li>
                                <x-link.footer href="{{ route($link[1]) }}">{{ $link[0] }}</x-link.footer>
                            </li>

                            @endforeach

                        </ul>
                    </div>
                </div>
                <div class="md:grid md:grid-cols-2 md:gap-8">
                    <div>

                        <h3 class="text-sm/6 font-semibold text-primary-950">Oferta</h3>
                        <ul role="list" class="mt-6 space-y-4">

                            @foreach($oferta as $link)

                            <li>
                                <x-link.footer href="{{ route($link[1]) }}">{{ $link[0] }}</x-link.footer>
                            </li>

                            @endforeach

                        </ul>

                    </div>
                    <div class="mt-10 md:mt-0">
                        <h3 class="text-sm/6 font-semibold text-primary-950">Baza wiedzy</h3>
                        <ul role="list" class="mt-6 space-y-4">

                            @foreach($wiedza as $link)

                            <li>
                                <x-link.footer href="{{ route($link[1]) }}">{{ $link[0] }}</x-link.footer>
                            </li>

                            @endforeach

                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="border-t border-primary-900/10 pt-12 md:flex md:items-center md:justify-between">
            <div class="flex gap-x-6 md:order-2">
                <a href="https://www.facebook.com/Kancelaria.Preda" target="_blank" class="text-secondary-600 hover:text-blue-700">
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
            <p class="mt-8 text-sm/6 text-secondary-600 md:order-1 md:mt-0">&copy; {{ date("Y") }} PRĘDA Kancelaria Adwokacka</p>
        </div>
    </div>

</footer>
