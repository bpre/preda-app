<div class="max-w-5xl">
    <ul role="list" class="space-y-16">

        @foreach($team as $person)
        <li id="{{ str_replace('.', '-', $person['img']) }}">
            <div class="space-x-2 lg:space-x-6 lg:flex xl:space-x-12">

                @if(file_exists("images/team/".image_from_email($person['email'])))
                    <div class="aspect-[3/4] w-full overflow-hidden rounded-lg lg:w-1/2 lg:shrink-0">
                        <img
                            src="/images/team/{{ image_from_email($person['email']) }}"
                            class="h-full w-full object-cover object-center"
                            alt="{{ $person['name'] }} - Zdjęcie portretowe"
                        />
                    </div>

                @else

                    <div class="aspect-[3/4] w-full rounded-lg bg-secondary-200/60 lg:w-1/2 lg:shrink-0">

                    </div>

                @endif

                <div>

                    <div class="mt-8">
                        <h3 class="mt-8 text-3xl font-semibold leading-6 border-accent-600">{{ $person['name'] }}</h3>
                        <div class="mt-2 mb-6 border-secondary-200">
                            <p class="text-accent-600">{{ $person['website_title'] }}</p>
                            <p class="mt-4">
                                <x-link.text href="mailto:{{ $person['email'] }}">{{ $person['email'] }}</x-link.text>
                            </p>
                        </div>
                    </div>

                    @if($person['website_description'])
                    <div class="prose border-none lg:border-t lg:pt-12">
                        {!! mos($person['website_description']) !!}
                    </div>
                    @endif

                </div>

            </div>

            <div class="mt-8 border-t lg:hidden {{ $loop->iteration != count($team) ?: ' hidden ' }}"></div>

        </li>
        @endforeach

    </ul>
</div>
