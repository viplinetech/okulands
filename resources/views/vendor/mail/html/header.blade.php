@props(['url'])
<tr>
<td align="center">
{{-- Same width as the white card below, so the navy band reads as its top half, not a separate block. --}}
<table class="header" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="header-cell" align="center">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
</table>
</td>
</tr>
