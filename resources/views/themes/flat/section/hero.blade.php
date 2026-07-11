
<div
    x-data="heroParallax()"
    class="ui-hero relative isolate mt-20 overflow-hidden bg-primary-900 sm:mt-24"
>
        <div
            class="absolute inset-x-0 -inset-y-8 -z-10 bg-center bg-cover transform-gpu will-change-transform sm:-inset-y-10 lg:-inset-y-14"
            style="background-image:linear-gradient(135deg, rgba(42, 59, 80, 0.4) 20%, rgba(213, 10, 81, 0.2)), url({{ isset($image) && file_exists('storage/'.$image) ? 'storage/'.$image : '/images/bg12.webp' }})"
            :style="{ transform: `translate3d(0, ${parallaxOffset}px, 0) scale(1.08)` }"
        ></div>

        <div
            class="container relative min-h-[70vh] sm:min-h-[85vh] md:min-h-[650px] justify-center flex flex-col gap-0 space-y-6 sm:space-y-12 animate-in fade-in-0
        duration-700 ease-in-out slide-in-from-right mx-auto"
        >

            <div class="flex flex-col gap-6 sm:gap-10 lg:gap-12">


                @if(isset($subheading) && $displaySubheadingFirst)
                    <{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}
                        class="ui-heading-hero-eyebrow"
                    >
                        {!! pipe2br(mos($subheading)) !!}
                    </{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}>
                @endif

                @if(isset($heading))
                    <{{ $useH1 ? ($subheadingIsPrimary ? 'h2' : 'h1') : ($subheadingIsPrimary ? 'h3' : 'h2') }}
                        class="ui-heading-hero"
                    >
                        {!! pipe2br(mos($heading)) !!}
                    </{{ $useH1 ? ($subheadingIsPrimary ? 'h2' : 'h1') : ($subheadingIsPrimary ? 'h3' : 'h2') }}>
                @endif

                @if(isset($subheading) && !$displaySubheadingFirst)
                    <{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}
                        class="ui-heading-hero-eyebrow max-w-[54rem]"
                    >
                        {!! pipe2br(mos($subheading)) !!}
                    </{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}>
                @endif


                <div class="text-sm leading-6 text-white/80 sm:text-base md:max-w-[50rem] md:text-lg">
                    @if(isset($content))
                        {!! mos(nl2br($content)) !!}
                    @endif
                </div>


                @if($showButtons == true)
                <div class="max-w-sm mt-2 sm:mt-10 sm:max-w-none sm:flex sm:justify-start">
                    <div class="space-y-4 sm:space-y-0 sm:inline-grid sm:grid-cols-2 sm:gap-8">

                    <x-button.primary-link href="{{ route($button_1_route) }}">
                        {{  $button_1_text }}
                    </x-button.primary-link>

                    </div>
                </div>
                @endif

            </div>

        </div>


</div>
