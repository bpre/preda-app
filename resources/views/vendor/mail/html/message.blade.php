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
    $signatureHtml = implode('<br>', array_map(fn ($line) => e($line), $signatureLines));
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
<div style="font-size: 13px; line-height: 1.6; color: #6b7280; margin-top: 24px; text-align: left;">
{!! $signatureHtml !!}
</div>
@endif

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
