const OVERLAY_SELECTOR = '[data-cookieyes-consent-overlay]';
const VISIBLE_ATTRIBUTE = 'data-cookieyes-consent-overlay-visible';
const COOKIEYES_COOKIE_NAME = 'cookieyes-consent';
const SCROLL_LOCK_CLASS = 'cookieyes-consent-scroll-locked';
const HIDE_DELAY_MS = 220;
const DETECTION_POLL_INTERVAL_MS = 250;
const DETECTION_POLL_TIMEOUT_MS = 8000;
const COOKIEYES_UI_SELECTOR = [
    '#cky-consent',
    '[id^="cky-"]',
    '[class^="cky-"]',
    '[class*=" cky-"]',
].join(',');
const COOKIEYES_SCRIPT_SELECTOR = [
    'script[src*="cookieyes"]',
    'script[src*="cdn-cookieyes"]',
    'script[id*="cookieyes" i]',
].join(',');
const SCROLL_LOCK_KEYS = new Set([
    ' ',
    'ArrowDown',
    'ArrowLeft',
    'ArrowRight',
    'ArrowUp',
    'End',
    'Home',
    'PageDown',
    'PageUp',
]);

const readCookie = (name) => {
    const encodedName = `${encodeURIComponent(name)}=`;

    return document.cookie
        .split(';')
        .map((cookie) => cookie.trim())
        .find((cookie) => cookie.startsWith(encodedName))
        ?.slice(encodedName.length) ?? null;
};

const parseCookieYesCookie = () => {
    const cookie = readCookie(COOKIEYES_COOKIE_NAME);

    if (!cookie) {
        return null;
    }

    return decodeURIComponent(cookie)
        .split(',')
        .reduce((result, pair) => {
            const [key, value] = pair.split(':');

            if (key && value !== undefined) {
                result[key.trim()] = value.trim();
            }

            return result;
        }, {});
};

const readCookieYesConsent = () => {
    if (typeof window.getCkyConsent === 'function') {
        try {
            return window.getCkyConsent();
        } catch {
            return null;
        }
    }

    return null;
};

const hasCompletedCookieYesAction = (detail = null) => {
    if (typeof detail?.isUserActionCompleted === 'boolean') {
        return detail.isUserActionCompleted;
    }

    const consent = readCookieYesConsent();

    if (typeof consent?.isUserActionCompleted === 'boolean') {
        return consent.isUserActionCompleted;
    }

    const cookie = parseCookieYesCookie();

    if (!cookie) {
        return false;
    }

    return ['yes', 'true', '1'].includes((cookie.action ?? cookie.consent ?? '').toLowerCase());
};

const hasIncompleteCookieYesAction = (detail = null) => {
    if (typeof detail?.isUserActionCompleted === 'boolean') {
        return !detail.isUserActionCompleted;
    }

    const consent = readCookieYesConsent();

    return typeof consent?.isUserActionCompleted === 'boolean'
        ? !consent.isUserActionCompleted
        : false;
};

const isElementVisible = (element) => {
    if (!element || !element.getClientRects().length) {
        return false;
    }

    const styles = window.getComputedStyle(element);

    return styles.display !== 'none'
        && styles.visibility !== 'hidden'
        && Number.parseFloat(styles.opacity || '1') > 0;
};

const hasVisibleCookieYesUi = () => {
    return Array.from(document.querySelectorAll(COOKIEYES_UI_SELECTOR)).some(isElementVisible);
};

const hasCookieYesUi = () => {
    return Boolean(document.querySelector(COOKIEYES_UI_SELECTOR));
};

const hasCookieYesApi = () => {
    return typeof window.getCkyConsent === 'function';
};

const hasCookieYesScript = () => {
    return Boolean(document.querySelector(COOKIEYES_SCRIPT_SELECTOR));
};

const hasCookieYesPresence = () => {
    return hasCookieYesApi() || hasCookieYesUi() || hasCookieYesScript();
};

const eventTargetAllowsScroll = (event) => {
    return Boolean(event.target?.closest?.(COOKIEYES_UI_SELECTOR));
};

