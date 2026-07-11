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

    $heroEyebrowClasses = 'block max-w-7xl text-xl leading-snug font-medium tracking-tight text-accent-600 sm:text-2xl xl:text-3xl';
    $heroHeadingClasses = 'block max-w-full text-3xl font-bold tracking-tight text-primary-700 !leading-none sm:text-4xl md:text-6xl lg:text-6xl';
    $buttonOneOpensAnalysisSidebar = $button_1_route === 'analiza';
@endphp

<div
    class="duo-hero relative isolate !mt-0 grid min-h-[calc(100svh_-_var(--duo-mobile-header-height))] !max-h-none items-center overflow-hidden !rounded-none bg-[#f4f7fa] text-primary-950 min-[1600px]:min-h-svh {{ $duoLineClasses }}"
>
    <div
        class="container relative isolate z-[1] mx-auto flex w-full !min-h-0 flex-col justify-center gap-0 space-y-6 animate-in fade-in-0 slide-in-from-right duration-700 ease-in-out sm:space-y-12"
    >

        <div class="flex flex-col gap-6 pt-[100px] sm:gap-10 lg:gap-12">


            @if(isset($subheading) && $displaySubheadingFirst)
                <{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}
                    class="{{ $heroEyebrowClasses }}"
                >
                    {!! pipe2br(mos($subheading)) !!}
                </{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}>
            @endif

            @if(isset($heading))
                <{{ $useH1 ? ($subheadingIsPrimary ? 'h2' : 'h1') : ($subheadingIsPrimary ? 'h3' : 'h2') }}
                    class="{{ $heroHeadingClasses }}"
                >
                    {!! pipe2br(mos($heading)) !!}
                </{{ $useH1 ? ($subheadingIsPrimary ? 'h2' : 'h1') : ($subheadingIsPrimary ? 'h3' : 'h2') }}>
            @endif

            @if(isset($subheading) && !$displaySubheadingFirst)
                <{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}
                    class="{{ $heroEyebrowClasses }} max-w-[54rem]"
                >
                    {!! pipe2br(mos($subheading)) !!}
                </{{ $useH1 ? ($subheadingIsPrimary ? 'h1' : 'h2') : ($subheadingIsPrimary ? 'h2' : 'h3') }}>
            @endif


            <div class="text-sm leading-6 text-secondary-700 sm:text-base md:max-w-[50rem] md:text-lg">
                @if(isset($content))
                    {!! mos(nl2br($content)) !!}
                @endif
            </div>


            @if($showButtons == true)
            <div class="max-w-sm mt-2 sm:mt-10 sm:max-w-none sm:flex sm:justify-start">
                <div class="space-y-4 sm:space-y-0 sm:inline-grid sm:grid-cols-2 sm:gap-8">

                @if($buttonOneOpensAnalysisSidebar)
                    <x-button.primary-link
                        href="{{ route($button_1_route) }}"
                        x-on:click="if (window.matchMedia('(min-width: 1600px)').matches && document.querySelector('[data-duo-analysis-sidebar-panel]')) { $event.preventDefault(); window.dispatchEvent(new CustomEvent('site:open-analysis-sidebar')); }"
                    >
                        {{  $button_1_text }}
                    </x-button.primary-link>
                @else
                    <x-button.primary-link href="{{ route($button_1_route) }}">
                        {{  $button_1_text }}
                    </x-button.primary-link>
                @endif

                    <x-button.primary-link
                        as="button"
                        type="button"
                        outline
                        x-on:click="
                            const hero = $el.closest('.duo-hero');
                            const nextSection = hero?.nextElementSibling;

                            if (! nextSection) {
                                return;
                            }

                            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                            const desktopLayout = window.matchMedia('(min-width: 1600px)').matches;
                            const mobileHeader = document.querySelector('[data-site-header] > header.site-header-frame');
                            const mobileHeaderHeight = desktopLayout ? 0 : (mobileHeader?.getBoundingClientRect().height ?? 0);
                            const targetTop = nextSection.getBoundingClientRect().top + window.scrollY - (desktopLayout ? 0 : mobileHeaderHeight);

                            window.scrollTo({
                                top: targetTop,
                                behavior: prefersReducedMotion ? 'auto' : 'smooth',
                            });
                        "
                    >
                        Czytaj dalej
                    </x-button.primary-link>

                </div>
            </div>
            @endif

        </div>

    </div>
</div>
