import './bootstrap';
import '../../../shared/assets/js/lead-attribution';
import '../../../shared/assets/js/cookieyes-overlay';

const DESKTOP_SMOOTH_SCROLL_QUERY = '(min-width: 1280px) and (prefers-reduced-motion: no-preference)';
const HERO_PARALLAX_QUERY = '(min-width: 1024px) and (prefers-reduced-motion: no-preference)';

// Higher values feel closer to native scroll. Lower values feel smoother/slower.
const DESKTOP_SMOOTH_SCROLL_STRENGTH = 0.52;
const HERO_PARALLAX_DISTANCE = 500;
const HERO_PARALLAX_EASING = 0.82;

const registerSiteShell = () => {
    if (!window.Alpine || registerSiteShell.registered) {
        return;
    }

    registerSiteShell.registered = true;

    window.Alpine.data('siteShell', () => ({
        showMenu: false,
        menuEffectsVisible: false,
        smoothEnabled: false,
        smoothCurrentY: 0,
        smoothTargetY: 0,
        smoothFrame: null,
        smoothMediaQuery: null,
        smoothResizeObserver: null,
        smoothHeader: null,
        smoothHeaderPlaceholder: null,

        init() {
            if (typeof window === 'undefined') {
                return;
            }

            this.smoothMediaQuery = window.matchMedia(DESKTOP_SMOOTH_SCROLL_QUERY);
            this.boundHandleSmoothMode = () => this.syncSmoothMode();
            this.boundHandleSmoothScroll = () => this.handleSmoothScroll();
            this.boundRefreshSmoothLayout = () => this.refreshSmoothLayout();

            if (typeof this.smoothMediaQuery.addEventListener === 'function') {
                this.smoothMediaQuery.addEventListener('change', this.boundHandleSmoothMode);
            } else {
                this.smoothMediaQuery.addListener(this.boundHandleSmoothMode);
            }

            window.addEventListener('scroll', this.boundHandleSmoothScroll, { passive: true });
            window.addEventListener('resize', this.boundRefreshSmoothLayout, { passive: true });
            window.addEventListener('load', this.boundRefreshSmoothLayout, { passive: true });

            this.$nextTick(() => {
                this.cacheSmoothHeader();
                this.syncSmoothMode();
            });
        },

        destroy() {
            if (this.smoothMediaQuery) {
                if (typeof this.smoothMediaQuery.removeEventListener === 'function') {
                    this.smoothMediaQuery.removeEventListener('change', this.boundHandleSmoothMode);
                } else {
                    this.smoothMediaQuery.removeListener(this.boundHandleSmoothMode);
                }
            }

            window.removeEventListener('scroll', this.boundHandleSmoothScroll);
            window.removeEventListener('resize', this.boundRefreshSmoothLayout);
            window.removeEventListener('load', this.boundRefreshSmoothLayout);

            this.disableSmoothScroll();
        },

        openMobileMenu() {
            this.menuEffectsVisible = true;
            this.showMenu = true;
        },

        closeMobileMenu() {
            this.menuEffectsVisible = false;
            this.showMenu = false;
        },

        cacheSmoothHeader() {
            if (this.smoothHeader) {
                return;
            }

            this.smoothHeader = this.$refs.smoothContent.querySelector('[data-site-header]');

            if (!this.smoothHeaderPlaceholder) {
                this.smoothHeaderPlaceholder = document.createElement('div');
                this.smoothHeaderPlaceholder.hidden = true;
                this.smoothHeaderPlaceholder.setAttribute('aria-hidden', 'true');
                this.smoothHeaderPlaceholder.setAttribute('data-smooth-header-placeholder', '');
            }
        },

        moveHeaderToLayer() {
            this.cacheSmoothHeader();

            if (!this.smoothHeader) {
                return;
            }

            if (!this.smoothHeaderPlaceholder.isConnected) {
                this.smoothHeader.before(this.smoothHeaderPlaceholder);
            }

            if (!this.$refs.headerLayer.contains(this.smoothHeader)) {
                this.$refs.headerLayer.appendChild(this.smoothHeader);
            }
        },

        restoreHeaderToContent() {
            if (!this.smoothHeader || !this.smoothHeaderPlaceholder?.isConnected) {
                return;
            }

            this.smoothHeaderPlaceholder.replaceWith(this.smoothHeader);
        },

        getSmoothMaxScroll() {
            return Math.max(this.$refs.smoothContent.scrollHeight - window.innerHeight, 0);
        },

        applySmoothTransform(y) {
            this.$refs.smoothContent.style.transform = `translate3d(0, ${-y}px, 0)`;
            window.dispatchEvent(new CustomEvent('site:smooth-scroll', { detail: { y } }));
        },

        queueSmoothFrame() {
            if (this.smoothFrame) {
                return;
            }

            this.smoothFrame = window.requestAnimationFrame(() => this.animateSmoothScroll());
        },

        animateSmoothScroll() {
            const distance = this.smoothTargetY - this.smoothCurrentY;

            if (Math.abs(distance) <= 0.1) {
                this.smoothCurrentY = this.smoothTargetY;
                this.applySmoothTransform(this.smoothCurrentY);
                this.smoothFrame = null;
                return;
            }

            this.smoothCurrentY += distance * DESKTOP_SMOOTH_SCROLL_STRENGTH;
            this.applySmoothTransform(this.smoothCurrentY);
            this.smoothFrame = window.requestAnimationFrame(() => this.animateSmoothScroll());
        },

        observeSmoothContent() {
            if (this.smoothResizeObserver || typeof ResizeObserver === 'undefined') {
                return;
            }

            this.smoothResizeObserver = new ResizeObserver(() => this.refreshSmoothLayout());
            this.smoothResizeObserver.observe(this.$refs.smoothContent);
        },

        stopObservingSmoothContent() {
            this.smoothResizeObserver?.disconnect();
            this.smoothResizeObserver = null;
        },

        enableSmoothScroll() {
            if (this.smoothEnabled) {
                this.refreshSmoothLayout();
                return;
            }

            this.smoothEnabled = true;
            this.moveHeaderToLayer();

            document.documentElement.style.scrollBehavior = 'auto';
            document.body.style.scrollBehavior = 'auto';

            Object.assign(this.$refs.smoothWrapper.style, {
                position: 'fixed',
                inset: '0',
                width: '100%',
                height: '100%',
                overflow: 'hidden',
            });

            Object.assign(this.$refs.smoothContent.style, {
                width: '100%',
                minHeight: '100%',
                willChange: 'transform',
            });

            this.smoothCurrentY = window.scrollY;
            this.smoothTargetY = window.scrollY;

            this.observeSmoothContent();
            this.refreshSmoothLayout();
            this.applySmoothTransform(this.smoothCurrentY);
        },

        disableSmoothScroll() {
            if (!this.smoothEnabled) {
                return;
            }

            this.smoothEnabled = false;

            if (this.smoothFrame) {
                window.cancelAnimationFrame(this.smoothFrame);
                this.smoothFrame = null;
            }

            this.stopObservingSmoothContent();
            this.restoreHeaderToContent();

            document.body.style.height = '';
            document.documentElement.style.scrollBehavior = '';
            document.body.style.scrollBehavior = '';

            Object.assign(this.$refs.smoothWrapper.style, {
                position: '',
                inset: '',
                width: '',
                height: '',
                overflow: '',
            });

            Object.assign(this.$refs.smoothContent.style, {
                width: '',
                minHeight: '',
                willChange: '',
                transform: '',
            });
        },

        syncSmoothMode() {
            if (this.smoothMediaQuery?.matches) {
                this.enableSmoothScroll();
                return;
            }

            this.disableSmoothScroll();
        },

        refreshSmoothLayout() {
            if (!this.smoothEnabled) {
                return;
            }

            const maxScroll = this.getSmoothMaxScroll();
            const bodyHeight = Math.max(this.$refs.smoothContent.scrollHeight, window.innerHeight);

            document.body.style.height = `${bodyHeight}px`;
            this.smoothTargetY = Math.min(window.scrollY, maxScroll);
            this.smoothCurrentY = Math.min(this.smoothCurrentY, maxScroll);
            this.applySmoothTransform(this.smoothCurrentY);
            this.queueSmoothFrame();
        },

        handleSmoothScroll() {
            if (!this.smoothEnabled) {
                return;
            }

            this.smoothTargetY = Math.min(window.scrollY, this.getSmoothMaxScroll());
            this.queueSmoothFrame();
        },
    }));

    window.Alpine.data('heroParallax', () => ({
        parallaxEnabled: false,
        parallaxVisible: false,
        parallaxOffset: 0,
        parallaxTarget: 0,
        parallaxFrame: null,
        parallaxObserver: null,
        parallaxMediaQuery: null,

        init() {
            if (typeof window === 'undefined') {
                return;
            }

            this.parallaxMediaQuery = window.matchMedia(HERO_PARALLAX_QUERY);
            this.boundHandleParallaxMode = () => this.syncParallaxMode();
            this.boundQueueParallaxUpdate = () => this.queueParallaxUpdate();

            if (typeof this.parallaxMediaQuery.addEventListener === 'function') {
                this.parallaxMediaQuery.addEventListener('change', this.boundHandleParallaxMode);
            } else {
                this.parallaxMediaQuery.addListener(this.boundHandleParallaxMode);
            }

            window.addEventListener('scroll', this.boundQueueParallaxUpdate, { passive: true });
            window.addEventListener('resize', this.boundQueueParallaxUpdate, { passive: true });
            window.addEventListener('site:smooth-scroll', this.boundQueueParallaxUpdate);

            if (typeof IntersectionObserver !== 'undefined') {
                this.parallaxObserver = new IntersectionObserver(([entry]) => {
                    this.parallaxVisible = entry?.isIntersecting ?? false;

                    if (this.parallaxVisible) {
                        this.queueParallaxUpdate();
                    } else {
                        this.stopParallax();
                    }
                });

                this.parallaxObserver.observe(this.$el);
            } else {
                this.parallaxVisible = true;
            }

            this.$nextTick(() => this.syncParallaxMode());
        },

        destroy() {
            if (this.parallaxMediaQuery) {
                if (typeof this.parallaxMediaQuery.removeEventListener === 'function') {
                    this.parallaxMediaQuery.removeEventListener('change', this.boundHandleParallaxMode);
                } else {
                    this.parallaxMediaQuery.removeListener(this.boundHandleParallaxMode);
                }
            }

            window.removeEventListener('scroll', this.boundQueueParallaxUpdate);
            window.removeEventListener('resize', this.boundQueueParallaxUpdate);
            window.removeEventListener('site:smooth-scroll', this.boundQueueParallaxUpdate);

            this.parallaxObserver?.disconnect();
            this.stopParallax();
        },

        syncParallaxMode() {
            this.parallaxEnabled = this.parallaxMediaQuery?.matches ?? false;

            if (this.parallaxEnabled) {
                this.queueParallaxUpdate();
                return;
            }

            this.resetParallax();
        },

        updateParallaxTarget() {
            const rect = this.$el.getBoundingClientRect();
            const viewportHeight = window.innerHeight || 1;
            const progress = ((viewportHeight - rect.top) / (viewportHeight + rect.height)) - 0.5;
            const normalized = Math.max(-1, Math.min(1, progress * 2));

            this.parallaxTarget = normalized * HERO_PARALLAX_DISTANCE;
        },

        queueParallaxUpdate() {
            if (!this.parallaxEnabled || !this.parallaxVisible) {
                return;
            }

            this.updateParallaxTarget();

            if (!this.parallaxFrame) {
                this.parallaxFrame = window.requestAnimationFrame(() => this.animateParallax());
            }
        },

        animateParallax() {
            const distance = this.parallaxTarget - this.parallaxOffset;

            if (Math.abs(distance) <= 0.1) {
                this.parallaxOffset = this.parallaxTarget;
                this.parallaxFrame = null;
                return;
            }

            this.parallaxOffset += distance * HERO_PARALLAX_EASING;
            this.parallaxFrame = window.requestAnimationFrame(() => this.animateParallax());
        },

        stopParallax() {
            if (this.parallaxFrame) {
                window.cancelAnimationFrame(this.parallaxFrame);
                this.parallaxFrame = null;
            }
        },

        resetParallax() {
            this.stopParallax();
            this.parallaxTarget = 0;
            this.parallaxOffset = 0;
        },
    }));
};

if (window.Alpine) {
    registerSiteShell();
}

document.addEventListener('alpine:init', registerSiteShell);
