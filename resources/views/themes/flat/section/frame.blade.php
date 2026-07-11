@php
    $hasImage = filled($image) && is_file(public_path('images/' . $image));
@endphp

<div {{ $attributes->class([
        'bg-secondary-50 hexagons' => $alternate,
        'bg-white' => !$alternate,
        'pt-24' => $extraMarginTop,
        'content-center'
    ]) }}>



    <div
        class="relative content-center mx-auto overflow-hidden isolate {{ ($heading =="" || $subheading =="") ? '' : 'py-12 lg:py-24' }}"
    >
        <div class="container relative mx-auto">

            @if($heading !="" && $subheading !="")
            <div class="max-w-5xl">
                <div class="{{ $full ? 'lg:col-span-6' : '' }}">

                        @if($displaySubheadingFirst == true)

                            <{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}
                                class="ui-heading-eyebrow"
                            >
                                {!! pipe2br($subheading) !!}
                                @if(request()->query('page') && request()->query('page') != '1')
                                <span class="font-normal text-secondary-400">
                                    - strona {{ request()->query('page') }}
                                </span>
                                @endif
                            </{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}>

                        @endif

                        <{{ $useH1 ? ($subheadingIsPrimary ? 'h2' : 'h1') : ($subheadingIsPrimary ? 'h3' : 'h2') }}
                        class="ui-heading-section {{ $useH1 ? 'ui-heading-section--page' : 'ui-heading-section--section' }}"
                        >
                        {!! pipe2br(mos($heading)) !!}
                        </{{ $useH1 ? ($subheadingIsPrimary ? 'h2' : 'h1') : ($subheadingIsPrimary ? 'h3' : 'h2') }}>

                        @if($displaySubheadingFirst == false)

                            <{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}
                                class="ui-heading-eyebrow"
                            >
                                {!! mos($subheading) !!}
                                @if(isset($page) && $page != '1')
                                <span class="font-normal text-secondary-400">
                                    - strona {{ $page }}
                                </span>
                                @endif
                            </{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}>

                        @endif

                </div>
            </div>
            @endif



            <div class="{{ $useH1 || ($heading =="" || $subheading =="") ? 'mt-6' : 'mt-12 lg:mt-24' }} {{ $full ? '' : 'lg:grid lg:grid-cols-2' }} lg:gap-16">

                @if($hasImage)
                    <div class="order-2 mt-4 md:mt-0">

                        <!-- image -->

                            <div class="relative {{ $right ? '' : 'lg:row-start-1 lg:col-start-2' }}">

                                <div class="relative mx-auto text-base lg:max-w-none">
                                @unless($full)
                                    <figure class="duration-500 animate-in fade-in zoom-in">
                                    <div class="aspect-w-12 aspect-h-7 lg:aspect-none">
                                        <img
                                            class="object-cover object-center rounded-lg shadow-lg {{ $imageClass }}"
                                            src="/images/{{ $image }}"
                                            alt="{{ isset($figcaption) ? $figcaption : 'Obrazek' }}"
                                            loading="lazy"
                                        >
                                    </div>
                                    @if($figcaption != '')
                                    <figcaption class="flex mt-3 text-sm text-secondary-500">
                                        <!-- Heroicon name: mini/camera -->
                                        <svg class="flex-none w-5 h-5 text-secondary-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M1 8a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 018.07 3h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0016.07 6H17a2 2 0 012 2v7a2 2 0 01-2 2H3a2 2 0 01-2-2V8zm13.5 3a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM10 14a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-2">{{ $figcaption }}</span>
                                    </figcaption>
                                    @endif
                                    </figure>
                                @endif
                                </div>
                            </div>

                        <!-- image -->

                    </div>
                @endif

                <div class="relative mt-4 lg:mt-0 {{ $right ? 'lg:row-start-1 lg:col-start-2' : '' }} animate-in fade-in zoom-in duration-500 order-1">

                    {{ $slot }}

                </div>
            </div>

        </div>

    </div>
  </div>
