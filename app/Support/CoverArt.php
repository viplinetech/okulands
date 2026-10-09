<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A designed cover image for an AI-generated blog post: an on-brand navy/sky gradient, a
 * decorative shape, and the post's own title set as real text. Saved as a real SVG file (so it
 * needs no font bundled on the server and no rasterising), in the same place a manually uploaded
 * cover photo would go, so the rest of the app treats it identically.
 */
class CoverArt
{
    /** @var list<array{from: string, via: string, to: string, accent: string, shape: string}> */
    private const VARIANTS = [
        ['from' => '#0a1330', 'via' => '#14306e', 'to' => '#2a5fc4', 'accent' => '#5b9bf0', 'shape' => 'circle-right'],
        ['from' => '#060c1f', 'via' => '#1c3170', 'to' => '#3d7de0', 'accent' => '#85b8fb', 'shape' => 'circle-left'],
        ['from' => '#101c44', 'via' => '#20489a', 'to' => '#5b9bf0', 'accent' => '#d9ebff', 'shape' => 'band'],
        ['from' => '#0a1330', 'via' => '#1a3a7a', 'to' => '#2a4593', 'accent' => '#b3d4ff', 'shape' => 'circle-right'],
        ['from' => '#15265a', 'via' => '#2a5fc4', 'to' => '#85b8fb', 'accent' => '#eef5ff', 'shape' => 'band'],
        ['from' => '#060c1f', 'via' => '#101c44', 'to' => '#20489a', 'accent' => '#5b9bf0', 'shape' => 'circle-left'],
    ];

    /** @return string path on the public disk, e.g. "uploads/blog/6f1c….svg" */
    public static function generateFor(string $title, string $seed): string
    {
        $variant = self::VARIANTS[crc32($seed) % count(self::VARIANTS)];
        $lines = self::wrap($title, 20);
        $titleBlock = self::titleLines($lines);
        $shape = match ($variant['shape']) {
            'circle-left' => '<circle cx="180" cy="750" r="380" fill="'.$variant['accent'].'" opacity="0.14"/>',
            'band' => '<rect x="0" y="0" width="1600" height="900" fill="none"/><path d="M0 900 L1600 500 L1600 900 Z" fill="'.$variant['accent'].'" opacity="0.1"/>',
            default => '<circle cx="1360" cy="140" r="360" fill="'.$variant['accent'].'" opacity="0.16"/>',
        };

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$variant['from']}"/>
      <stop offset="0.6" stop-color="{$variant['via']}"/>
      <stop offset="1" stop-color="{$variant['to']}"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#g)"/>
  {$shape}
  <rect x="100" y="100" width="56" height="6" fill="{$variant['accent']}"/>
  <text x="100" y="80" font-family="Arial, sans-serif" font-size="30" font-weight="700" fill="{$variant['accent']}" letter-spacing="6">OKU LANDS &amp; PROPERTIES</text>
  {$titleBlock}
</svg>
SVG;

        $path = 'uploads/blog/'.Str::uuid().'.svg';
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    /** @return list<string> */
    private static function wrap(string $title, int $maxChars): array
    {
        $words = preg_split('/\s+/', trim($title)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;
            if (mb_strlen($candidate) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return array_slice($lines, 0, 4);
    }

    private static function titleLines(array $lines): string
    {
        $startY = 900 - 140 - (count($lines) - 1) * 90;
        $out = '';

        foreach ($lines as $i => $line) {
            $escaped = htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $y = $startY + $i * 90;
            $out .= '<text x="100" y="'.$y.'" font-family="Georgia, serif" font-size="72" font-weight="400" fill="#ffffff">'.$escaped.'</text>';
        }

        return $out;
    }
}
