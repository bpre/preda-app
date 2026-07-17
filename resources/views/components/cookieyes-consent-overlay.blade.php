<style>
    [data-cookieyes-consent-overlay] {
        position: fixed;
        inset: 0;
        z-index: 999998;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        pointer-events: none;
        transition: opacity 180ms ease-out;
    }

    [data-cookieyes-consent-overlay][data-cookieyes-consent-overlay-visible] {
        opacity: 1;
        pointer-events: auto;
    }

    :where(
        #cky-consent,
        [id^='cky-'],
        [class^='cky-'],
        [class*=' cky-']
    ) {
        z-index: 9999999 !important;
    }

    :where(.cky-consent-container) {
        position: fixed !important;
        inset: auto !important;
        top: 50% !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;
        width: min(520px, calc(100vw - 2rem)) !important;
        max-width: calc(100vw - 2rem) !important;
        margin: 0 !important;
        transform: translate3d(-50%, -50%, 0) !important;
    }

    :where(.cky-consent-container .cky-consent-bar) {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    :where(
        [data-cky-tag='powered-by'],
        [data-cky-tag='detail-powered-by'],
        .cky-powered-by
    ) {
        display: none !important;
    }

    html.cookieyes-consent-scroll-locked,
    html.cookieyes-consent-scroll-locked body {
        overflow: hidden !important;
        overscroll-behavior: none;
    }

    @media (prefers-reduced-motion: reduce) {
        [data-cookieyes-consent-overlay] {
            transition: none;
        }
    }
</style>

<div
    data-cookieyes-consent-overlay
    hidden
    aria-hidden="true"
></div>
