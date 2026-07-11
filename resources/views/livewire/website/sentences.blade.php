<div>
    @if(!$more)
        @once
            <style>
                #sentence-filters .fi-select-input,
                #sentence-filters .fi-select-input-btn,
                #sentence-filters .fi-select-input-option,
                #sentence-filters .fi-select-input-option > span,
                #sentence-filters .fi-dropdown-list-item-label,
                #sentence-filters .fi-select-input-search-ctn .fi-input {
                    color: var(--color-primary-950);
                }

                #sentence-filters .fi-select-input-placeholder {
                    color: var(--color-secondary-400);
                }

                #sentence-filters .fi-dropdown-list-item.fi-color {
                    --text: var(--color-primary-950);
                    --hover-text: var(--color-primary-950);
                    --dark-text: var(--color-primary-950);
                    --dark-hover-text: var(--color-primary-950);
                }

                #sentence-filters .fi-select-input-option.fi-selected,
                #sentence-filters .fi-dropdown-list-item.fi-selected,
                #sentence-filters .fi-dropdown-list-item.fi-color.fi-selected {
                    background-color: var(--color-secondary-50);
                    color: var(--color-primary-950);
                }

                #sentence-filters .fi-dropdown-list-item.fi-selected .fi-dropdown-list-item-label {
                    color: var(--color-primary-950);
                }
            </style>
        @endonce

        <div class="mb-4 flex flex-wrap items-center justify-end gap-x-5 gap-y-2">
            <button
                type="button"
                wire:click="toggleFilters"
                class="border-0 p-0 text-sm font-semibold text-primary-900 underline decoration-secondary-300 underline-offset-4 transition-colors duration-200 hover:text-accent-600 hover:decoration-accent-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-600"
            >
                {{ $filtersVisible ? 'Ukryj filtry' : 'Pokaż filtry' }}
            </button>

            @if($hasActiveFilters)
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="border-0 p-0 text-sm font-semibold text-accent-600 underline decoration-accent-200 underline-offset-4 transition-colors duration-200 hover:text-primary-900 hover:decoration-secondary-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-600"
                >
                    Usuń zastosowane filtry ({{ $activeFiltersCount }})
                </button>
            @endif
        </div>

        @if($filtersVisible)
            <div id="sentence-filters" class="mb-10 rounded-[1.25rem] border border-secondary-200/80 bg-white p-4 shadow-sm sm:p-5">
                <div @class([
                    'grid gap-4',
                    'lg:grid-cols-4 lg:items-start' => $showSentenceFilters,
                ])>
                    <label class="fi-fo-field">
                        <span class="fi-fo-field-label">
                            <span class="fi-fo-field-label-content">Szukaj</span>
                        </span>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                placeholder="Sygnatura, sędzia, bank..."
                                type="text"
                                wire:model.live="search"
                            />
                        </x-filament::input.wrapper>
                    </label>

                    @if($showSentenceFilters)
                        <div class="min-w-0 lg:col-span-3">
                            {{ $this->filtersForm }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="mb-10"></div>
        @endif
    @endif

    <!-- Lista wyroków -->
    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">

        @if($sentences->isEmpty())
            <div class="rounded-[1.25rem] bg-white p-6 text-sm leading-6 text-secondary-700 shadow-sm">
            Brak wyników.
            </div>
        @endif

        @foreach($sentences as $sentence)
        @php
            $primaryMetricItems = [
                [
                    'label' => 'Sąd',
                    'value' => $sentence->court['organization'],
                    'href' => url('wyroki/sad/' . $sentence->court['slug']),
                    'icon' => 'home',
                ],
                [
                    'label' => 'Sygnatura',
                    'value' => $sentence->sign,
                    'href' => url('wyrok/' . $sentence['slug']),
                    'icon' => 'document-text',
                ],
                [
                    'label' => 'Sędzia',
                    'value' => $sentence->judge['label'],
                    'href' => url('wyroki/sedzia/' . $sentence->judge['slug']),
                    'icon' => 'user',
                ],
                [
                    'label' => 'Bank',
                    'value' => $sentence->bank['label'],
                    'href' => $sentence->bank?->is_published
                        ? url('wyroki/bank/' . $sentence->bank['slug'])
                        : null,
                    'icon' => 'building-office-2',
                ],
            ];

            $paidOffMetricItems = [
                [
                    'label' => 'Sąd',
                    'value' => $sentence->court['organization'],
                    'href' => url('wyroki/sad/' . $sentence->court['slug']),
                    'icon' => 'home',
                ],
                [
                    'label' => 'Sygnatura',
                    'value' => $sentence->sign,
                    'href' => url('wyrok/' . $sentence['slug']),
                    'icon' => 'document-text',
                ],
                [
                    'label' => 'Sędzia',
                    'value' => $sentence->judge['label'],
                    'href' => url('wyroki/sedzia/' . $sentence->judge['slug']),
                    'icon' => 'user',
                ],
                [
                    'label' => 'Rok spłaty kredytu',
                    'value' => $sentence->paid_off_year == '' ? 'Brak danych' : $sentence->paid_off_year,
                    'href' => null,
                    'icon' => 'calendar-days',
                ],
            ];

            $metricItems = $is_paid_off ? $paidOffMetricItems : $primaryMetricItems;
            $hasBenefitPanel = $is_paid_off && filled($sentence->credit_profit);
            $sentenceCardClasses = 'flex h-full flex-col gap-3 overflow-hidden rounded-[1.5rem] border border-secondary-200/80 bg-white p-3 shadow-sm';
            $sentenceSummaryClasses = 'overflow-hidden rounded-[1.25rem] px-4 pt-4 pb-3';
            $sentenceHeadingClasses = 'flex flex-col items-start gap-3 sm:-ml-1 sm:flex-row sm:items-stretch sm:gap-4';
            $sentenceIconClasses = 'hidden size-11 min-w-11 items-center justify-center rounded-full bg-accent-600 text-white sm:flex';
            $sentenceTitleClasses = 'text-xl leading-7 font-semibold tracking-tight text-primary-900 line-clamp-3 sm:line-clamp-none';
            $sentenceKickerClasses = 'text-[0.7rem] font-medium uppercase tracking-[0.22em] text-secondary-500';
            $benefitPanelClasses = 'overflow-hidden border-t-4 border-b border-solid border-rose-600 bg-white px-6 py-6 shadow-sm sm:px-7';
            $benefitKickerClasses = 'text-[0.7rem] font-medium uppercase tracking-[0.22em] text-secondary-500';
            $benefitValueClasses = 'mt-3 text-4xl leading-none font-semibold tracking-tight text-primary-950 sm:text-[2.4rem]';
            $benefitNoteClasses = 'mt-4 border-t border-secondary-200/80 pt-4 text-sm leading-6 text-secondary-600';
            $metricPanelClasses = 'overflow-hidden rounded-[1.25rem] bg-white';
            $metricListBorderClasses = $hasBenefitPanel ? 'border-b' : 'border-y';
            $metricListClasses = "grid {$metricListBorderClasses} border-secondary-200/80 px-4 pt-3 pb-3 gap-x-2 gap-y-3 sm:gap-y-4 lg:grid-cols-2";
            $metricItemClasses = 'flex items-start gap-3.5 px-0 py-0';
            $metricLabelClasses = 'text-[0.7rem] font-medium uppercase tracking-[0.22em] text-secondary-500';
            $metricValueClasses = 'mt-1 text-sm leading-5 font-semibold text-primary-900';
        @endphp

        <article class="{{ $sentenceCardClasses }}">
            <div class="{{ $sentenceSummaryClasses }}">
                <div class="{{ $sentenceHeadingClasses }}">
                    <div class="{{ $sentenceIconClasses }}">
                        <x-icon name="heroicon-o-document-text" class="size-4 sm:size-5" />
                    </div>

                    <div class="w-full min-w-0 sm:w-auto">
                        <h3 class="{{ $sentenceTitleClasses }}">
                            <a href="{{ url('wyrok/' . $sentence['slug']) }}" class="border-0 p-0 text-primary-900 no-underline transition-colors duration-300 hover:text-accent-600">
                                {{ mos($sentence->label) }}
                            </a>
                        </h3>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="{{ $sentenceKickerClasses }}">
                        Wyrok z {{ hd($sentence->sentence_date) }}
                    </div>
                    <div class="mt-2 text-sm leading-6 text-secondary-700">
                        {{ mos($sentence->excerpt) }}
                    </div>
                </div>
            </div>

            @if($hasBenefitPanel)
                <div class="{{ $benefitPanelClasses }}">
                    <div class="{{ $benefitKickerClasses }}">Korzyść z wygrania sprawy</div>
                    <div class="{{ $benefitValueClasses }}">
                        {{ pln_format($sentence->credit_profit) }}
                    </div>

                    @if(filled($sentence->credit_payoff))
                        <p class="{{ $benefitNoteClasses }}">
                            Kwota wypłaconego kredytu: {{ pln_format($sentence->credit_payoff) }}
                        </p>
                    @else
                        <p class="{{ $benefitNoteClasses }}">
                            Szacowana korzyść kredytobiorcy wynikająca z wygrania sprawy.
                        </p>
                    @endif
                </div>
            @endif

            <div class="{{ $metricPanelClasses }}">
                <dl @class([
                    $metricListClasses,
                    'lg:grid-cols-1' => $is_paid_off,
                ])>
                    @foreach($metricItems as $item)
                        <div @class([
                            $metricItemClasses,
                            'border-t border-secondary-200/80 pt-3 sm:pt-4' => !$loop->first,
                            'lg:border-t-0 lg:pt-0' => $loop->iteration === 2,
                        ])>
                            <div class="min-w-0">
                                <dt class="{{ $metricLabelClasses }}">{{ $item['label'] }}</dt>
                                <dd class="{{ $metricValueClasses }}">
                                    @if($item['href'])
                                        <a href="{{ $item['href'] }}" class="border-0 p-0 text-primary-900 no-underline transition-colors duration-300 hover:text-accent-600">
                                            {{ $item['value'] }}
                                        </a>
                                    @else
                                        <span>{{ $item['value'] }}</span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="mt-auto flex justify-end pt-2 pr-1 pb-1">
                <x-button.primary-link muted size="sm" href="{{ url('wyrok/' . $sentence['slug']) }}">
                    Szczegóły wyroku
                </x-button.primary-link>
            </div>
        </article>

        @endforeach
    </div>

    <!-- Linki paginacji (tylko gdy nie jest włączony tryb "more") -->
    @if(isset($links) && !$more)
        <div class="pagination-wrapper">
            {{ $links }}
        </div>
    @endif

    <!-- Przycisk "Pokaż więcej" (opcjonalnie) -->
    @if($more)
        <x-button.more-link href="{{  $more_url ? url($more_url) : route('wyroki') }}">
            Zobacz więcej wyroków
        </x-button.more-link>
    @endif
</div>
