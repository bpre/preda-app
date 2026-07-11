<div>

    <!-- Pole wyszukiwania -->
    @if(!$more)

        <div class="justify-end block w-full mb-10 lg:flex">
            <div class="lg:w-1/4">

                <x-filament::input.wrapper>
                    <x-filament::input
                        placeholder="Szukaj..."
                        type="text"
                        wire:model.live="search"
                    />
                </x-filament::input.wrapper>

            </div>
        </div>

    @endif

    <div>
        <div class="max-w-2xl">

            <div class="space-y-16 border-primary-200">

                @if(empty($posts))
                    <div class="border border-primary-200 p-4 bg-white">
                    Brak wyników.
                    </div>
                @endif

                @foreach($posts as $post)

                    <article class="flex max-w-2xl flex-col items-start justify-between">
                        <div class="flex items-center gap-x-4 text-xs">
                            <time datetime="2020-03-16" class="text-secondary-500">
                                {{  hd($post->date) }}
                            </time>
                        </div>
                        <div class="group relative">
                            <h3 class="mt-3 text-2xl/6 font-semibold group-hover:text-accent-600">
                                <a href="{{ url('/'.$post->category.'/'.$post->slug) }}">
                                    <span class="absolute inset-0"></span>
                                    {{  mos($post->title) }}
                                </a>
                            </h3>
                            <p class="mt-5 line-clamp-3 text-secondary-600">
                                {{  mos($post->excerpt) }}
                            </p>
                        </div>
                        @if($post->author)
                            <x-partial::author :author="$post->author" />
                        @endif
                    </article>

                @endforeach

            </div>

            @if($links && !$more)
                <div class="pagination-wrapper">
                    {{ $links }}
                </div>
            @endif

        </div>
    </div>
</div>
