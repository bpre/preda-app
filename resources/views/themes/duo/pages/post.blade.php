<x-theme::app>

    <x-theme::header />

    <!-- Post -->
    <x-section::frame :heading="$post->title" :subheading="ucFirst($post->category)" :useH1="true" :displaySubheadingFirst="true" :full="true"
        :alternate="true">

        <x-partial::author :author="$post->author" />

        <div class="max-w-3xl prose prose-slate text-xl mt-12">
            {{ mos($post->excerpt) }}
        </div>



    </x-section::frame>


    <x-section::frame :subheadingIsPrimary="true" :displaySubheadingFirst="true" :full="true">
        <div class="prose prose-slate {{ substr($post->content, 0, 4) == '<h2>' ? '' : 'py-12' }}">

            <div>

                {!! mos($postHtml) !!}

            </div>

        </div>

        <div class="max-w-2xl mb-12 mx-auto px-6 py-3 text-secondary-400 text-right text-xs italic">
            Data publikacji: {{ hd($post->date) }}
            @if ($post->modified_at)
                <div class="mt-2">Ostatnia aktualizacja: {{ hd($post->modified_at) }}</div>
            @endif
        </div>




        @if ($post->author && false)
            <div class="max-w-2xl mb-2 mx-auto px-6 py-3 text-secondary-400 text-right text-xs italic">
                Autor artykułu:
            </div>

            <div class="mx-auto max-w-2xl bg-white rounded-md">

                <div
                    class=" hidden px-6 py-3 border-b rounded-t-md border-secondary-200 text-secondary-300 bg-secondary-50 text-right text-xs italic">
                    Autor artykułu
                </div>

                <div class="flex gap-6 align-top items-start p-6">

                    <div class="max-w-48">
                        <img src="/images/team/{{ image_from_email($post->author->email) }}" class="rounded-md"
                            alt="{{ $post->author->name }}">
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg">
                            {{ $post->author->name }}
                        </h3>
                        <span class="text-accent-600">{{ $post->author->website_title }}</span>

                        <div class="text-sm prose mt-4">
                            {!! mos($post->author->website_description) !!}
                        </div>
                    </div>

                </div>

            </div>
        @endif

    </x-section::frame>


    <x-section::frame heading="Zobacz inne {{ $post->category == 'orzecznictwo' ? 'orzeczenia' : 'wpisy na blogu' }}"
        subheading="{{ $post->category == 'orzecznictwo'
            ? 'Orzecznictwo w sprawach kredytów frankowych i kredytów w euro'
            : 'Blog o kredytach frankowych i kredytach w euro' }}"
        :displaySubheadingFirst="true" :full="true" :alternate="true">

        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            <div
                class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 border-primary-200 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                @foreach ($posts as $item)
                    <article class="flex max-w-xl flex-col items-start justify-between">
                        <div class="flex items-center gap-x-4 text-xs">
                            <time datetime="2020-03-16" class="text-secondary-500">{{ hd($item->date) }}</time>
                        </div>
                        <div class="group relative grow">
                            <h3 class="mt-3 text-lg/5 font-semibold">
                                <a href="{{ url('/' . $item->category . '/' . $item->slug) }}"
                                    class="group-hover:text-accent-600">
                                    <span class="absolute inset-0"></span>
                                    {{ mos($item->title) }}
                                </a>
                            </h3>
                            <p class="mt-5 line-clamp-3 text-sm/6 text-secondary-600">
                                {{ mos($item->excerpt) }}
                            </p>
                        </div>
                        @if ($item->author)
                            <div class="relative mt-8 flex items-center gap-x-4 justify-self-end">
                                <x-partial::author :author="$item->author" />
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

        </div>



    </x-section::frame>


    <x-website.element.cta />
    <x-theme::footer />

</x-theme::app>
