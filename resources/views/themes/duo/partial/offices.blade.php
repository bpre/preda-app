
<h3 class="text-2xl font-semibold my-4 text-accent-600">
    {{  count($offices)>1 ? 'Oddziały kancelarii' : 'Oddział kancelarii' }}
</h3>

<div class="grid grid-cols-1 gap-10 text-base lg:grid-cols-2">

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:col-span-2 lg:gap-8">

    @foreach($offices as $office)
        <x-card.navigation
            :heading="$office->city"
            :href="route($office->slug)"
            icon="building-office-2"
        >
            <address class="text-secondary-600 text-sm not-italic">
                {!!  nl2br($office->address) !!}
            </address>
        </x-card.navigation>

    @endforeach

    </div>
</div>
