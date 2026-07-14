@php
    $html = \Filament\Forms\Components\RichEditor\RichContentRenderer::make($body)->toHtml();
    $html = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
    $html = preg_replace('/<\s*\/\s*(p|div|li|h[1-6])\s*>/i', "\n", $html);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/[ \t]+\n/", "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", trim($text));
@endphp
{{ $text }}
