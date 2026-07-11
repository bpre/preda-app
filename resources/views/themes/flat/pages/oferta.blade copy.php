<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :content="$content"
        :alternate="true"
        :full="true"
        :extraMarginTop="true"
    >

        <div class="grid max-w-md grid-cols-1 mx-auto my-12 prose isolate gap-y-8 lg:mx-0 lg:max-w-none lg:grid-cols-3 prose-slate">

            <div class="flex flex-col justify-between rounded-[1.5rem] border border-primary-200 bg-white p-8 pt-0 xl:pt-0 lg:mt-8 lg:rounded-r-none xl:p-10">
                <div>

                    <div class="h-6"></div>

                    <h3 class="mt-4 text-center text-2xl font-semibold leading-8 text-primary-900">Wariant ekonomiczny</h3>

                    <p class="mt-4 text-sm leading-6 text-center">Najniższa opłata wstępna, najwyższa premia dla kancelarii w razie wygranej.</p>

                    <div class="mt-6 text-center">
                        <div class="leading-6">Opłata wstępna: </div>
                        <div class="text-4xl font-bold tracking-tight text-primary-900">2.800 zł</div>
                    </div>

                    <ul role="list" class="pl-0 mt-8 space-y-3 leading-6">

                        <li class="flex gap-x-3">
                            <svg class="flex-none w-5 h-6 text-accent-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            opłata wstępna płatna w 4 miesięcznych ratach po 700 zł
                        </li>
                        <li class="flex gap-x-3">
                            <svg class="flex-none w-5 h-6 text-accent-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            premia za wygranie sprawy - ustalana indywidualnie
                        </li>
                    </ul>

                </div>

            </div>

            <div class="flex flex-col justify-between rounded-[1.5rem] border border-primary-200 bg-white p-8 pt-0 xl:pt-0 lg:mt-8 lg:border-x-0 lg:rounded-b-none lg:rounded-t-none xl:p-10">
                <div>

                    <div class="h-6"></div>

                    <h3 class="mt-4 text-center text-2xl font-semibold leading-8 text-primary-900">Wariant optymalny</h3>

                    <p class="mt-4 text-sm leading-6 text-center">Średnia opłata wstępna, średnia premia dla kancelarii w razie wygranej.</p>

                    <div class="mt-6 text-center">
                        <div class="leading-6">Opłata wstępna: </div>
                        <div class="text-4xl font-bold tracking-tight text-primary-900">6.800 zł</div>
                    </div>

                    <ul role="list" class="pl-0 mt-8 space-y-3 leading-6">

                        <li class="flex gap-x-3">
                            <svg class="flex-none w-5 h-6 text-accent-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            opłata wstępna płatna w 4 miesięcznych ratach po 1.700 zł
                        </li>
                        <li class="flex gap-x-3">
                            <svg class="flex-none w-5 h-6 text-accent-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            premia za wygranie sprawy - ustalana indywidualnie
                        </li>
                    </ul>

                </div>

            </div>

            <div class="flex flex-col justify-between rounded-[1.5rem] border border-primary-200 bg-white p-8 pt-6 xl:pt-8 lg:z-10 lg:rounded-bl-none xl:p-10">
                <div>

                    <div class="m-0 h-6 content-center text-center 2xl:order-2">
                        <span class="rounded-full bg-accent-600/10 px-2.5 py-1 text-xs font-semibold leading-5 text-accent-600">
                            Najczęściej wybierany
                        </span>
                    </div>

                    <h3 class="mt-4 text-center text-2xl font-semibold leading-8 text-primary-900">Wariant premium</h3>


                    <p class="mt-4 text-sm leading-6 text-center">Najwyższa opłata wstępna, brak premia dla kancelarii w razie wygranej.</p>

                    <div class="mt-6 text-center">
                        <div class="leading-6">Opłata wstępna: </div>
                        <div class="text-4xl font-bold tracking-tight text-primary-900">10.800 zł</div>
                    </div>

                    <ul role="list" class="pl-0 mt-8 space-y-3 leading-6">

                        <li class="flex gap-x-3">
                            <svg class="flex-none w-5 h-6 text-accent-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            opłata wstępna płatna w 4 miesięcznych ratach po 2.700 zł
                        </li>
                        <li class="flex font-bold gap-x-3">
                            <svg class="flex-none w-5 h-6 text-accent-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            brak premii za wygranie sprawy
                        </li>
                    </ul>

                </div>
            </div>

        </div>

        <div class="prose prose-slate mx-auto">
            {!! mos($content) !!}
        </div>

    </x-section::frame>



    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
