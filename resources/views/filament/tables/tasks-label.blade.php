<div class="text-sm">

    <div class="fi-ta-text-item inline-flex items-center gap-1.5">

        <div class="text-base font-bold">
            {{  ucfirst($getRecord()->label) }}
        </div>

    </div>

    <div class="text-gray-600">
         @if($getRecord()->matter)
            <a href="{{ bp_pl_url($getRecord()->matter->category).'/'.$getRecord()->matter->id }}/edit" class="hover:underline">{{ $getRecord()->matter->label }}</a>
        @endif
    </div>
</div>
