<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        @php
            // $dir = str_replace('public', 'storage/app/', realpath($_SERVER["DOCUMENT_ROOT"]));
            $path = 'neostamp/'.substr($getRecord()->created_at, 0, 10).'/'.$getRecord()->label;
            $file = 'neoznaczki/'.substr($getRecord()->created_at, 0, 10).'/'.$getRecord()->label.'_znaczek.jpg';

        @endphp

        @if(Storage::disk('local')->exists($file))
            <img src="{{ getenv('APP_URL') }}/{{ $path }}" alt="Znaczek" />
        @endif

    </div>
</x-dynamic-component>
