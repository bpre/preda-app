@php
    $cornerMode = strtolower((string) config('website.theme.corners', 'rounded'));
    $cornerMode = in_array($cornerMode, ['rounded', 'semirounded', 'square'], true) ? $cornerMode : 'rounded';
    $shadowsEnabled = (bool) config('website.theme.shadows', true);
    $primaryColor = strtolower((string) config('website.theme.primary_color', 'slate'));
    $accentColor = strtolower((string) config('website.theme.accent_color', 'rose'));
    $logoContentSpacing = (string) config('website.theme.logo_content_spacing', '0');
    $supportedThemeColors = [
        'slate', 'gray', 'zinc', 'neutral', 'stone', 'red', 'orange', 'amber',
        'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue',
        'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose',
    ];
    $primaryColor = in_array($primaryColor, $supportedThemeColors, true) ? $primaryColor : 'slate';
    $accentColor = in_array($accentColor, $supportedThemeColors, true) ? $accentColor : 'rose';
    $logoContentSpacing = in_array($logoContentSpacing, ['0', '1', '2', '3'], true) ? $logoContentSpacing : '0';
@endphp

<!doctype html>
<html
    lang="pl"
    data-corners="{{ $cornerMode }}"
    data-shadows="{{ $shadowsEnabled ? 'on' : 'off' }}"
    data-primary-color="{{ $primaryColor }}"
    data-accent-color="{{ $accentColor }}"
    data-logo-content-spacing="{{ $logoContentSpacing }}"
    data-theme="duo"
>
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

 {!! $seo !!}
@if (request()->routeIs('homepage'))
  <link rel="canonical" href="{{ config('app.url') }}">
@endif

 @filamentStyles



    @if(app()->isProduction())
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-T7L78BPR');</script>
    <!-- End Google Tag Manager -->
    @endif

  {{ filament()->getTheme()->getHtml() }}

  <style>
    :root {
        --font-family: 'Inter Variable';
        --sidebar-width: {{ filament()->getSidebarWidth() }};
        --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
        --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
    }
 </style>

 <script>
    document.documentElement.dataset.duoReveal = 'primed';
 </script>

 @vite(['resources/views/themes/duo/assets/css/duo.css', 'resources/views/themes/duo/assets/js/duo.js'])

<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
<link rel="manifest" href="/favicon/site.webmanifest" />
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="{{ csrf_token() }}">


</head>

<body x-data="siteShell()" :class="{ 'site-menu-open': menuEffectsVisible }">

    @if(app()->isProduction())
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T7L78BPR"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @endif

    <div>
        <div x-ref="headerLayer"></div>

        <div x-ref="smoothWrapper">
            <div
                x-ref="smoothContent"
                class="site-page-content duo-page-content pt-[var(--duo-mobile-header-height)] [&>*:not([data-site-header])]:mx-0 [&>.duo-error-page]:min-h-[calc(100svh_-_var(--duo-mobile-header-height))] min-[1600px]:pt-0 min-[1600px]:pr-[var(--duo-sidebar-width)] min-[1600px]:[&_.container]:!pl-[var(--duo-content-safe-left)] min-[1600px]:[&>.duo-error-page]:-mr-[var(--duo-sidebar-width)] min-[1600px]:[&>.duo-error-page]:min-h-svh"
            >
                {{  $slot }}

                <div x-ref="sidebarScrollSpacer" class="hidden min-[1600px]:block" aria-hidden="true"></div>
            </div>
        </div>
    </div>

    <div
        x-cloak
        aria-hidden="true"
        class="fixed inset-0 z-[45] hidden bg-black/70 opacity-0 transition-opacity duration-300 ease-out motion-reduce:transition-none min-[1600px]:block"
        :class="analysisCoverVisible ? 'opacity-100' : 'opacity-0'"
        :style="{ pointerEvents: analysisCoverVisible ? 'auto' : 'none' }"
        @click="window.dispatchEvent(new CustomEvent('site:close-analysis-sidebar'))"
    ></div>

    <div
        x-cloak
        aria-hidden="true"
        class="pointer-events-none fixed inset-0 z-[45] bg-black/50 opacity-0 transition-opacity duration-300 ease-out motion-reduce:transition-none"
        :class="menuEffectsVisible ? 'opacity-100' : 'opacity-0'"
    ></div>

    @livewire('alpine')
    @filamentScripts

</body>
</html>
