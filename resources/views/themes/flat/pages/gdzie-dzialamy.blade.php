<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :displaySubheadingFirst="true"
        :content="$content"
        :alternate="true"
        :full="true"
        :extraMarginTop="true"
    >



    </x-section::frame>

    <x-section::frame
        :full="true"
    >

        <div class="prose prose-slate py-12">
            {!! $content !!}
        </div>

        @if(false)

        @foreach($provinces as $province)

                <h3 class="text-xl font-semibold">{{ $province->getProvince() }}</h3>

                <div class="my-6">

                    @foreach($cities as $city)

                        @if($city->province == $province)


                        <div class="ml-4">

                            <h4>
                                {{  $city->city }}
                            </h4>

                            <ul>
                                <li>
                                    <a href="{{ url('kredyty-frankowe-' . $city->slug) }}">Kredyty frankowe {{ $city->city }}</a>
                                </li>
                                <li>
                                    <a href="{{ url('kredyt-euro-kancelaria-' . $city->slug) }}">Kredyt w euro {{ $city->city }}</a><br>
                                </li>
                            </ul>

                        </div>



                        @endif
                    @endforeach
                </div>


        @endforeach

        @endif

        @php($firstProvince = collect($provinces)->first())

        <div class="space-y-4 max-w-3xl" x-data="{ openItem: @js($firstProvince?->getProvinceId()) }">
            @foreach($provinces as $province)

                <div class="ui-accordion-card">
                    <button
                        class="ui-accordion-button"
                        @click="openItem = openItem === {{ $province->getProvinceId() }} ? null : {{ $province->getProvinceId() }}"
                        :aria-expanded="openItem === {{ $province->getProvinceId() }}"
                    >
                        <span class="ui-accordion-title">
                            {{ $province->getProvince() }}
                        </span>
                        <svg
                            class="ui-accordion-icon"
                            :class="{ 'rotate-180': openItem === {{ $province->getProvinceId() }} }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        :aria-hidden="openItem !== {{ $province->getProvinceId() }}"
                        :inert="openItem !== {{ $province->getProvinceId() }}"
                        :class="{ 'ui-accordion-panel--open': openItem === {{ $province->getProvinceId() }} }"
                        class="ui-accordion-panel"
                    >
                        <div class="ui-accordion-body prose prose-slate">


                            @foreach($cities as $city)

                                @if($city->province == $province)

                                <div class="ml-4">

                                    <h4>
                                        {{  $city->city }}
                                    </h4>

                                    <ul>
                                        <li>
                                            <a class="font-normal" href="{{ url('kredyty-frankowe-' . $city->slug) }}">Kredyty frankowe {{ $city->city }}</a>
                                        </li>
                                        <li>
                                            <a class="font-normal" href="{{ url('kredyt-euro-kancelaria-' . $city->slug) }}">Kredyt w euro {{ $city->city }}</a><br>
                                        </li>
                                    </ul>

                                </div>



                                @endif
                            @endforeach

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
