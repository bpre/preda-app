import './bootstrap';

const DESKTOP_SMOOTH_SCROLL_QUERY = '(min-width: 1600px) and (prefers-reduced-motion: no-preference)';
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
        analysisCoverVisible: false,
        smoothEnabled: false,
        smoothCurrentY: 0,
        smoothTargetY: 0,
        smoothFrame: null,
        smoothMediaQuery: null,
        smoothResizeObserver: null,
        smoothHeader: null,
        smoothHeaderPlaceholder: null,
        sidebarScrollExtra: 0,

        init() {
            if (typeof window === 'undefined') {
                return;
            }

            this.smoothMediaQuery = window.matchMedia(DESKTOP_SMOOTH_SCROLL_QUERY);
            this.boundHandleSmoothMode = () => this.syncSmoothMode();
            this.boundHandleSmoothScroll = () => this.handleSmoothScroll();
            this.boundRefreshSmoothLayout = () => this.refreshSmoothLayout();
            this.boundHandleSidebarScrollSpace = (event) => {
                this.setSidebarScrollExtra(event.detail?.extra ?? 0);
            };
            this.boundHandleAnalysisCover = (event) => {
                this.analysisCoverVisible = Boolean(event.detail?.visible);
            };

            if (typeof this.smoothMediaQuery.addEventListener === 'function') {
                this.smoothMediaQuery.addEventListener('change', this.boundHandleSmoothMode);
            } else {
                this.smoothMediaQuery.addListener(this.boundHandleSmoothMode);
            }

            window.addEventListener('scroll', this.boundHandleSmoothScroll, { passive: true });
            window.addEventListener('resize', this.boundRefreshSmoothLayout, { passive: true });
            window.addEventListener('load', this.boundRefreshSmoothLayout, { passive: true });
            window.addEventListener('site:sidebar-scroll-space', this.boundHandleSidebarScrollSpace);
            window.addEventListener('site:analysis-sidebar-cover', this.boundHandleAnalysisCover);

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
            window.removeEventListener('site:sidebar-scroll-space', this.boundHandleSidebarScrollSpace);
            window.removeEventListener('site:analysis-sidebar-cover', this.boundHandleAnalysisCover);

            this.analysisCoverVisible = false;
            this.setSidebarScrollExtra(0);
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

            this.smoothHeader = this.$refs.headerLayer.querySelector('[data-site-header]')
                || this.$refs.smoothContent.querySelector('[data-site-header]');

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

            if (
                !this.$refs.headerLayer.contains(this.smoothHeader)
                && !this.smoothHeaderPlaceholder.isConnected
            ) {
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

        setSidebarScrollExtra(extra) {
            const nextExtra = Math.max(0, Number(extra) || 0);
            const isClearingExtra = nextExtra === 0 && this.sidebarScrollExtra !== 0;

            if (!isClearingExtra && Math.abs(nextExtra - this.sidebarScrollExtra) < 1) {
                return;
            }

            this.sidebarScrollExtra = nextExtra;

            if (this.$refs.sidebarScrollSpacer) {
                this.$refs.sidebarScrollSpacer.style.height = nextExtra > 0 ? `${nextExtra}px` : '';
            }

            this.refreshSmoothLayout();
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

    window.Alpine.data('duoSidebar', () => ({
        sidebarView: 'menu',
        analysisOpenScrollY: 0,
        analysisScrollOffset: 0,
        analysisScrollExtra: 0,
        analysisMaxScroll: 0,
        analysisScrollFrame: null,
        analysisResizeObserver: null,
        analysisResetTimeout: null,
        analysisFormProcessComplete: false,

        init() {
            if (typeof window === 'undefined') {
                return;
            }

            this.boundQueueAnalysisScrollSync = () => this.queueAnalysisScrollSync();
            this.boundRefreshAnalysisScrollSpace = () => this.refreshAnalysisScrollSpace();
            this.boundOpenAnalysisSidebar = () => this.openAnalysisSidebar();
            this.boundCloseAnalysisSidebar = () => this.closeAnalysisSidebar();
            this.boundHandleAnalysisFormCompletion = (event) => this.handleAnalysisFormCompletion(event);

            window.addEventListener('scroll', this.boundQueueAnalysisScrollSync, { passive: true });
            window.addEventListener('resize', this.boundRefreshAnalysisScrollSpace, { passive: true });
            window.addEventListener('load', this.boundRefreshAnalysisScrollSpace, { passive: true });
            window.addEventListener('site:smooth-scroll', this.boundQueueAnalysisScrollSync);
            window.addEventListener('site:open-analysis-sidebar', this.boundOpenAnalysisSidebar);
            window.addEventListener('site:close-analysis-sidebar', this.boundCloseAnalysisSidebar);
            window.addEventListener('analysis-form-completion', this.boundHandleAnalysisFormCompletion);
        },

        destroy() {
            window.removeEventListener('scroll', this.boundQueueAnalysisScrollSync);
            window.removeEventListener('resize', this.boundRefreshAnalysisScrollSpace);
            window.removeEventListener('load', this.boundRefreshAnalysisScrollSpace);
            window.removeEventListener('site:smooth-scroll', this.boundQueueAnalysisScrollSync);
            window.removeEventListener('site:open-analysis-sidebar', this.boundOpenAnalysisSidebar);
            window.removeEventListener('site:close-analysis-sidebar', this.boundCloseAnalysisSidebar);
            window.removeEventListener('analysis-form-completion', this.boundHandleAnalysisFormCompletion);
            this.setAnalysisCoverVisible(false);

            if (this.analysisScrollFrame) {
                window.cancelAnimationFrame(this.analysisScrollFrame);
                this.analysisScrollFrame = null;
            }

            if (this.analysisResetTimeout) {
                window.clearTimeout(this.analysisResetTimeout);
                this.analysisResetTimeout = null;
            }

            this.analysisResizeObserver?.disconnect();
            this.resetAnalysisScrollSpace();
        },

        openAnalysisSidebar() {
            if (!this.$refs.analysisSidebarPanel) {
                return;
            }

            this.analysisOpenScrollY = window.scrollY || window.pageYOffset || 0;
            this.analysisScrollOffset = 0;
            this.sidebarView = 'analysis';
            this.setAnalysisCoverVisible(true);

            this.$nextTick(() => {
                this.observeAnalysisPanel();
                this.refreshAnalysisScrollSpace();
                this.syncAnalysisScroll();
            });
        },

        closeAnalysisSidebar() {
            if (this.sidebarView !== 'analysis') {
                this.setAnalysisCoverVisible(false);
                return;
            }

            const returnScrollY = this.analysisOpenScrollY;
            const shouldResetAnalysisForm = this.analysisFormProcessComplete;

            this.setAnalysisCoverVisible(false);
            this.sidebarView = 'menu';
            this.analysisScrollOffset = 0;
            this.analysisMaxScroll = 0;
            this.analysisResizeObserver?.disconnect();
            this.analysisResizeObserver = null;
            this.resetAnalysisScrollSpace();

            window.requestAnimationFrame(() => {
                window.scrollTo({ top: returnScrollY, behavior: 'auto' });
            });

            if (shouldResetAnalysisForm) {
                this.scheduleAnalysisFormReset();
            }
        },

        analysisPanelStyle() {
            return {
                '--duo-analysis-scroll-y': `-${this.analysisScrollOffset}px`,
            };
        },

        handleAnalysisFormCompletion(event) {
            if ((event.detail?.context ?? 'sidebar') !== 'sidebar') {
                return;
            }

            this.analysisFormProcessComplete = Boolean(event.detail?.complete);
        },

        scheduleAnalysisFormReset() {
            if (this.analysisResetTimeout) {
                window.clearTimeout(this.analysisResetTimeout);
            }

            this.analysisResetTimeout = window.setTimeout(() => {
                window.dispatchEvent(new CustomEvent('analysis-sidebar-reset-form'));
                this.analysisFormProcessComplete = false;
                this.analysisResetTimeout = null;
            }, 360);
        },

        setAnalysisCoverVisible(visible) {
            window.dispatchEvent(new CustomEvent('site:analysis-sidebar-cover', {
                detail: { visible },
            }));
        },

        observeAnalysisPanel() {
            if (typeof ResizeObserver === 'undefined' || !this.$refs.analysisSidebarPanel || this.analysisResizeObserver) {
                return;
            }

            this.analysisResizeObserver = new ResizeObserver(() => {
                this.refreshAnalysisScrollSpace();
                this.queueAnalysisScrollSync();
            });
            this.analysisResizeObserver.observe(this.$refs.analysisSidebarPanel);
        },

        getAnalysisScrollRange() {
            const panel = this.$refs.analysisSidebarPanel;

            if (!panel) {
                return 0;
            }

            return Math.max(panel.scrollHeight - window.innerHeight, 0);
        },

        getRemainingPageScrollWithoutAnalysisSpace() {
            const viewportHeight = window.innerHeight || 0;
            const documentHeight = document.documentElement.scrollHeight || 0;
            const pageMaxScroll = Math.max(documentHeight - this.analysisScrollExtra - viewportHeight, 0);

            return Math.max(pageMaxScroll - (window.scrollY || window.pageYOffset || 0), 0);
        },

        refreshAnalysisScrollSpace() {
            if (this.sidebarView !== 'analysis') {
                this.resetAnalysisScrollSpace();
                return;
            }

            this.analysisMaxScroll = this.getAnalysisScrollRange();

            const remainingPageScroll = this.getRemainingPageScrollWithoutAnalysisSpace();
            const nextExtra = Math.max(this.analysisMaxScroll - remainingPageScroll, 0);

            if (Math.abs(nextExtra - this.analysisScrollExtra) < 1) {
                return;
            }

            this.analysisScrollExtra = nextExtra;
            window.dispatchEvent(new CustomEvent('site:sidebar-scroll-space', {
                detail: { extra: nextExtra },
            }));
        },

        resetAnalysisScrollSpace() {
            if (this.analysisScrollExtra === 0) {
                return;
            }

            this.analysisScrollExtra = 0;
            window.dispatchEvent(new CustomEvent('site:sidebar-scroll-space', {
                detail: { extra: 0 },
            }));
        },

        queueAnalysisScrollSync() {
            if (this.sidebarView !== 'analysis' || this.analysisScrollFrame) {
                return;
            }

            this.analysisScrollFrame = window.requestAnimationFrame(() => this.syncAnalysisScroll());
        },

        syncAnalysisScroll() {
            this.analysisScrollFrame = null;

            if (this.sidebarView !== 'analysis') {
                return;
            }

            this.analysisMaxScroll = this.getAnalysisScrollRange();

            const currentScrollY = window.scrollY || window.pageYOffset || 0;
            const nextOffset = Math.max(0, Math.min(currentScrollY - this.analysisOpenScrollY, this.analysisMaxScroll));

            this.analysisScrollOffset = nextOffset;
            this.refreshAnalysisScrollSpace();
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
