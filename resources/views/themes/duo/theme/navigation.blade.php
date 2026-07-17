<nav aria-label="Menu główne">
    <ul class="grid gap-1.5">
        @foreach($navigation as $navi)
            @if(($navi['type'] ?? null) === 'spacer')
                <li class="h-7" aria-hidden="true"></li>
                @continue
            @endif

            <li
                x-data="{ open: @js((bool) ($navi['active'] ?? false)) }"
            >
                @if(isset($navi['more']))
                    <button
                        type="button"
                        @class([
                            'flex w-full min-w-[min(300px,100%)] max-w-[min(max(50%,300px),100%)] items-center justify-between gap-3 rounded-xl px-4 py-2.5 text-left text-base font-semibold text-primary-800 transition-colors duration-150 hover:text-accent-600 focus-visible:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600',
                            '!text-accent-600' => $navi['active'] ?? false,
                        ])
                        @click="open = !open"
                        @keyup.window.escape="open = false"
                        :aria-expanded="open ? 'true' : 'false'"
                    >
                        <span>{{ $navi['text'] }}</span>
                        <x-icon.chevron-down class="size-4 shrink-0 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                    </button>

                    <div
                        x-cloak
                        x-show="open"
                        x-collapse
                        class="mt-1 grid min-w-[min(300px,100%)] max-w-[min(max(50%,300px),100%)] gap-1 pl-3"
                    >
                        @foreach($navi['more'] as $subnavi)
                            <a
                                href="{{ route($subnavi['route']) }}"
                                @class([
                                    'block rounded-lg px-4 py-1.5 text-sm leading-6 text-secondary-600 transition-colors duration-150 hover:text-accent-600 focus-visible:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600',
                                    '!text-accent-600' => $subnavi['active'] ?? false,
                                ])
                                @if($subnavi['active'] ?? false) aria-current="page" @endif
                            >
                                {{ $subnavi['text'] }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <a
                        href="{{ route($navi['route']) }}"
                        @class([
                            'flex w-full min-w-[min(300px,100%)] max-w-[min(max(50%,300px),100%)] items-center justify-between gap-3 rounded-xl px-4 py-2.5 text-left text-base font-semibold text-primary-800 transition-colors duration-150 hover:text-accent-600 focus-visible:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600',
                            '!text-accent-600' => $navi['active'] ?? false,
                        ])
                        @if($navi['active'] ?? false) aria-current="page" @endif
                    >
                        <span>{{ $navi['text'] }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
