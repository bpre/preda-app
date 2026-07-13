<div @if (! $this->shouldDisplay()) style="display: none;" @endif>
    @if ($this->shouldDisplay())
        <x-filament-widgets::widget>
            <x-filament::section>
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: .75rem;">
                    <h2
                        class="text-base font-semibold text-gray-950 dark:text-white"
                        style="flex: 0 0 auto; min-width: 190px; white-space: nowrap;"
                    >
                        Podejmij działanie
                    </h2>

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
                </div>

                <x-filament-actions::modals />
            </x-filament::section>
        </x-filament-widgets::widget>
    @endif
</div>
