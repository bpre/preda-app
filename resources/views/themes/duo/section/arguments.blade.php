@php
    $arguments = [
        [
            'title' => 'Specjalizacja',
            'content' => 'Specjalizujemy się w kredytach frankowych i kredytach powiązanych z innymi walutami obcymi. '.(config('app.specialization') ? 'Nie podejmujemy się prowadzenia innych spraw. ' : '').'Wygrywamy ze wszystkimi bankami.',
        ],
        [
            'title' => 'Ekspercka wiedza',
            'content' => 'Kredyty frankowe i kredyty w euro nie mają przed nami tajemnic. Na bieżąco śledzimy i analizujemy orzecznictwo, studiujemy branżowe publikacje i doskonalimy naszą argumentację.',
        ],
        [
            'title' => 'Doświadczenie',
            'content' => 'Kredytami powiązanymi z walutami zajmujemy się od 2017 r. Mimo coraz większej liczby wygranych spraw – nie spoczywamy na laurach. Cały czas rozwijamy naszą argumentację.',
        ],
        [
            'title' => 'Korzystne wyroki',
            'content' => 'Regularnie wygrywamy z bankami. Mamy na swoim koncie dziesiątki prawomocnych wygranych i rozliczonych umów. Liczba korzystnych wyroków naszej kancelarii rośnie z każdym miesiącem.',
        ],
        [
            'title' => 'Kompleksowa obsługa',
            'content' => 'Powierzając nam sprawę, o nic więcej nie musisz się martwić. Zajmiemy się wszystkim - od wyliczeń, przez proces sądowy, na rozliczeniu z bankiem kończąc.',
        ],
        [
            'title' => 'Bezpłatna analiza umowy kredytowej',
            'content' => 'Bezpłatnie przeanalizujemy Twoją umowę i wyjaśnimy, jakie są możliwości działania w Twojej sprawie.',
        ],
        [
            'title' => 'Jasne zasady rozliczeń',
            'content' => 'Wynagrodzenie kancelarii jasno określone w umowie. Z góry wiesz, jakie poniesiesz koszty. Częściowo możesz zapłacić po zakończeniu sprawy.',
        ],
        [
            'title' => 'Bezpośredni kontakt z adwokatem',
            'content' => 'W każdej chwili możesz skontaktować się bezpośrednio z adwokatem prowadzącym Twoją sprawę.',
        ],
    ];
@endphp

<div class="relative z-20 mt-8 not-prose">
    <div>
        <div class="gap-8 2xl:gap-x-5 2xl:gap-y-12">
            <dl class="space-y-8 2xl:grid 2xl:grid-cols-2 2xl:gap-x-8 2xl:gap-y-12 2xl:space-y-0">
                @foreach($arguments as $argument)
                    <div class="relative">
                        <dt>
                            <p class="mb-2 flex items-center gap-3 text-lg font-semibold leading-6 text-primary-950">
                                <span class="inline-flex size-8 min-h-8 min-w-8 flex-none basis-8 items-center justify-center overflow-hidden rounded-full bg-accent-600 p-0 text-sm font-semibold leading-none text-white">
                                    {{ $loop->iteration }}
                                </span>
                                <span>{{ $argument['title'] }}</span>
                            </p>
                        </dt>
                        <dd class="mt-2 py-2 pl-11 text-base prose">
                            {{ mos($argument['content']) }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</div>
