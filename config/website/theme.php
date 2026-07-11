<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Website Theme
    |--------------------------------------------------------------------------
    |
    | This value controls which website view theme should be loaded first.
    | Theme views live in resources/views/themes/{theme}. The fallback theme is
    | checked when the active theme does not provide a given view.
    |
    */
    'active' => env('WEBSITE_THEME', 'duo'),
    'fallback' => env('WEBSITE_FALLBACK_THEME', 'duo'),

    /*
    |--------------------------------------------------------------------------
    | Website Logo Orientation
    |--------------------------------------------------------------------------
    |
    | Choose how the main website logo should be displayed by themes that
    | support logo orientation variants.
    |
    | Supported values: "normal", "rotated-left", "dynamic", "decreasing"
    |
    */
    'logo_orientation' => env('WEBSITE_LOGO_ORIENTATION', 'decreasing'),

    /*
    |--------------------------------------------------------------------------
    | Website Logo Content Spacing
    |--------------------------------------------------------------------------
    |
    | Increase the horizontal space between a standalone logo and the beginning
    | of section content in themes that support this layout.
    |
    | Supported values: 0, 1, 2, 3
    |
    */
    'logo_content_spacing' => env('WEBSITE_LOGO_CONTENT_SPACING', 3),

    /*
    |--------------------------------------------------------------------------
    | Website Active Practice Contexts
    |--------------------------------------------------------------------------
    |
    | The base "credits" context is always available. Additional contexts must
    | be listed here to make their related pages and navigation available.
    |
    | Supported values: "family-law"
    |
    */
    'active_contexts' => [
        // 'family-law',
    ],

    /*
    |--------------------------------------------------------------------------
    | Website Corner Style
    |--------------------------------------------------------------------------
    |
    | Change this single value to switch the website between rounded, softly
    | rounded and sharp square corners across buttons, cards, images and icons.
    |
    | Supported values: "rounded", "semirounded", "square"
    |
    */
    'corners' => env('WEBSITE_CORNERS', 'semirounded'),

    /*
    |--------------------------------------------------------------------------
    | Website Primary Color
    |--------------------------------------------------------------------------
    |
    | Change this single value to switch the primary Tailwind color scale across
    | text, backgrounds, borders and other primary-* utilities on the website.
    |
    | Supported values: "slate", "gray", "zinc", "neutral", "stone", "red",
    | "orange", "amber", "yellow", "lime", "green", "emerald", "teal",
    | "cyan", "sky", "blue", "indigo", "violet", "purple", "fuchsia",
    | "pink", "rose"
    |
    */
    'primary_color' => env('WEBSITE_PRIMARY_COLOR', 'slate'),

    /*
    |--------------------------------------------------------------------------
    | Website Accent Color
    |--------------------------------------------------------------------------
    |
    | Change this single value to switch the accent Tailwind color scale across
    | buttons, highlights, icons and other accent-* utilities on the website.
    |
    | Supported values: "slate", "gray", "zinc", "neutral", "stone", "red",
    | "orange", "amber", "yellow", "lime", "green", "emerald", "teal",
    | "cyan", "sky", "blue", "indigo", "violet", "purple", "fuchsia",
    | "pink", "rose"
    |
    */
    'accent_color' => env('WEBSITE_ACCENT_COLOR', 'rose'),

    /*
    |--------------------------------------------------------------------------
    | Website Shadows
    |--------------------------------------------------------------------------
    |
    | Change this single value to enable or disable shadows across the website.
    |
    */
    'shadows' => env('WEBSITE_SHADOWS', false),

    /*
    |--------------------------------------------------------------------------
    | Website Header Border Bottom
    |--------------------------------------------------------------------------
    |
    | Show a subtle bottom border under the header, but only when shadows are
    | disabled globally.
    |
    */
    'header_border_bottom' => env('WEBSITE_HEADER_BORDER_BOTTOM', true),
];
