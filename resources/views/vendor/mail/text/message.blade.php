@props([
    'mailSignatureGreeting' => 'Z wyrazami szacunku',
    'mailSignatureName' => null,
    'mailSignatureTitle' => null,
    'mailSignatureCompany' => config('mail.from.name') ?: 'PRĘDA Kancelaria Adwokacka',
])

@php
    $signatureLines = array_values(array_filter([
        $mailSignatureGreeting,
        $mailSignatureName,
        $mailSignatureTitle,
        $mailSignatureCompany,
    ], fn ($line) => filled($line)));
@endphp

<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ config('app.name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    @if (count($signatureLines))
{{ "\n\n" . implode("\n", $signatureLines) }}
    @endif

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset
</x-mail::layout>
