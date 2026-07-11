@php
    $footerContent = trim((string) $slot);
@endphp

<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
@if ($footerContent !== '')
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
@else
<td align="center" style="font-size: 0; line-height: 0; padding: 0;">&nbsp;</td>
@endif
</tr>
</table>
</td>
</tr>
