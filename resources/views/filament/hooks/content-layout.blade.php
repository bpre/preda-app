@php
    use App\Support\FilamentContentLayout;

    $contentPreferences = FilamentContentLayout::preferences();
    $useFullWidthForAllContent = FilamentContentLayout::shouldUseFullWidthForAllContent();
    $isCurrentTablePage = FilamentContentLayout::isCurrentTablePage();
    $showRecordListPagesFullWidthToggle = FilamentContentLayout::shouldShowRecordListPagesFullWidthToggle();
    $recordListPagesFullWidthStorageKey = FilamentContentLayout::recordListPagesFullWidthStorageKey();
    $defaultRecordListPagesWidthMode = FilamentContentLayout::defaultRecordListPagesWidthMode();
    $contentMaxWidthCssValue = FilamentContentLayout::contentMaxWidthCssValue();
@endphp

<script>
    (() => {
        const root = document.documentElement;
        let storageKey = @js($recordListPagesFullWidthStorageKey);
        const defaultMode = @js($defaultRecordListPagesWidthMode);
        const updatePreferenceUrl = @js(route('filament-layout.preferences.table-width', absolute: false));
        const csrfToken = @js(csrf_token());
        const isTablePageFromServer = @js($isCurrentTablePage);
        let isTablePageForced = isTablePageFromServer;
        let tableToggleEnabled = @js($showRecordListPagesFullWidthToggle);

        const normalizeMode = (mode) => ['full', 'contained'].includes(mode) ? mode : defaultMode;
        let currentMode = normalizeMode(defaultMode);

        const writeModeToLocalStorage = (mode, key = storageKey) => {
            try {
                window.localStorage.setItem(key, normalizeMode(mode));
            } catch (error) {
                //
            }
        };

        const persistMode = (mode) => {
            fetch(updatePreferenceUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    record_list_pages_full_width: normalizeMode(mode) === 'full',
                }),
            })
                .then((response) => response.ok ? response.json() : null)
                .then((data) => {
                    if (! data?.mode) {
                        return;
                    }

                    if (data.storage_key) {
                        storageKey = data.storage_key;
                    }

                    setMode(data.mode);
                    applyMode(isTableWidthPage());
                })
                .catch(() => {
                    //
                });
        };

        const getMode = () => currentMode;

        const setMode = (mode, persist = false) => {
            currentMode = normalizeMode(mode);
            writeModeToLocalStorage(currentMode);

            if (persist) {
                persistMode(currentMode);
            }

            return currentMode;
        };

        const eventDetail = (event) => {
            if (Array.isArray(event.detail)) {
                return event.detail[0] ?? {};
            }

            return event.detail ?? {};
        };

        const setBooleanDataAttribute = (name, value) => {
            if (value === undefined || value === null) {
                return;
            }

            root.dataset[name] = value ? 'true' : 'false';
        };

        const applyLayoutPreferences = (preferences) => {
            if (preferences.contentMaxWidthCssValue) {
                root.style.setProperty('--filament-content-max-width', preferences.contentMaxWidthCssValue);
                root.style.setProperty('--filament-record-list-pages-max-width', preferences.contentMaxWidthCssValue);
            }

            if (preferences.contentAlignment) {
                root.dataset.filamentContentAlignment = preferences.contentAlignment === 'left' ? 'left' : 'center';
            }

            setBooleanDataAttribute('filamentContentFullWidth', preferences.contentFullWidth);

            if (preferences.recordListPagesFullWidthToggle !== undefined && preferences.recordListPagesFullWidthToggle !== null) {
                tableToggleEnabled = Boolean(preferences.recordListPagesFullWidthToggle);
                setBooleanDataAttribute('filamentTableWidthToggleEnabled', tableToggleEnabled);
            }
        };

        const syncFromPreferences = (detail) => {
            if (detail.storageKey) {
                storageKey = detail.storageKey;
            }

            applyLayoutPreferences(detail);

            if (detail.tableWidthMode) {
                setMode(detail.tableWidthMode);
            }

            applyMode(isTableWidthPage());
        };

        window.addEventListener('filament-layout-preferences-updated', (event) => {
            syncFromPreferences(eventDetail(event));
        });

        window.addEventListener('storage', (event) => {
            if (event.key !== storageKey || ! event.newValue) {
                return;
            }

            setMode(event.newValue);
            applyMode(isTableWidthPage());
        });

        applyLayoutPreferences({
            contentAlignment: @js($contentPreferences['content_alignment']),
            contentFullWidth: @js($useFullWidthForAllContent),
            contentMaxWidthCssValue: @js($contentMaxWidthCssValue),
            recordListPagesFullWidthToggle: @js($showRecordListPagesFullWidthToggle),
        });
        setMode(defaultMode);

        let widthProbe = null;

        const hasTableWidthPageMarker = () => Boolean(document.querySelector('[data-filament-table-width-page]'));
        const hasTableWidthToggle = () => Boolean(document.querySelector('[data-filament-table-width-toggle]'));
        const isTableWidthPage = () => isTablePageForced || hasTableWidthPageMarker();

        const getContainedWidth = () => {
            if (! document.body) {
                return Number.POSITIVE_INFINITY;
            }

            widthProbe ??= document.createElement('div');
            widthProbe.style.cssText = 'position: fixed; inset: 0 auto auto 0; width: 100%; max-width: var(--filament-record-list-pages-max-width); height: 0; visibility: hidden; pointer-events: none;';

            if (! widthProbe.isConnected) {
                document.body.appendChild(widthProbe);
            }

            return widthProbe.getBoundingClientRect().width;
        };

        const getAvailableContentWidth = () => {
            const main = document.querySelector('.fi-main');

            return (main?.parentElement ?? main)?.getBoundingClientRect().width ?? window.innerWidth;
        };

        const canChangeTableWidth = (isTablePage) => (
            isTablePage &&
            tableToggleEnabled &&
            hasTableWidthToggle() &&
            (getAvailableContentWidth() > (getContainedWidth() + 1))
        );

        const syncButtons = (isTablePage = isTablePageFromServer) => {
            const mode = getMode();
            const canChangeWidth = canChangeTableWidth(isTablePage);

            root.dataset.filamentTableWidthCanToggle = canChangeWidth ? 'true' : 'false';

            document.querySelectorAll('[data-filament-table-width-toggle]').forEach((button) => {
                const isFull = mode === 'full';

                button.dataset.mode = mode;
                button.setAttribute('aria-pressed', isFull ? 'true' : 'false');
                button.setAttribute('aria-label', isFull ? 'Wyłącz pełną szerokość tabel' : 'Włącz pełną szerokość tabel');
                button.setAttribute('title', isFull ? 'Wyłącz pełną szerokość tabel' : 'Włącz pełną szerokość tabel');
            });
        };

        const applyMode = (isTablePage = isTablePageFromServer) => {
            if (! isTablePage) {
                root.removeAttribute('data-filament-table-width');
                syncButtons(false);

                return;
            }

            root.dataset.filamentTableWidth = getMode();
            syncButtons(true);
        };

        window.filamentTableWidth = {
            markTablePage: () => {
                isTablePageForced = true;
                applyMode(true);
            },
            toggle: () => {
                const nextMode = getMode() === 'full' ? 'contained' : 'full';

                setMode(nextMode, true);
                applyMode(true);
            },
        };

        applyMode();

        document.addEventListener('livewire:navigated', () => {
            isTablePageForced = false;

            requestAnimationFrame(() => applyMode(isTableWidthPage()));
        });

        document.addEventListener('DOMContentLoaded', () => {
            applyMode(isTableWidthPage());

            if (! document.body) {
                return;
            }

            new MutationObserver(() => {
                applyMode(isTableWidthPage());
            }).observe(document.body, { childList: true, subtree: true });
        });

        let resizeFrame = null;

        window.addEventListener('resize', () => {
            if (resizeFrame) {
                cancelAnimationFrame(resizeFrame);
            }

            resizeFrame = requestAnimationFrame(() => applyMode(isTableWidthPage()));
        });
    })();
