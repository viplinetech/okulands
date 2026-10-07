<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

/**
 * The branded stand-in shown wherever a photo has not been uploaded yet: the OkuLands name and the section it belongs to.
 * It is generated as an inline image, so it needs no file and always loads.
 */
class Placeholder
{
    public static function url(string $section): string
    {
        $label = htmlspecialchars($section, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#0a1330"/>
      <stop offset="0.6" stop-color="#14306e"/>
      <stop offset="1" stop-color="#2a5fc4"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#g)"/>
  <circle cx="1300" cy="180" r="420" fill="#5b9bf0" opacity="0.18"/>
  <text x="800" y="420" text-anchor="middle" font-family="Georgia, serif" font-size="150" fill="#ffffff" letter-spacing="6">OkuLands</text>
  <rect x="740" y="470" width="120" height="4" fill="#5b9bf0"/>
  <text x="800" y="550" text-anchor="middle" font-family="Arial, sans-serif" font-size="40" font-weight="700" fill="#a9c8ff" letter-spacing="8">{$label}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
