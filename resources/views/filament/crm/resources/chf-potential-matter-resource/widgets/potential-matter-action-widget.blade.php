<div @if (! $this->shouldDisplay()) style="display: none;" @endif>
    @if ($this->shouldDisplay())
        <x-filament-widgets::widget>
            <x-filament::section>
                <div style="display: grid; gap: 1rem;">
                    <div style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem;">
                        <div style="min-width: 16rem; flex: 1 1 22rem;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;">
                                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                                    {{ $this->widgetHeading() }}
                                </h2>

                                <span
                                    class="text-xs font-medium"
                                    style="{{ $this->stateBadgeStyle() }}"
                                >
                                    {{ $this->stateBadgeLabel() }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400" style="margin-top: .35rem;">
                                {{ $this->stateSummary() }}
                            </p>
                        </div>

                        @if ($this->hasActionControls())
                            <div style="display: flex; flex: 0 1 auto; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .75rem;">
                                @if ($this->hasClientMessageActions())
                                    <div class="preda-potential-matter-action-select" style="width: 28rem; max-width: 100%;">
                                        <style>
                                            .preda-potential-matter-action-select .fi-sc,
                                            .preda-potential-matter-action-select .fi-fo-field-wrp,
                                            .preda-potential-matter-action-select .fi-fo-field-wrp > div,
                                            .preda-potential-matter-action-select .fi-fo-select,
                                            .preda-potential-matter-action-select .fi-input-wrp {
                                                width: 100%;
                                            }
                                        </style>

                                        {{ $this->form }}
                                    </div>

                                    {{ $this->sendClientMessageAction }}
                                @endif

                                @if ($this->canArchivePotentialMatter())
                                    {{ $this->archivePotentialMatterAction }}
                                @endif
                            </div>
                        @endif
                    </div>

                    <dl
                        class="text-sm"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); gap: .75rem 1.25rem;"
                    >
                        @foreach ($this->stateDetails() as $label => $value)
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {{ $label }}
                                </dt>
                                <dd class="text-gray-800 dark:text-gray-200">
                                    {{ $value }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <x-filament-actions::modals />
            </x-filament::section>
        </x-filament-widgets::widget>
    @endif
</div>