const initCookieYesConsentOverlay = () => {
    if (window.__cookieYesConsentOverlayInitialized) {
        return;
    }

    const overlay = document.querySelector(OVERLAY_SELECTOR);

    if (!overlay) {
        return;
    }

    window.__cookieYesConsentOverlayInitialized = true;

    let hideTimeout = null;
    let frame = null;
    let pendingDetail = null;
    let pendingActionCompleted = false;
    let cookieYesSeen = hasCookieYesPresence();
    let detectionStartedAt = window.performance?.now?.() ?? Date.now();
    let scrollLocked = false;
    let lockedScrollY = 0;

    const keepWindowScrollLocked = () => {
        if (!scrollLocked || Math.abs((window.scrollY || window.pageYOffset || 0) - lockedScrollY) < 1) {
            return;
        }

        window.scrollTo({ top: lockedScrollY, behavior: 'auto' });
    };

    const preventPageScroll = (event) => {
        if (!scrollLocked || eventTargetAllowsScroll(event)) {
            return;
        }

        event.preventDefault();
    };

    const preventKeyboardScroll = (event) => {
        if (!scrollLocked || eventTargetAllowsScroll(event) || !SCROLL_LOCK_KEYS.has(event.key)) {
            return;
        }

        event.preventDefault();
    };

    const lockPageScroll = () => {
        if (scrollLocked) {
            return;
        }

        scrollLocked = true;
        lockedScrollY = window.scrollY || window.pageYOffset || 0;
        document.documentElement.classList.add(SCROLL_LOCK_CLASS);
        document.body.classList.add(SCROLL_LOCK_CLASS);

        window.addEventListener('wheel', preventPageScroll, { passive: false });
        window.addEventListener('touchmove', preventPageScroll, { passive: false });
        window.addEventListener('keydown', preventKeyboardScroll, { passive: false });
        window.addEventListener('scroll', keepWindowScrollLocked, { passive: true });
    };

    const unlockPageScroll = () => {
        if (!scrollLocked) {
            return;
        }

        scrollLocked = false;
        document.documentElement.classList.remove(SCROLL_LOCK_CLASS);
        document.body.classList.remove(SCROLL_LOCK_CLASS);

        window.removeEventListener('wheel', preventPageScroll);
        window.removeEventListener('touchmove', preventPageScroll);
        window.removeEventListener('keydown', preventKeyboardScroll);
        window.removeEventListener('scroll', keepWindowScrollLocked);
        window.scrollTo({ top: lockedScrollY, behavior: 'auto' });
    };

    const showOverlay = () => {
        if (hideTimeout) {
            window.clearTimeout(hideTimeout);
            hideTimeout = null;
        }

        lockPageScroll();

        if (!overlay.hidden && overlay.hasAttribute(VISIBLE_ATTRIBUTE)) {
            return;
        }

        overlay.hidden = false;
        window.requestAnimationFrame(() => {
            overlay.setAttribute(VISIBLE_ATTRIBUTE, '');
        });
    };

    const hideOverlay = () => {
        unlockPageScroll();

        if (overlay.hidden) {
            return;
        }

        overlay.removeAttribute(VISIBLE_ATTRIBUTE);

        if (hideTimeout) {
            window.clearTimeout(hideTimeout);
        }

        hideTimeout = window.setTimeout(() => {
            overlay.hidden = true;
            hideTimeout = null;
        }, HIDE_DELAY_MS);
    };

    const syncOverlay = (detail = null, actionCompleted = false) => {
        frame = null;

        if (detail || hasCookieYesPresence()) {
            cookieYesSeen = true;
        }

        if (actionCompleted || hasCompletedCookieYesAction(detail)) {
            hideOverlay();
            return;
        }

        if (hasVisibleCookieYesUi() || (cookieYesSeen && hasIncompleteCookieYesAction(detail))) {
            showOverlay();
            return;
        }

        hideOverlay();
    };

    const queueSyncOverlay = (detail = null, actionCompleted = false) => {
        pendingDetail = detail ?? pendingDetail;
        pendingActionCompleted = pendingActionCompleted || actionCompleted;

        if (frame) {
            return;
        }

        frame = window.requestAnimationFrame(() => {
            const nextDetail = pendingDetail;
            const nextActionCompleted = pendingActionCompleted;

            pendingDetail = null;
            pendingActionCompleted = false;

            syncOverlay(nextDetail, nextActionCompleted);
        });
    };

    document.addEventListener('cookieyes_banner_load', (event) => {
        queueSyncOverlay(event.detail);
    });

    document.addEventListener('cookieyes_consent_update', (event) => {
        queueSyncOverlay(event.detail, true);
    });

    window.addEventListener('load', () => queueSyncOverlay(), { passive: true });

    const detectionPoll = window.setInterval(() => {
        queueSyncOverlay();

        const now = window.performance?.now?.() ?? Date.now();

        if (hasCompletedCookieYesAction() || hasVisibleCookieYesUi() || now - detectionStartedAt > DETECTION_POLL_TIMEOUT_MS) {
            window.clearInterval(detectionPoll);
        }
    }, DETECTION_POLL_INTERVAL_MS);

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(() => queueSyncOverlay());
        observer.observe(document.documentElement, {
            attributes: true,
            childList: true,
            subtree: true,
        });
    }

    queueSyncOverlay();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCookieYesConsentOverlay, { once: true });
} else {
    initCookieYesConsentOverlay();
}
