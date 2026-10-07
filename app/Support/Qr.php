<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/** Inline SVG QR codes, generated locally (no third-party service ever sees the link). */
class Qr
{
    public static function svg(string $text, int $size = 220, int $margin = 1): string
    {
        $svg = (new Writer(new ImageRenderer(new RendererStyle($size, $margin), new SvgImageBackEnd)))->writeString($text);

        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);
    }
}
