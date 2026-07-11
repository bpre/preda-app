<div>

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

    <ul role="list" class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">


        @if(!count($banks))
            <div class="border border-primary-200 p-4 bg-white">
            Brak wyników.
            </div>
        @endif

        @foreach($banks as $bank)
            <li class="flex flex-col border border-primary-200 col-span-1 divide-y divide-primary-200 rounded-lg bg-primary-50 hover:bg-white hover:shadow-sm">
                <a href="{{  route('bank', $bank->slug) }}">
                <div class="flex w-full items-center justify-between space-x-4 p-4 sm:space-x-6 sm:p-6">
                    <div class="flex-1 truncate">
                        <div class="flex items-center space-x-3">
                        <h3 class="truncate text-base font-medium sm:text-lg">
                            {{ $bank->label }}
                        </h3>

                        </div>

                    </div>
                <x-icon name="heroicon-o-building-office-2" class="w-6 text-accent-600 sm:w-8" />
                </div>
                </a>
            </li>
        @endforeach
    </ul>

</div>
