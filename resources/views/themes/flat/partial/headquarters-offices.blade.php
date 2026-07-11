<div class="grid grid-cols-1 lg:grid-cols-2 text-secondary-700">

    <div>
        <h3 class="text-2xl font-semibold my-4 text-accent-600">Siedziba kancelarii</h3>

        <div class="flex gap-x-6 rounded-lg p-4">
            <div class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-secondary-100">
                <x-icon name="heroicon-o-home" class="size-6 text-secondary-400" />
            </div>
            <div>
                <x-website.element.headquarters />
            </div>
        </div>

        <x-card.navigation
            heading="666 580 580"
            href="tel:+48666580580"
            icon="phone"
        >
            <address class="mt-3 space-y-1 text-sm text-secondary-600 not-italic">
                Zadzwoń do sekretariatu kancelarii.
            </address>
        </x-card.navigation>

        <x-card.navigation
            heading="kancelaria@preda.info"
            href="mailto:kancelaria@preda.info"
            icon="envelope"
        >
            <address class="mt-3 space-y-1 text-sm text-secondary-600 not-italic">
                Napisz do nas.
            </address>
        </x-card.navigation>

    </div>

    <div>
        <h3 class="text-2xl font-semibold my-4 text-accent-600">Oddziały kancelarii</h3>
        <x-partial::offices />
    </div>

</div>