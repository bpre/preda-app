@php
    $cornerMode = strtolower((string) config('website.theme.corners', 'rounded'));
    $cornerMode = in_array($cornerMode, ['rounded', 'semirounded', 'square'], true) ? $cornerMode : 'rounded';
    $shadowsEnabled = (bool) config('website.theme.shadows', true);
    $primaryColor = strtolower((string) config('website.theme.primary_color', 'slate'));
    $accentColor = strtolower((string) config('website.theme.accent_color', 'rose'));
    $supportedThemeColors = [
        'slate', 'gray', 'zinc', 'neutral', 'stone', 'red', 'orange', 'amber',
        'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue',
        'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose',
    ];
    $primaryColor = in_array($primaryColor, $supportedThemeColors, true) ? $primaryColor : 'slate';
    $accentColor = in_array($accentColor, $supportedThemeColors, true) ? $accentColor : 'rose';
@endphp

<!doctype html>
<html
    lang="pl"
    data-corners="{{ $cornerMode }}"
    data-shadows="{{ $shadowsEnabled ? 'on' : 'off' }}"
    data-primary-color="{{ $primaryColor }}"
    data-accent-color="{{ $accentColor }}"
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
        --font-family: '{!! filament()->getFontFamily() !!}';
        --sidebar-width: {{ filament()->getSidebarWidth() }};
        --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
        --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
    }
 </style>

 @vite(['resources/views/themes/flat/assets/css/flat.css', 'resources/views/themes/flat/assets/js/flat.js'])

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
            <div x-ref="smoothContent" class="site-page-content flex min-h-svh flex-col">
                {{  $slot }}
            </div>
        </div>
    </div>

    <div
        x-cloak
        aria-hidden="true"
        class="pointer-events-none fixed inset-0 z-[45] bg-black/50 opacity-0 transition-opacity duration-300 ease-out motion-reduce:transition-none"
        :class="menuEffectsVisible ? 'opacity-100' : 'opacity-0'"
    ></div>

    <x-cookieyes-consent-overlay />

    @livewire('alpine')
    @filamentScripts

</body>
</html>
