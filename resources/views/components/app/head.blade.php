{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    <head> contents shared by the Realtor and Admin layouts.
    Viewport is locked (no pinch zoom); private pages are never indexed or cached.
--}}
@props(['title', 'settings'])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#F5F8FC">
<meta name="format-detection" content="telephone=no">
<title>{{ $title }}</title>
@if ($settings->faviconUrl())
    <link rel="icon" href="{{ $settings->faviconUrl() }}">
@endif
<x-theme-boot-script :default="$settings->default_theme ?? 'light'" />
<script>document.documentElement.classList.add('js');</script>
@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/shell.js'])
