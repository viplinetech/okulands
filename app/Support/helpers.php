<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 * Short helpers for admin-editable page wording (see App\Support\SiteCopy). Use in Blade as {{ c('home.about.eyebrow') }}.
 */

use App\Support\SiteCopy;
use Illuminate\Support\HtmlString;

if (! function_exists('c')) {
    /** A piece of page wording as plain text (Blade escapes it). */
    function c(string $key): string
    {
        return (string) SiteCopy::value($key);
    }
}

if (! function_exists('ch')) {
    /** A headline where *words in asterisks* become the highlighted accent, e.g. ch('home.blog.title', 'italic text-glow'). */
    function ch(string $key, string $accentClass = 'italic text-glow'): HtmlString
    {
        $safe = e((string) SiteCopy::value($key));
        $html = preg_replace('/\*(.+?)\*/u', '<span class="'.e($accentClass).'">$1</span>', $safe);

        return new HtmlString(str_replace('|', '<br>', $html)); // "|" = line break
    }
}

if (! function_exists('cl')) {
    /** A list of one-line items. */
    function cl(string $key): array
    {
        return (array) SiteCopy::value($key);
    }
}

if (! function_exists('cp')) {
    /** A list of [title, description] pairs. */
    function cp(string $key): array
    {
        return array_map(fn ($row) => [(string) ($row[0] ?? $row['title'] ?? ''), (string) ($row[1] ?? $row['text'] ?? '')], (array) SiteCopy::value($key));
    }
}
