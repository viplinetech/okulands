<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Safe image uploads. Phone photos arrive at 5-10MB; every upload is verified as a real raster
 * image, rotated upright, resized and re-encoded as WebP, so the site stays fast and a disguised
 * file (script, SVG, polyglot) can never be stored or served.
 */
class ImageProcessor
{
    public const MAX_BYTES = 12 * 1024 * 1024;
    public const MAX_PIXELS = 40_000_000;
    private const ALLOWED = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];

    /**
     * @return string path relative to the public disk, e.g. "uploads/gallery/6f1c….webp"
     *
     * @throws \InvalidArgumentException when the file is not an acceptable image
     */
    public function store(UploadedFile $file, string $folder, int $maxWidth = 1920, int $quality = 80): string
    {
        $path = $file->getRealPath();

        if (! $file->isValid() || ! $path || $file->getSize() > self::MAX_BYTES) {
            throw new \InvalidArgumentException('The image is missing, too large (max 12MB) or failed to upload.');
        }

        $info = @getimagesize($path);
        if (! $info || ! in_array($info[2], self::ALLOWED, true) || ($info[0] * $info[1]) > self::MAX_PIXELS) {
            throw new \InvalidArgumentException('Please upload a JPG, PNG, WebP or GIF image.');
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (! $source) {
            throw new \InvalidArgumentException('That image could not be read. Try exporting it again.');
        }

        $source = $this->orient($source, $path, $info[2]);

        // Logos are often exported with uneven transparent padding (more space on one side than
        // the other), which makes them look off-centre wherever they're placed. Trim to the
        // actual artwork, with a small even margin, so the file itself is properly centred.
        if ($folder === 'branding' && $info[2] !== IMAGETYPE_JPEG) {
            $source = $this->trimTransparentPadding($source);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width > $maxWidth) {
            $resized = imagescale($source, $maxWidth, (int) round($height * $maxWidth / $width), IMG_BICUBIC);
            if ($resized) {
                imagedestroy($source);
                $source = $resized;
            }
        }

        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        $uuid = Str::uuid();
        $relative = 'uploads/'.trim($folder, '/').'/'.$uuid.'.webp';

        // Branding (logo/favicon) also gets a lossless PNG saved alongside, straight from this same
        // trimmed/oriented source. Email clients render the site's lossy WebP badly (Gmail's image
        // proxy in particular turns it into a blocky, dark artifact) and need a universally
        // supported format — generating that PNG now, from the original pixels, avoids a lossy
        // WebP-to-PNG round trip later for no reason.
        if ($folder === 'branding') {
            ob_start();
            imagepng($source);
            Storage::disk('public')->put('uploads/'.trim($folder, '/').'/'.$uuid.'.png', ob_get_clean());
        }

        ob_start();
        imagewebp($source, null, $quality);
        $binary = ob_get_clean();
        imagedestroy($source);

        Storage::disk('public')->put($relative, $binary);

        return $relative;
    }

    /**
     * A favicon is shown at 16-32px in a browser tab: any fine detail (thin outlines, small
     * cutouts) just blurs into an unrecognisable blob at that size. This trims to the artwork,
     * bolds the strokes so they survive shrinking, adds a little even breathing room, and saves
     * as PNG (favicon support for WebP is inconsistent across browsers; PNG is universal).
     *
     * @throws \InvalidArgumentException when the file is not an acceptable image
     */
    public function storeFavicon(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        if (! $file->isValid() || ! $path || $file->getSize() > self::MAX_BYTES) {
            throw new \InvalidArgumentException('The image is missing, too large (max 12MB) or failed to upload.');
        }

        $info = @getimagesize($path);
        if (! $info || ! in_array($info[2], self::ALLOWED, true) || ($info[0] * $info[1]) > self::MAX_PIXELS) {
            throw new \InvalidArgumentException('Please upload a JPG, PNG, WebP or GIF image.');
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (! $source) {
            throw new \InvalidArgumentException('That image could not be read. Try exporting it again.');
        }

        $source = $this->orient($source, $path, $info[2]);
        if ($info[2] !== IMAGETYPE_JPEG) {
            $source = $this->trimTransparentPadding($source);
        }
        $source = $this->boldenForSmallSize($source);

        ob_start();
        imagepng($source);
        $binary = ob_get_clean();
        imagedestroy($source);

        $relative = 'uploads/branding/'.Str::uuid().'.png';
        Storage::disk('public')->put($relative, $binary);

        return $relative;
    }

    /**
     * Thickens every stroke (each pixel takes on the most-opaque colour within a small radius of
     * itself), then adds a touch of padding. Done at a fixed working size so the effect scales
     * sensibly regardless of how large the original upload was.
     */
    private function boldenForSmallSize(\GdImage $image, int $workSize = 256, int $radius = 3): \GdImage
    {
        $resized = imagecreatetruecolor($workSize, $workSize);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $workSize, $workSize, imagesx($image), imagesy($image));
        imagedestroy($image);

        $bold = imagecreatetruecolor($workSize, $workSize);
        imagealphablending($bold, false);
        imagesavealpha($bold, true);
        $transparent2 = imagecolorallocatealpha($bold, 0, 0, 0, 127);
        imagefill($bold, 0, 0, $transparent2);

        for ($y = 0; $y < $workSize; $y++) {
            for ($x = 0; $x < $workSize; $x++) {
                $bestAlpha = 127;
                $bestColor = null;
                for ($dy = -$radius; $dy <= $radius; $dy++) {
                    $ny = $y + $dy;
                    if ($ny < 0 || $ny >= $workSize) {
                        continue;
                    }
                    for ($dx = -$radius; $dx <= $radius; $dx++) {
                        $nx = $x + $dx;
                        if ($nx < 0 || $nx >= $workSize) {
                            continue;
                        }
                        $color = imagecolorat($resized, $nx, $ny);
                        $alpha = ($color >> 24) & 0x7F;
                        if ($alpha < $bestAlpha) {
                            $bestAlpha = $alpha;
                            $bestColor = $color;
                        }
                    }
                }
                if ($bestColor !== null) {
                    imagesetpixel($bold, $x, $y, $bestColor);
                }
            }
        }
        imagedestroy($resized);

        // A little padding so the mark doesn't touch the very edge of the tab icon.
        $padded = $this->trimTransparentPadding($bold);
        $finalSize = max(imagesx($padded), imagesy($padded));
        $canvas = imagecreatetruecolor($finalSize, $finalSize);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent3 = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent3);
        imagecopy(
            $canvas, $padded,
            (int) round(($finalSize - imagesx($padded)) / 2),
            (int) round(($finalSize - imagesy($padded)) / 2),
            0, 0, imagesx($padded), imagesy($padded)
        );
        imagedestroy($padded);

        return $canvas;
    }

    /** Delete a previously stored upload (and its sibling email-PNG, if a branding upload made one). */
    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);

            if (str_ends_with($path, '.webp')) {
                Storage::disk('public')->delete(substr($path, 0, -5).'.png');
            }
        }
    }

    /**
     * Crop to the bounding box of visible (non-fully-transparent) pixels, with a small even
     * margin added back on every side. Falls back to the original image if it has no real
     * transparency (a flat background) or trimming would leave nothing.
     */
    private function trimTransparentPadding(\GdImage $image): \GdImage
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        // Sample every pixel (logos are small uploads, so this is cheap) looking for anything
        // that isn't fully transparent.
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $alpha = (imagecolorat($image, $x, $y) >> 24) & 0x7F;
                if ($alpha < 127) { // 127 = fully transparent in GD's 0-127 alpha scale
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                }
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            return $image; // nothing but transparent pixels (or no alpha channel at all)
        }

        $boxWidth = $maxX - $minX + 1;
        $boxHeight = $maxY - $minY + 1;

        // Only bother if there is meaningfully uneven padding to remove.
        if ($boxWidth >= $width * 0.98 && $boxHeight >= $height * 0.98) {
            return $image;
        }

        $margin = (int) round(max($boxWidth, $boxHeight) * 0.06);

        $cropX = max(0, $minX - $margin);
        $cropY = max(0, $minY - $margin);
        $cropWidth = min($width - $cropX, $boxWidth + $margin * 2);
        $cropHeight = min($height - $cropY, $boxHeight + $margin * 2);

        $trimmed = imagecreatetruecolor($cropWidth, $cropHeight);
        imagealphablending($trimmed, false);
        imagesavealpha($trimmed, true);
        $transparent = imagecolorallocatealpha($trimmed, 0, 0, 0, 127);
        imagefill($trimmed, 0, 0, $transparent);

        imagecopy($trimmed, $image, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight);
        imagedestroy($image);

        return $trimmed;
    }

    /** Honour the camera's EXIF orientation so portrait photos are not stored sideways. */
    private function orient(\GdImage $image, string $path, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle) {
            $rotated = imagerotate($image, $angle, 0);
            if ($rotated) {
                imagedestroy($image);

                return $rotated;
            }
        }

        return $image;
    }
}
