<div>
    <div class="group relative flex gap-x-6 rounded-lg p-4 bg-secondary-100 ">
        <div class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-white">
            <x-icon name="heroicon-o-building-office-2" class="size-6 text-accent-600" />
        </div>
        <div>
            <h3 class="text-lg font-semibold">
                Oddział {{ $office->form_w }}
            </h3>
            <span class="absolute inset-0"></span>
            <p class="mt-1 text-secondary-600">
                <address class="mt-3 space-y-1 text-sm text-secondary-600 not-italic">
                    {!! nl2br($office->address) !!}
                </address>
            </p>
        </div>
    </div>

    <div class="prose prose-slate mt-12">
        {!!  $currency == 'EUR' ? str_replace('frankowego', 'w euro', $office->description) : $office->description !!}
    </div>
</div>
