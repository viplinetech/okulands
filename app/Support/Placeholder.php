<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

/**
 * The branded stand-in shown wherever a photo has not been uploaded yet: the OkuLands name and the section it belongs to.
 * It is generated as an inline image, so it needs no file and always loads.
 *
 * Picks one of a few on-brand navy/sky gradient variants, keyed off a seed (defaults to the
 * section text) so the same thing always renders the same colour, but a list of several (e.g.
 * blog posts without a cover photo yet) doesn't look identical card after card.
 */
class Placeholder
{
    /** @var list<array{from: string, via: string, to: string, accent: string}> */
    private const VARIANTS = [
        ['from' => '#0a1330', 'via' => '#14306e', 'to' => '#2a5fc4', 'accent' => '#5b9bf0'],
        ['from' => '#060c1f', 'via' => '#1c3170', 'to' => '#3d7de0', 'accent' => '#85b8fb'],
        ['from' => '#101c44', 'via' => '#20489a', 'to' => '#5b9bf0', 'accent' => '#d9ebff'],
        ['from' => '#0a1330', 'via' => '#1a3a7a', 'to' => '#2a4593', 'accent' => '#b3d4ff'],
        ['from' => '#15265a', 'via' => '#2a5fc4', 'to' => '#85b8fb', 'accent' => '#eef5ff'],
    ];

    public static function url(string $section, ?string $seed = null): string
    {
        $label = htmlspecialchars($section, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $variant = self::VARIANTS[crc32($seed ?? $section) % count(self::VARIANTS)];

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$variant['from']}"/>
      <stop offset="0.6" stop-color="{$variant['via']}"/>
      <stop offset="1" stop-color="{$variant['to']}"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#g)"/>
  <circle cx="1300" cy="180" r="420" fill="{$variant['accent']}" opacity="0.18"/>
  <text x="800" y="420" text-anchor="middle" font-family="Georgia, serif" font-size="150" fill="#ffffff" letter-spacing="6">OkuLands</text>
  <rect x="740" y="470" width="120" height="4" fill="{$variant['accent']}"/>
  <text x="800" y="550" text-anchor="middle" font-family="Arial, sans-serif" font-size="40" font-weight="700" fill="{$variant['accent']}" letter-spacing="8">{$label}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
