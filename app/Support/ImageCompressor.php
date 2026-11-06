<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressor
{
    private const MAX_SIDE = 1280;
    private const QUALITY  = 75;

    /**
     * Simpan foto ke disk "public" dalam bentuk JPEG terkompresi.
     * Jika ekstensi GD tidak aktif, file disimpan apa adanya.
     */
    public static function store(UploadedFile $file, string $dir = 'complaints'): string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $file->store($dir, 'public');
        }

        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $image) {
            return $file->store($dir, 'public');
        }

        // Foto dari kamera HP sering membawa info rotasi di EXIF
        if ($file->getMimeType() === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file->getRealPath());
            $rotated = match ($exif['Orientation'] ?? 1) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
            $image = $rotated ?: $image;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        if (max($width, $height) > self::MAX_SIDE) {
            $scale  = self::MAX_SIDE / max($width, $height);
            $scaled = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
            if ($scaled) {
                $image  = $scaled;
                $width  = imagesx($image);
                $height = imagesy($image);
            }
        }

        // Latar putih supaya PNG transparan tidak jadi hitam saat jadi JPEG
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagejpeg($canvas, null, self::QUALITY);
        $bytes = ob_get_clean();

        $path = $dir.'/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }
}
