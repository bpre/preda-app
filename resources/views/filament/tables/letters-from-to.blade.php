<div class="text-sm">
    @if($getRecord()->type == 'in')
        {{ $getRecord()->sender?->label }}
    @elseif($getRecord()->type == 'out')
        <ol>
        @foreach($getRecord()->recipients as $recipient)
            <li>{{ $recipient->label }}@unless($loop->last)<br>@endunless</li>
        @endforeach
        </ol>
    @endif
</div>
