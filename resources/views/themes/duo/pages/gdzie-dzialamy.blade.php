<x-theme::app>

    <x-theme::header />

    @php
        $accordionCardClasses = 'overflow-hidden rounded-[1.25rem] border border-primary-200 bg-white';
        $accordionButtonClasses = 'flex w-full items-center justify-between bg-primary-50 px-6 py-4 text-left transition-colors duration-200 hover:bg-primary-100 focus-visible:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600';
        $accordionTitleClasses = 'text-lg font-semibold text-primary-900';
        $accordionIconClasses = 'h-5 w-5 text-secondary-500 transition-transform duration-200';
        $accordionBodyClasses = 'border-t border-primary-200 px-6 py-4 prose prose-slate';
    @endphp

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :displaySubheadingFirst="true"
        :content="$content"
        :alternate="true"
        :full="true"
    >



    </x-section::frame>

    <x-section::frame
        :full="true"
        class="pb-12"
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

                <div class="{{ $accordionCardClasses }}">
                    <button
                        class="{{ $accordionButtonClasses }}"
                        @click="openItem = openItem === {{ $province->getProvinceId() }} ? null : {{ $province->getProvinceId() }}"
                        :aria-expanded="openItem === {{ $province->getProvinceId() }}"
                    >
                        <span class="{{ $accordionTitleClasses }}">
                            {{ $province->getProvince() }}
                        </span>
                        <svg
                            class="{{ $accordionIconClasses }}"
                            :class="{ 'rotate-180': openItem === {{ $province->getProvinceId() }} }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="openItem === {{ $province->getProvinceId() }}"
                        x-collapse
                        :aria-hidden="openItem !== {{ $province->getProvinceId() }}"
                        :inert="openItem !== {{ $province->getProvinceId() }}"
                        class="overflow-hidden bg-white"
                    >
                        <div class="{{ $accordionBodyClasses }}">


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
