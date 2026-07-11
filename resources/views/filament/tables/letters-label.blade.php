@php
    $isRelationManager = $this instanceof \Filament\Resources\RelationManagers\RelationManager;
@endphp
<div class="text-sm">

    <div class="inline-flex items-center gap-2 fi-ta-text-item">

        @if($getRecord()->type == 'in')

            <span class="inline-flex items-center justify-center w-5 font-bold text-danger-600 shrink-0" title="Pismo przychodzące">↓</span>

        @else

            <span class="inline-flex items-center justify-center w-5 font-bold text-success-600 shrink-0" title="Pismo wychodzące">↑</span>

        @endif

        <div class="text-base font-bold">
            {{  ucfirst($getRecord()->label) }}
        </div>

    </div>

    @if(!$isRelationManager)
    <div class="text-gray-600 pl-7">
         @if($getRecord()->matter)
            <a href="{{ bp_pl_url($getRecord()->matter->category).'/'.$getRecord()->matter->id }}/edit" class="hover:underline">{{ $getRecord()->matter->label }}</a>
        @endif
    </div>
    @endif
</div>
