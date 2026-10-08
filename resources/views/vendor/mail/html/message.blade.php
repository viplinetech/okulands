@php
    // The admin's uploaded logo, converted to PNG (email clients, Gmail's image proxy especially,
    // render the site's native WebP uploads as a blocky, dark artifact) and made absolute (email
    // clients cannot resolve a relative /storage/... path).
    $okuSettings = \App\Models\SiteSetting::current();
    $okuLogoPath = $okuSettings->logo_dark_path ?: $okuSettings->logo_path;
    $okuLogo = $okuLogoPath ? app(\App\Services\EmailImage::class)->pngUrl($okuLogoPath) : null;
@endphp
<x-mail::layout>
{{-- Header: the uploaded logo on the brand-navy band, or the site name if none is uploaded yet --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
@if ($okuLogo)
<img src="{{ $okuLogo }}" class="logo" alt="{{ $okuSettings->site_name }}">
@else
{{ $okuSettings->site_name }}
@endif
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $okuSettings->site_name }}. {{ __('All rights reserved.') }}
@if ($okuSettings->address)
<br>{{ $okuSettings->address }}
@endif
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