</script>

<style>
    :root {
        --filament-content-max-width: {{ $contentMaxWidthCssValue }};
        --filament-record-list-pages-max-width: {{ $contentMaxWidthCssValue }};
    }

    html[data-filament-content-full-width="false"] .fi-main {
        max-width: var(--filament-content-max-width) !important;
    }

    html[data-filament-content-alignment="left"] .fi-main {
        margin-inline-start: 0;
        margin-inline-end: auto;
    }

    html[data-filament-content-alignment="left"][data-filament-table-width="contained"] .fi-main {
        margin-inline-start: 0;
        margin-inline-end: auto;
    }

    html[data-filament-content-full-width="true"] .fi-main {
        max-width: 100% !important;
    }

    html:not([data-filament-table-width-toggle-enabled="true"]) [data-filament-table-width-toggle],
    html:not([data-filament-table-width-can-toggle="true"]) [data-filament-table-width-toggle],
    html[data-filament-table-width="full"] [data-filament-table-width-toggle-mode="expand"],
    html:not([data-filament-table-width="full"]) [data-filament-table-width-toggle-mode="collapse"] {
        display: none;
    }

    html[data-filament-table-width="full"] .fi-main {
        max-width: 100% !important;
    }

    html[data-filament-table-width="contained"] .fi-main {
        max-width: var(--filament-record-list-pages-max-width) !important;
    }
</style>
