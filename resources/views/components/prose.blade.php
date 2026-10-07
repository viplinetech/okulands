{{-- Formatted text from the admin editor (also renders older plain text). Colour/size come from the parent. --}}
@props(['html' => ''])
<div {{ $attributes->class(['prose-oku']) }}>{!! \App\Support\RichText::html($html) !!}</div>
