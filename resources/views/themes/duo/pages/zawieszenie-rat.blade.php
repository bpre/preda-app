<x-theme::app>

    <x-theme::header />


    <x-section::frame
        :heading="$heading"
        :subheading="$subheading"
        :useH1="true"
        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        :full="true"
        :alternate="true"
    >

        <div class="prose">
            <p>
                Choć sprawy o unieważnienie kredytów powiązanych z walutami obcymi trwają dość długo, to w większości przypadków już na samym początku można uzyskać postanowienie sądu o zawieszeniu spłat rat kredytu.
            </p>
            <p>
                W prowadzonych przez naszą kancelarię sprawach postanowienie wstrzymujące obowiązek spłacania rat uzyskiwaliśmy już kilkadziesiąt razy!
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 mt-12 2xl:grid-cols-2">

        @foreach($securities as $sentence)

            <div class="flex flex-col rounded-md border border-primary-200 bg-white">
                <div class="p-4 md:p-6 flex-1">

                    <div class="flex gap-4 px-0">
                        <x-icon name="heroicon-o-document-text" class="hidden text-accent-600 sm:block sm:h-5 sm:w-5 sm:min-w-5 md:h-6 md:w-6 md:min-w-6 xl:h-7 xl:w-7 xl:min-w-7" />
                        <h3 class="text-pretty text-base leading-6 font-semibold text-primary-700 sm:text-lg sm:leading-7 md:text-base md:leading-6 xl:text-lg xl:leading-7">
                            Zawieszenie rat kredytu {{  mos($sentence->bank_previously['bank']) }} - postanowienie z dnia {{  hd($sentence->sentence_date, 'human', false) }} r.

                        </h3>

                    </div>

                    <div class="mt-4 text-secondary-700 md:mt-6">

                        @if($sentence->files)
                            @if(file_exists('storage/' . $sentence->files[0]))
                                <img
                                    src="storage/{{ $sentence->files[0] }}"
                                    alt="Postanowienie w sprawie {{ $sentence->sign }}"
                                    loading="lazy"
                                    class="m-0 border-b border-secondary-100/20 p-0"
                                />
                            @endif
                        @endif


                        <dl class="grid grid-cols-1 sm:grid-cols-2">

                        <div class="border-t border-secondary-100 py-4 sm:col-span-1 sm:px-0">
                            <dt class="text-sm/6 font-medium text-secondary-900">Sąd</dt>
                            <dd class="md:mt-1 text-sm/6 sm:mt-2">
                                <x-link.text href="{{ url('wyroki/sad/' . $sentence->court['slug']) }}">
                                    {{  $sentence->court['organization'] }}
                                </x-link.text>
                            </dd>
                        </div>

                        <div class="border-t border-secondary-100 py-4 sm:col-span-1 sm:px-0">
                            <dt class="text-sm/6 font-medium text-secondary-900">Sygnatura</dt>
                            <dd class="md:mt-1 text-sm/6 sm:mt-2">
                                {{  $sentence->sign }}
                            </dd>
                        </div>

                        <div class="border-t border-secondary-100 py-4 sm:col-span-1 sm:px-0">
                            <dt class="text-sm/6 font-medium text-secondary-900">Sędzia</dt>
                            <dd class="md:mt-1 text-sm/6 sm:mt-2">
                                <x-link.text href="{{ url('wyroki/sedzia/' . $sentence->judge['slug']) }}">
                                    {{  $sentence->judge['label'] }}
                                </x-link.text>
                            </dd>
                        </div>

                        <div class="border-t border-secondary-100 py-4 sm:col-span-1 sm:px-0">
                            <dt class="text-sm/6 font-medium text-secondary-900">Bank</dt>
                            <dd class="md:mt-1 text-sm/6 sm:mt-2">

                                @if(in_array($sentence->bank['label'], $active_banks))
                                    <x-link.text href="{{ url('wyroki/bank/' . $sentence->bank['slug']) }}">
                                        {{ $sentence->bank['label'] }}
                                    </x-link.text>
                                @else
                                        {{ $sentence->bank['label'] }}
                                @endif


                            </dd>
                        </div>

                        </dl>
                    </div>

                </div>

            </div>

        @endforeach

        </div>

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
