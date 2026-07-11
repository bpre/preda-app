@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'PREDA')
<img src="https://preda.info/images/logo.navy.png" class="logo" alt="PRĘDA Kancelaria Adwokacka">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
