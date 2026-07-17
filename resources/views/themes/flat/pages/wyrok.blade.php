<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$sentence->label"
        :subheading="$sentence->court->organization . ' - wyrok w sprawie ' .$sentence->sign"
        :useH1="true"
        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        :full="true"
        :alternate="true"
        :extraMarginTop="true"
    >
        <div class="max-w-3xl prose prose-slate text-lg">
            {!! mos($sentence->excerpt) !!}
        </div>

    </x-section::frame>

    <x-section::frame

        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        :full="true"
        :extraMarginTop="false"
        class="mt-12"
    >


    @php
        $metricItems = [
            [
                'label' => 'Sąd',
                'value' => $sentence->court['organization'],
                'href' => url('/wyroki/sad/' . $sentence->court['slug']),
                'icon' => 'home',
            ],
            [
                'label' => 'Sygnatura',
                'value' => $sentence->sign,
                'href' => url('/wyrok/' . $sentence->slug),
                'icon' => 'document-text',
            ],
            [
                'label' => 'Sędzia',
                'value' => $sentence->judge['label'],
                'href' => url('/wyroki/sedzia/' . $sentence->judge['slug']),
                'icon' => 'user',
            ],
            [
                'label' => 'Bank',
                'value' => $sentence->bank['label'],
                'href' => $sentence->bank?->is_published
                    ? url('/wyroki/bank/' . $sentence->bank['slug'])
                    : null,
                'icon' => 'building-office-2',
            ],
            [
                'label' => $sentence['instance'] == '1' ? 'Data pozwu' : 'Data apelacji',
                'value' => hd($sentence['instance'] == '1' ? $sentence['lawsuit_date'] : $sentence['appeal_date']),
                'href' => null,
                'icon' => 'calendar-days',
            ],
            [
                'label' => 'Data wyroku',
                'value' => hd($sentence['sentence_date']),
                'href' => null,
                'icon' => 'calendar-days',
            ],
            [
                'label' => 'Liczba rozpraw',
                'value' => $sentence['hearings'],
                'href' => null,
                'icon' => 'chat-bubble-left-right',
            ],
            [
                'label' => 'Wartość przedmiotu sporu',
                'value' => $sentence['wps'],
                'href' => null,
                'icon' => 'banknotes',
            ],
            [
                'label' => 'Żądanie',
                'value' => $sentence['claim'],
                'href' => null,
                'icon' => 'clipboard-document-list',
            ],
            [
                'label' => 'Wynik',
                'value' => $sentence['result'],
                'href' => null,
                'icon' => 'check-circle',
            ],
        ];

        $metricPrimaryItems = array_slice($metricItems, 0, 4);
        $metricSecondaryItems = array_slice($metricItems, 4);
        $hasCreditProfit = filled($sentence->credit_profit);
    @endphp

    <div class="grid grid-cols-1 gap-12 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] xl:gap-10">

        <div class="prose prose-slate">


            <h2>
                Wyrok {{
                    str_replace(array('Sąd', 'Okręgowy', 'Apelacyjny', 'Rejonowy', 'Najwyższy'), array('Sądu', 'Okręgowego', 'Apelacyjnego', 'Rejonowego', 'Najwyższego'), $sentence->court['organization'])
                }}: {{ mos($sentence->sign) }}
            </h2>

            <div>
                {!! mos($sentence->content) !!}
            </div>

            @if($sentence->files)
                <div class="not-prose mt-10 space-y-8">
                    @foreach($sentence->files as $file)
                        <div>
                            <img
                                src="/storage/{{ $file }}"
                                alt="Wyrok w sprawie {{ $sentence->sign }} - strona {{ $loop->iteration }}"
                                loading="lazy"
                                class="w-full rounded border border-secondary-200"
                            />
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        <aside class="space-y-5 self-start xl:sticky xl:top-28">

            @if($hasCreditProfit)
                <div class="ui-benefit-panel">
                    <div class="ui-benefit-kicker">Korzyść z wygrania sprawy</div>
                    <div class="ui-benefit-value">
                        {{ pln_format($sentence->credit_profit) }}
                    </div>

                    @if(filled($sentence->credit_payoff))
                        <p class="ui-benefit-note">
                            Kwota wypłaconego kredytu: {{ pln_format($sentence->credit_payoff) }}
                        </p>
                    @else
                        <p class="ui-benefit-note">
                            Szacowana korzyść kredytobiorcy wynikająca z wygrania sprawy.
                        </p>
                    @endif
                </div>
            @endif

            <div class="ui-metric-panel">
                <dl class="ui-metric-list ui-metric-list--bounded ui-metric-list--divided">
                    @foreach($metricPrimaryItems as $item)
                        <div class="ui-metric-item">
                            <div class="ui-metric-icon">
                                <x-icon name="heroicon-o-{{ $item['icon'] }}" class="size-5" />
                            </div>

                            <div class="min-w-0" role="presentation">
                                <dt class="ui-metric-label">{{ $item['label'] }}</dt>
                                <dd class="ui-metric-value">
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

            <div class="ui-metric-panel">
                <dl class="ui-metric-list ui-metric-list--bounded ui-metric-list--divided pt-2">
                    @foreach($metricSecondaryItems as $item)
                        <div class="ui-metric-item ui-metric-item-muted">
                            <div class="ui-metric-icon ui-metric-icon-muted">
                                <x-icon name="heroicon-o-{{ $item['icon'] }}" class="h-[1.125rem] w-[1.125rem]" />
                            </div>

                            <div class="min-w-0" role="presentation">
                                <dt class="ui-metric-label">{{ $item['label'] }}</dt>
                                <dd class="ui-metric-value ui-metric-value-muted">
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

                @if($sentence->parent)
                <div class="ui-metric-footer group">
                    <a href="{{ url('wyrok/' . $sentence->parent['slug']) }}" class="flex items-center gap-4 px-6 py-5 sm:px-7">
                        <div class="ui-metric-icon ui-metric-icon-muted">
                            <x-icon name="heroicon-o-document-text" class="h-[1.125rem] w-[1.125rem]" />
                        </div>

                        <div class="min-w-0">
                            <div class="ui-metric-label">Wyrok sądu I instancji</div>
                            <div class="mt-1 text-sm leading-6 font-medium text-secondary-700">
                                <span class="font-semibold text-primary-900">{{ $sentence->parent['sign'] }}</span>
                                <span class="ml-1">({{ $sentence->parent->court['organization'] }})</span>
                            </div>
                        </div>

                        <x-icon name="heroicon-s-arrow-right" class="ml-auto w-3 min-w-3 text-primary-900 transition-colors duration-300 group-hover:text-accent-600" />
                    </a>
                </div>
                @endif

            </div>

        </aside>

    </div>

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
