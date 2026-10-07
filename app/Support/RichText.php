<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Formatted text written in the admin editor.
 *
 * SECURITY: clean() runs on every save with a strict allow-list (no scripts, styles, images, iframes,
 * event handlers or javascript: links), so what is stored is safe to print unescaped.
 * html() also renders older plain-text content (blank line = new paragraph) without losing anything.
 */
class RichText
{
    private const ALLOWED = 'p[class],br,strong,b,em,i,u,s,strike,h2,h3,h4,ul,ol,li[class],blockquote,a[href|target|rel]';

    private const CLASSES = ['ql-align-center', 'ql-align-right', 'ql-align-justify', 'ql-indent-1', 'ql-indent-2', 'ql-indent-3'];

    private static ?HTMLPurifier $purifier = null;

    /** Sanitise editor output for storage. An effectively empty editor becomes null. */
    public static function clean(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        // A plain-text submission (no editor / older content): keep it, the renderer handles it.
        if (! preg_match('/<[a-z][^>]*>/i', $html)) {
            return $html;
        }

        $clean = trim(self::purifier()->purify($html));

        // Blank lines at the very start or end are just editor padding.
        $blank = '<p><br></p>|<p><br />\s*</p>|<p>\s*</p>|<p>&nbsp;</p>';
        $clean = trim((string) preg_replace('#^(\s*('.$blank.'))+|(\s*('.$blank.'))+\s*$#i', '', $clean));

        return self::isEmpty($clean) ? null : $clean;
    }

    /** True when there is no visible text (e.g. Quill's empty "<p><br></p>"). */
    public static function isEmpty(?string $html): bool
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5)) === '' && ! preg_match('/<(img|hr)\b/i', (string) $html);
    }

    /** Safe HTML ready to print with {!! !!}. Handles both editor HTML and legacy plain text. */
    public static function html(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/<(p|h[2-4]|ul|ol|blockquote)[\s>\/]/i', $value)) {
            return $value;
        }

        return collect(preg_split('/\R{2,}/', $value))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => '<p>'.nl2br(e($p), false).'</p>')
            ->implode("\n");
    }

    /** Plain text for meta descriptions, search indexes and structured data. */
    public static function text(?string $value): string
    {
        $spaced = preg_replace('/<\/(p|h[2-4]|li|blockquote)>|<br\s*\/?>/i', ' ', (string) $value);

        return trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $spaced), ENT_QUOTES | ENT_HTML5)));
    }

    /**
     * The opening paragraph as plain text: what a card or search result should show. Unlike text(), it does not
     * run headings and list items together into one run-on sentence.
     */
    public static function lead(?string $value, int $limit = 160): string
    {
        $html = self::html($value);

        if (preg_match_all('#<p\b[^>]*>(.*?)</p>#is', $html, $m)) {
            foreach ($m[1] as $paragraph) {
                $plain = self::text($paragraph);
                if ($plain !== '') {
                    return Str::limit($plain, $limit);
                }
            }
        }

        return Str::limit(self::text($html), $limit);
    }

    public static function excerpt(?string $value, int $limit = 160): string
    {
        return Str::limit(self::text($value), $limit);
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier) {
            return self::$purifier;
        }

        $cache = storage_path('framework/cache/purifier');
        File::ensureDirectoryExists($cache);

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cache);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', self::ALLOWED);
        $config->set('Attr.AllowedClasses', self::CLASSES);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('AutoFormat.RemoveEmpty', false);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', false);

        return self::$purifier = new HTMLPurifier($config);
    }
}
