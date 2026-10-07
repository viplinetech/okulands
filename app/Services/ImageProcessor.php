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

        ob_start();
        imagewebp($source, null, $quality);
        $binary = ob_get_clean();
        imagedestroy($source);

        $relative = 'uploads/'.trim($folder, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($relative, $binary);

        return $relative;
    }

    /** Delete a previously stored upload. Only files under uploads/ are ever removed. */
    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
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
