<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Website uploads are stored as WebP (fast, tiny, great for the site itself), but WebP support
 * in email clients is inconsistent — Gmail's own image proxy in particular re-encodes it badly,
 * rendering it as a blocky, dark artifact instead of the real logo. A branding (logo/favicon)
 * upload saves a lossless PNG sibling for exactly this reason (see ImageProcessor::store()); this
 * just resolves to that, converting from the WebP on the fly only as a fallback for an upload
 * made before that existed.
 */
class EmailImage
{
    /** @return string|null absolute URL to a PNG-safe version of the given storage path, or null if it can't be made */
    public function pngUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (! str_ends_with($path, '.webp')) {
            return url(Storage::url($path));
        }

        $pngPath = substr($path, 0, -5).'.png';

        if (! Storage::disk('public')->exists($pngPath)) {
            $source = Storage::disk('public')->path($path);
            $image = @imagecreatefromwebp($source);

            if (! $image) {
                return url(Storage::url($path)); // fall back to the original rather than nothing
            }

            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            imagepng($image);
            $binary = ob_get_clean();
            imagedestroy($image);

            Storage::disk('public')->put($pngPath, $binary);
        }

        return url(Storage::url($pngPath));
    }
}
