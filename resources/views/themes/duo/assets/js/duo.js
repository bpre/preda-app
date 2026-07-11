import './base.js';
import '../../../shared/assets/js/lead-attribution';

const DYNAMIC_LOGO_SELECTOR = '[data-duo-dynamic-logo]';
const SECTION_REVEAL_SELECTOR = '[data-duo-section-reveal]';
const PAGE_CONTENT_SELECTOR = '.duo-page-content';
const SECTION_SELECTOR = '.duo-section';
const SECTION_REVEAL_PRIMED_CLASS = 'duo-section-heading-reveal--primed';
const SECTION_REVEAL_VISIBLE_CLASS = 'duo-section-heading-reveal--visible';

const initFirstSectionOffset = () => {
    const pageContent = document.querySelector(PAGE_CONTENT_SELECTOR);

    if (!pageContent) {
        return;
    }

    const firstContentElement = Array.from(pageContent.children).find((element) => {
        return !element.matches('[data-site-header]');
    });

    if (!firstContentElement || !firstContentElement.matches(SECTION_SELECTOR) || firstContentElement.matches('.duo-hero')) {
        return;
    }

    firstContentElement.classList.add('duo-first-page-section');
};

const initSectionReveal = () => {
    const revealElements = Array.from(document.querySelectorAll(SECTION_REVEAL_SELECTOR));

    if (!revealElements.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        revealElements.forEach((element) => element.classList.add(SECTION_REVEAL_VISIBLE_CLASS));
        return;
    }

    let frame = null;
    let observer = null;
    const pendingElements = new Set(revealElements);

    const revealElement = (element) => {
        element.classList.add(SECTION_REVEAL_VISIBLE_CLASS);
        pendingElements.delete(element);
        observer?.unobserve(element);
    };

    const revealVisibleElements = () => {
        frame = null;

        Array.from(pendingElements).forEach((element) => {
            const rect = element.getBoundingClientRect();
            const revealPoint = window.innerHeight * 0.86;

            if (rect.top <= revealPoint && rect.bottom >= 0) {
                revealElement(element);
            }
        });
    };

    const queueReveal = () => {
        if (frame) {
            return;
        }

        frame = window.requestAnimationFrame(revealVisibleElements);
    };

    revealElements.forEach((element) => {
        element.classList.add(SECTION_REVEAL_PRIMED_CLASS);
    });

    const startReveal = () => {
        if ('IntersectionObserver' in window) {
            observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        revealElement(entry.target);
                    }
                });
            }, {
                rootMargin: '0px 0px -14% 0px',
                threshold: 0,
            });

            revealElements.forEach((element) => observer.observe(element));
        }

        revealVisibleElements();

        window.addEventListener('scroll', queueReveal, { passive: true });
        window.addEventListener('resize', queueReveal, { passive: true });
        window.addEventListener('load', queueReveal, { passive: true });
        window.addEventListener('site:smooth-scroll', queueReveal);
    };

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(startReveal);
    });
};

const initDynamicLogo = () => {
    const logos = Array.from(document.querySelectorAll(DYNAMIC_LOGO_SELECTOR));

    if (!logos.length) {
        return;
    }

    const syncLogoState = () => {
        const scrollY = window.scrollY || window.pageYOffset || 0;

        logos.forEach((logo) => {
            const threshold = Number.parseFloat(logo.dataset.duoScrollThreshold || '100');
            const useNormalLogo = scrollY >= threshold;
            const mode = logo.dataset.duoLogoMode || 'dynamic';

            if (mode === 'decreasing') {
                const previousState = logo.dataset.duoLogoState;
                const nextState = useNormalLogo ? 'normal' : 'large';

                if (previousState === nextState) {
                    return;
                }

                const shouldAnimateShrink = previousState === 'large' && nextState === 'normal';

                if (!shouldAnimateShrink) {
                    logo.classList.remove('duo-main-logo--decreasing-shrink');
                }

                logo.classList.toggle('duo-main-logo--large', !useNormalLogo);
                logo.classList.toggle('duo-main-logo--normal', useNormalLogo);
                logo.dataset.duoLogoState = nextState;

                if (shouldAnimateShrink) {
                    logo.classList.remove('duo-main-logo--decreasing-shrink');

                    window.requestAnimationFrame(() => {
                        logo.classList.add('duo-main-logo--decreasing-shrink');
                    });
                }

                return;
            }

            logo.classList.toggle('duo-main-logo--rotated-left', !useNormalLogo);
            logo.classList.toggle('duo-main-logo--normal', useNormalLogo);
            logo.dataset.duoLogoState = useNormalLogo ? 'normal' : 'rotated-left';
        });
    };

    let frame = null;

    const queueSyncLogoState = () => {
        if (frame) {
            return;
        }

        frame = window.requestAnimationFrame(() => {
            frame = null;
            syncLogoState();
        });
    };

    syncLogoState();

    logos.forEach((logo) => {
        logo.addEventListener('animationend', (event) => {
            if (event.animationName === 'duo-logo-decrease') {
                logo.classList.remove('duo-main-logo--decreasing-shrink');
            }
        });
    });

    window.addEventListener('scroll', queueSyncLogoState, { passive: true });
    window.addEventListener('resize', queueSyncLogoState, { passive: true });
    window.addEventListener('load', queueSyncLogoState, { passive: true });
    window.addEventListener('site:smooth-scroll', queueSyncLogoState);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initFirstSectionOffset();
        initSectionReveal();
        initDynamicLogo();
    }, { once: true });
} else {
    initFirstSectionOffset();
    initSectionReveal();
    initDynamicLogo();
}
