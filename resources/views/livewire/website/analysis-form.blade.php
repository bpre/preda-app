@php
    $isSidebar = ($context ?? 'page') === 'sidebar';
@endphp

<div
    x-data="{
        syncAttribution() {
            const attribution = window.PredaLeadAttribution?.get?.() ?? {};

            this.$wire.set('attributionData', attribution, false);
        },
    }"
    x-init="
        syncAttribution();
        window.addEventListener('preda:lead-attribution-updated', () => syncAttribution());
    "
    x-on:submit.capture="syncAttribution()"
    x-on:analysis-sidebar-reset-form.window="$wire.resetAfterSidebarCompletion()"
>

    @once
    <script>
        window.addEventListener('gtm', () => {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'gtm.form_sent',
                'form_type': 'analiza'
            });
        });

        window.addEventListener('analysis-form-event', (event) => {
            const eventName = event.detail?.eventName;

            if (! eventName) {
                return;
            }

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': eventName,
                'form_type': 'analiza'
            });
        });
    </script>
    <style>
        #form-analysis-sidebar {
            container-type: inline-size;
        }

        :is(#form-analysis, #form-analysis-sidebar, #form-analysis-upload, #form-analysis-upload-sidebar) {
            --primary-50: var(--color-accent-50);
            --primary-100: var(--color-accent-100);
            --primary-200: var(--color-accent-200);
            --primary-300: var(--color-accent-300);
            --primary-400: var(--color-accent-400);
            --primary-500: var(--color-accent-500);
            --primary-600: var(--color-accent-600);
            --primary-700: var(--color-accent-700);
            --primary-800: var(--color-accent-800);
            --primary-900: var(--color-accent-900);
            --primary-950: var(--color-accent-950);
        }

        #form-analysis-sidebar .fi-section-content.fi-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        #form-analysis-sidebar .fi-section > .fi-section-header {
            padding-block: 0.625rem;
        }

        #form-analysis .fi-select-input,
        #form-analysis-sidebar .fi-select-input,
        #form-analysis .fi-select-input-btn,
        #form-analysis-sidebar .fi-select-input-btn,
        #form-analysis .fi-select-input-option,
        #form-analysis-sidebar .fi-select-input-option,
        #form-analysis .fi-select-input-option > span,
        #form-analysis-sidebar .fi-select-input-option > span {
            color: var(--color-primary-950);
        }

        #form-analysis .fi-select-input-placeholder,
        #form-analysis-sidebar .fi-select-input-placeholder {
            color: var(--color-secondary-400);
        }

        #form-analysis .fi-select-input-option.fi-selected,
        #form-analysis-sidebar .fi-select-input-option.fi-selected {
            background-color: var(--color-secondary-50);
            color: var(--color-primary-950);
        }

        :is(#form-analysis, #form-analysis-sidebar)
            .fi-sc-wizard-header-step:not(.fi-active):not(.fi-completed)
            .fi-sc-wizard-header-step-label {
            color: var(--color-secondary-500);
        }

        @container (min-width: 300px) {
            #form-analysis-sidebar .fi-section-content.fi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .duo-analysis-sidebar-panel:has([data-analysis-sidebar-step]) [data-analysis-sidebar-heading-copy] {
            display: none;
        }

        .duo-analysis-sidebar-panel:has([data-analysis-sidebar-step]) [data-analysis-sidebar-heading] {
            justify-content: flex-end;
        }
    </style>
    @endonce


    <div @class([
        'col-span-3 py-12 xl:pb-24' => ! $isSidebar,
        'w-full' => $isSidebar,
    ])>

    @if($sent == true)

        @if($isSidebar)
            <span class="hidden" data-analysis-sidebar-step="{{ $this->hasContract && ! $this->documentsUploaded && ! $this->documentsSkipped ? 'upload' : 'complete' }}"></span>
        @endif

        <div @class([
            'animate-in fade-in zoom-in',
            'border border-primary-200 bg-primary-50 rounded-md' => ! $isSidebar,
            'max-w-3xl p-12' => ! $isSidebar,
            'p-0' => $isSidebar,
        ])>

            @unless($this->documentsSkipped)
                <div @class([
                    'flex',
                    'gap-8' => ! $isSidebar,
                    'items-center gap-4' => $isSidebar,
                ])>
                    <div>
                        <x-icon
                            name="heroicon-s-check-circle"
                            @class([
                                'fill-green-500',
                                'w-24' => ! $isSidebar,
                                'w-14' => $isSidebar,
                            ])
                        />
                    </div>

                    <div>

                        <h3 @class([
                            'font-bold tracking-tight',
                            'mt-2 text-xl sm:text-4xl' => ! $isSidebar,
                            'text-xl leading-7' => $isSidebar,
                        ])>
                            {{ $this->documentsUploaded ? 'Dokumenty zostały załączone.' : 'Dziękujemy. Twoje zgłoszenie zostało przyjęte.' }}
                        </h3>

                    </div>
                </div>
            @endunless

            @if($this->hasContract)
                @if($this->documentsUploaded)
                    <div @class([
                        'mt-8 rounded-md border border-secondary-200 bg-white p-5 text-secondary-700',
                        'text-sm leading-6' => $isSidebar,
                        'text-base leading-7' => ! $isSidebar,
                    ])>
                        Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia i zapoznaniu się z dokumentami.
                    </div>
                @elseif($this->documentsSkipped)
                    <div @class([
                        'rounded-md border border-secondary-200 bg-white p-5 text-secondary-700',
                        'text-sm leading-6' => $isSidebar,
                        'text-base leading-7' => ! $isSidebar,
                    ])>
                        Dziękujemy. Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.
                    </div>
                @else
                    <form
                        wire:submit="uploadDocuments"
                        @class([
                            'border-t border-primary-200 pt-8',
                            'mt-8' => ! $isSidebar,
                            'mt-6' => $isSidebar,
                            'w-full' => $isSidebar,
                        ])
                        id="{{ $isSidebar ? 'form-analysis-upload-sidebar' : 'form-analysis-upload' }}"
                    >
                        {{ $this->uploadForm }}

                        <div @class([
                            'mt-8 flex',
                            'justify-end' => ! $isSidebar,
                        ])>
                            <x-button.primary-link
                                as="button"
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="uploadDocuments"
                                @class([
                                    'disabled:pointer-events-none disabled:opacity-70',
                                    'w-full' => $isSidebar,
                                ])
                            >
                                Załącz dokumenty
                            </x-button.primary-link>
                        </div>
                    </form>

                    <button
                        type="button"
                        wire:click="skipDocuments"
                        class="mt-5 ml-auto block w-fit text-sm font-semibold text-secondary-600 transition-colors duration-200 hover:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                    >
                        Pomiń na razie ten krok
                    </button>
                @endif
            @else
                <div @class([
                    'mt-8 rounded-md border border-secondary-200 bg-white p-5 text-secondary-700',
                    'text-sm leading-6' => $isSidebar,
                    'text-base leading-7' => ! $isSidebar,
                ])>
                    Skontaktujemy się z Tobą po przeanalizowaniu zgłoszenia.
                </div>
            @endif

        </div>


    @else

        <div class="w-full">
            <div class="">
                <form
                    wire:submit="create"
                    @class([
                        'max-w-5xl' => ! $isSidebar,
                        'w-full' => $isSidebar,
                    ])
                    id="{{ $isSidebar ? 'form-analysis-sidebar' : 'form-analysis' }}"
                >
                    {{ $this->form }}
                </form>
            </div>
        </div>

    @endif

    </div>

</div>
