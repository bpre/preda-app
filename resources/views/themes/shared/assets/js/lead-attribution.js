const STORAGE_KEY = 'preda_lead_attribution_v1';

const TRACKED_PARAMS = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_id',
    'utm_term',
    'utm_content',
    'gad',
    'gad_source',
    'gad_campaignid',
    'gclid',
    'gbraid',
    'wbraid',
    'fbclid',
    'msclkid',
    'dclid',
    'ttclid',
    'li_fat_id',
    'campaign',
    'campaignid',
    'adgroupid',
    'assetgroupid',
    'keyword',
    'matchtype',
    'network',
    'device',
    'creative',
    'targetid',
    'placement',
    'adposition',
    'feeditemid',
    'extensionid',
    'adtype',
    'loc_physical_ms',
    'loc_interest_ms',
    'product_channel',
    'product_id',
    'merchant_id',
];

const MAX_VALUE_LENGTH = 1000;

const safeParse = (value) => {
    if (!value) {
        return {};
    }

    try {
        const parsed = JSON.parse(value);

        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
};

const safeStorageGet = () => {
    try {
        return safeParse(window.localStorage.getItem(STORAGE_KEY));
    } catch {
        return {};
    }
};

const safeStorageSet = (value) => {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
    } catch {
        // Attribution is helpful but non-critical. Browsers may block storage.
    }
};

const cleanValue = (value) => {
    if (typeof value !== 'string') {
        return null;
    }

    const cleaned = value.trim();

    if (!cleaned) {
        return null;
    }

    return cleaned.slice(0, MAX_VALUE_LENGTH);
};

const currentParams = () => {
    const params = {};
    const searchParams = new URLSearchParams(window.location.search);

    searchParams.forEach((value, rawKey) => {
        const key = rawKey.toLowerCase();
        const shouldTrack = key.startsWith('utm_') || key.startsWith('gad_') || TRACKED_PARAMS.includes(key);
        const cleanedValue = cleanValue(value);

        if (shouldTrack && cleanedValue) {
            params[key] = cleanedValue;
        }
    });

    return params;
};

const externalReferrer = () => {
    const referrer = cleanValue(document.referrer || '');

    if (!referrer) {
        return null;
    }

    try {
        const referrerUrl = new URL(referrer);

        if (referrerUrl.hostname === window.location.hostname) {
            return null;
        }
    } catch {
        return referrer;
    }

    return referrer;
};

const currentTouch = () => ({
    url: window.location.href,
    path: `${window.location.pathname}${window.location.search}`,
    referrer: externalReferrer(),
    params: currentParams(),
    captured_at: new Date().toISOString(),
});

const hasMarketingSignal = (touch) => {
    return Boolean(
        touch.referrer
        || (touch.params && Object.keys(touch.params).length > 0)
    );
};

const captureAttribution = () => {
    const stored = safeStorageGet();
    const touch = currentTouch();
    const payload = {
        ...stored,
        current_page: {
            url: touch.url,
            path: touch.path,
            captured_at: touch.captured_at,
        },
    };

    if (!payload.first_touch) {
        payload.first_touch = touch;
    }

    if (!payload.last_touch || hasMarketingSignal(touch)) {
        payload.last_touch = hasMarketingSignal(touch) ? touch : payload.last_touch;
    }

    safeStorageSet(payload);
    window.dispatchEvent(new CustomEvent('preda:lead-attribution-updated', { detail: payload }));

    return payload;
};

const getAttribution = () => {
    const stored = safeStorageGet();
    const touch = currentTouch();

    return {
        ...stored,
        current_page: {
            url: touch.url,
            path: touch.path,
            captured_at: touch.captured_at,
        },
    };
};

window.PredaLeadAttribution = {
    capture: captureAttribution,
    get: getAttribution,
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', captureAttribution, { once: true });
} else {
    captureAttribution();
}
