@php
    $panels = \App\Support\PanelRegistry::availableFor(auth()->user());
@endphp

@if (count($panels) > 1)
    <div class="me-3 flex items-center">
        <label for="preda-panel-switcher" class="sr-only">Panel</label>

        <select
            id="preda-panel-switcher"
            onchange="if (this.value) window.location.href = this.value"
            class="block rounded-lg border border-gray-300 bg-white py-1.5 pe-8 ps-3 text-sm font-medium text-gray-700 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
        >
            @foreach ($panels as $panel)
                <option value="{{ $panel['url'] }}" @selected($panel['active'])>
                    {{ $panel['label'] }}
                </option>
            @endforeach
        </select>
    </div>
@endif
