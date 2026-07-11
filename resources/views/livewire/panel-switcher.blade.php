@if (count($panels) > 1)
    <style>
        .preda-panel-switcher,
        .preda-panel-switcher .fi-sc,
        .preda-panel-switcher .fi-fo-field-wrp,
        .preda-panel-switcher .fi-fo-field-wrp > div,
        .preda-panel-switcher .fi-fo-select,
        .preda-panel-switcher .fi-input-wrp,
        .preda-panel-switcher .fi-select-input {
            width: 11rem;
        }
    </style>

    <div class="preda-panel-switcher" style="margin-inline-start: 100px; display: flex; width: 11rem; align-items: center;">
        {{ $this->form }}
    </div>
@endif
