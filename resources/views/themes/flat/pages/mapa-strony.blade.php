<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :alternate="true"
        :full="true"
        :extraMarginTop="true"
    >

    </x-section::frame>

    <x-section::frame
        :full="true"
    >

    <ul class="max-w-3xl prose prose-slate">

        <li>
            <h3><a href="{{ route('homepage') }}">Strona główna</a></h3>
        </li>
        <li>
            <h3><a href="{{ route('oferta') }}">Oferta</a></h3>
            <ul>
                <li>
                    <a href="{{ route('kredyty-frankowe') }}">Kredyty frankowe</a>
                </li>
                <li>
                    <a href="{{ route('kredyty-euro') }}">Kredyty euro</a>
                </li>
                <li>
                    <a href="{{ route('oferta') }}">Wynagrodzenie kancelarii</a>
                </li>
            </ul>
        </li>
        <li>
            <h3><a href="{{ route('kancelaria') }}">O kancelarii</a></h3>
        </li>
        @php($offices = App\Models\Website\Office::query()->active()->ordered()->get())
        @if($offices->isNotEmpty())
            <li>
                <h3>Oddziały kancelarii</h3>
                <ul>
                    @foreach($offices as $office)
                        <li>
                            <a href="{{ route($office->slug) }}">Kancelaria {{ $office->city }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
        <li>
            <h3><a href="{{ route('kontakt') }}">Kontakt</a></h3>
        </li>
        <li>
            <h3><a href="{{ route('analiza') }}">Analiza umowy</a></h3>
        </li>
        <li>
            <h3><a href="{{ route('opinie') }}">Opinie naszych klientów</a></h3>
        </li>

        <li>
            <h3><a href="{{ route('faq') }}">Częste pytania</a></h3>
        </li>

        <li>
            <h3><a href="{{ route('splacony-kredyt') }}">Spłacony kredyt frankowy</a></h3>
        </li>

        <li>
            <h3><a href="{{ route('wyroki') }}">Nasze wyroki</a></h3>
                <ul>
                    @foreach(App\Models\Website\Sentence::where('is_published', 1)->orderBy('sentence_date', 'desc')->get(['label', 'slug']) as $sentence)
                    <li>
                        <a href="{{ url('wyrok/' . $sentence->slug) }}">{{ mos($sentence->label) }}</a>
                    </li>
                    @endforeach
                </ul>
        </li>

        <li>
            <h3><a href="{{ url('wyroki/splacone') }}">Wyroki w sprawch kredytów spłaconych</a></h3>
        </li>
        <li>
            <h3><a href="{{ route('wyroki-kredyty-euro') }}">Wyroki w sprawach kredytów euro</a></h3>
        </li>
        <li>
            <h3><a href="{{ route('wyroki-kredyty-frankowe') }}">Wyroki w sprawach kredytów frankowych</a></h3>
        </li>

        <li>
            <h3>Sądy</h3>
                <ul>
                    @foreach(App\Models\Website\Contact::where('category', 'Sąd')->whereNot('slug', '')->orderBy('organization')->get() as $sad)
                        @if(count($sad->court_published_sentences))
                        <li>
                            <a href="{{ url('/wyroki/sad/'.$sad['slug']) }}">{{ mos($sad['organization']) }}</a>
                        </li>
                        @endif
                    @endforeach
                </ul>
        </li>
        <li>
            <h3>Sędziowie</h3>
                <ul>
                    @foreach(App\Models\Website\Contact::where('category', 'Sędzia')->whereNot('slug', '')->orderBy('sort_name')->get() as $sedzia)
                        @if(count($sedzia->judge_published_sentences))
                        <li>
                            <a href="{{ url('/wyroki/sedzia/'.$sedzia['slug']) }}">{{ mos($sedzia['sort_name']) }}</a>
                        </li>
                        @endif
                    @endforeach
                </ul>
        </li>
        <li>
            <h3>Wyroki przeciwko bankom</h3>
                <ul>
                    @foreach(App\Models\Website\Bank::where('is_published', 1)->whereNot('slug', '')->orderBy('bank')->get() as $bank)
                        @if(count($bank->bank_published_sentences))
                        <li>
                            <a href="{{ url('/wyroki/bank/'.$bank['slug']) }}">Wyroki {{ mos($bank['label']) }}</a>
                        </li>
                        @endif
                    @endforeach
                </ul>
        </li>

        <li>
            <h3><a href="{{ route('klauzule-niedozwolone') }}">Klauzule niedozwolone</a></h3>
                <ul>
                    @foreach(App\Models\Website\Bank::where('is_published', 1)->orderBy('label')->pluck('slug', 'label')->all() as $label => $slug)
                        <li>
                            <a href="{{ url('bank/' . $slug) }}">{{ mos($label) }}</a>
                        </li>
                    @endforeach
                </ul>
        </li>
        <li>
            <h3><a href="{{ url('orzecznictwo') }}">Orzecznictwo w sprawach frankowych</a></h3>
                <ul>
                    @foreach(App\Models\Website\Post::where('category', 'orzecznictwo')->where('is_published', 1)->where('date', '<=', now())->pluck('slug', 'title')->all() as $title => $slug)
                    <li>
                        <a href="{{ url('orzecznictwo/' . $slug) }}">{{ mos($title) }}</a>
                    </li>
                    @endforeach
                </ul>
        </li>
        <li>
            <h3><a href="{{ url('blog') }}">Blog o kredytach frankowych</a></h3>
                <ul>
                    @foreach(App\Models\Website\Post::where('category', 'blog')->where('is_published', 1)->where('date', '<=', now())->pluck('slug', 'title')->all() as $title => $slug)
                    <li>
                        <a href="{{ route('post', $slug) }}">{{ mos($title) }}</a>
                    </li>
                    @endforeach
                </ul>
        </li>
        <li>
            <h3><a href="{{ route('gdzie-dzialamy') }}">Gdzie działamy?</a></h3>
            <ul>
                @foreach(App\Models\Website\City::where('is_published', 1)->orderBy('city')->pluck('slug', 'city')->all() as $city => $slug)
                <li>
                    <a href="{{ url('kredyty-frankowe-' . $slug) }}">Kredyty frankowe {{ mos($city) }}</a>
                </li>
                <li>
                    <a href="{{ url('kredyt-euro-kancelaria-' . $slug) }}">Kredyt w euro {{ mos($city) }}</a>
                </li>
                @endforeach
            </ul>
        </li>
        <li>
            <h3><a href="{{ route('polityka-prywatnosci') }}">Polityka prywatności</a></h3>
        </li>
    </ul>

    </x-section::frame>


    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
