<?php

namespace Azuriom\Plugin\SkinApi\Render;

use Closure;
use GdImage;
use Illuminate\Support\Facades\Storage;

class AvatarRenderer
{
    private const DISK = 'public';

    /**
     * Eagerly render and cache all avatar types for a given skin.
     */
    public static function renderAll(string $skinAbsPath, string $name, bool $slim = false): void
    {
        foreach (RenderType::cases() as $type) {
            self::render($type, $skinAbsPath, $name, $slim);
        }
    }

    /**
     * Render one avatar type and write the PNG to the public disk.
     *
     * Adapted from https://github.com/scholtzm/php-minecraft-avatars.
     *
     * @license MIT
     * @author  Michael Scholtz
     */
    public static function render(RenderType $type, string $skinAbsPath, string $name, bool $slim, int $size = 64): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $skin = imagecreatefrompng($skinAbsPath);

        $image = match ($type) {
            RenderType::AVATAR => self::buildAvatar($skin, $size),
            RenderType::COMBO => self::buildCombo($skin, $slim, $size),
            RenderType::BODY => self::buildBody($skin, $slim, $size),
        };

        $disk = Storage::disk(self::DISK);
        $dir = $disk->path("skins/{$type->value}");

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagepng($image, $disk->path("skins/{$type->value}/{$name}"));

        imagedestroy($image);
        imagedestroy($skin);
    }

    /**
     * Square face render (base layer + hat overlay).
     */
    private static function buildAvatar(GdImage $skin, int $size = 64): GdImage
    {
        $skinWidth = imagesx($skin);
        $s = self::scaler($skinWidth / 64);
        $image = imagecreatetruecolor($size, $size);

        imagecopyresampled($image, $skin, 0, 0, $s(8), $s(8), $size, $size, $s(8), $s(8));
        imagecopyresampled($image, $skin, 0, 0, $s(40), $s(8), $size, $size, $s(8), $s(8));

        return $image;
    }

    /**
     * Square composite (large face in the background, then the full body projection in the foreground).
     */
    private static function buildCombo(GdImage $skin, bool $slim, int $size = 64): GdImage
    {
        $skinWidth = imagesx($skin);
        $s = self::scaler($skinWidth / 64);
        $f = $size / 64;  // output scale factor relative to the canonical 64-px layout
        $image = imagecreatetruecolor($size, $size);

        self::makeTransparent($image);

        // Background: large face (base + hat overlay)
        imagecopyresampled($image, $skin, 0, 0, $s(8), $s(8), $size, $size, $s(8), $s(8));
        imagecopyresampled($image, $skin, 0, 0, $s(40), $s(8), $size, $size, $s(8), $s(8));

        // Anchor point for the foreground body, scaled from the canonical 64-px position
        $x = (int) round(46 * $f);
        $y = (int) round(30 * $f);

        self::drawWhiteShadows($image, $x, $y, $f);
        self::drawBodyParts($image, $skin, $skinWidth, $x, $y, $f, $slim);

        return $image;
    }

    /**
     * Full body render (width x height = $width x 2*$width).
     */
    private static function buildBody(GdImage $skin, bool $slim, int $width = 64): GdImage
    {
        $skinWidth = imagesx($skin);
        $image = imagecreatetruecolor($width, $width * 2);

        self::makeTransparent($image);

        // The body sprite is defined on a 16x32 logical grid, so dstScale = width / 16.
        self::drawBodyParts($image, $skin, $skinWidth, 0, 0, $width / 16, $slim);

        return $image;
    }

    /**
     * Fill $image with a fully-transparent background.
     */
    private static function makeTransparent(GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);
    }

    /**
     * White-filled rectangles used as shadow placeholders in the COMBO render.
     */
    private static function drawWhiteShadows(GdImage $image, int $x, int $y, float $f = 1.0): void
    {
        $sc = fn ($v) => (int) round($v * $f);
        $parts = [
            [$x + $sc(3), $y - $sc(1), $sc(10), $sc(10)], // head
            [$x - $sc(1), $y + $sc(7), $sc(18), $sc(14)], // torso + arms
            [$x + $sc(3), $y + $sc(19), $sc(10), $sc(14)], // legs
        ];

        foreach ($parts as [$dx, $dy, $w, $h]) {
            $bg = imagecreate($w, $h);
            imagecolorallocate($bg, 255, 255, 255);
            imagecopyresampled($image, $bg, $dx, $dy, 0, 0, $w, $h, $w, $h);
            imagedestroy($bg);
        }
    }

    /**
     * Draw all body parts (face, torso, arms, legs, base layer + overlay each).
     */
    private static function drawBodyParts(
        GdImage $image,
        GdImage $skin,
        int $skinWidth,
        int $x,
        int $y,
        float $dstScale,
        bool $slim,
    ): void {
        $s = self::scaler($skinWidth / 64);
        $d = fn ($v) => (int) round($v * $dstScale);

        $armW = $slim ? 3 : 4;
        $leftArmX = $x + $d($slim ? 1 : 0);
        $rightArmX = $x + $d(12); // same for both — stays flush with torso end

        // Face, base + hat overlay
        imagecopyresampled($image, $skin, $x + $d(4), $y, $s(8), $s(8), $d(8), $d(8), $s(8), $s(8));
        imagecopyresampled($image, $skin, $x + $d(4), $y, $s(40), $s(8), $d(8), $d(8), $s(8), $s(8));

        // Torso, base + overlay
        imagecopyresampled($image, $skin, $x + $d(4), $y + $d(8), $s(20), $s(20), $d(8), $d(12), $s(8), $s(12));
        imagecopyresampled($image, $skin, $x + $d(4), $y + $d(8), $s(20), $s(36), $d(8), $d(12), $s(8), $s(12));

        // Left arm, base + overlay
        imagecopyresampled($image, $skin, $leftArmX, $y + $d(8), $s(44), $s(20), $d($armW), $d(12), $s($armW), $s(12));
        imagecopyresampled($image, $skin, $leftArmX, $y + $d(8), $s(44), $s(36), $d($armW), $d(12), $s($armW), $s(12));

        // Right arm, base + overlay
        imagecopyresampled($image, $skin, $rightArmX, $y + $d(8), $s(36), $s(52), $d($armW), $d(12), $s($armW), $s(12));
        imagecopyresampled($image, $skin, $rightArmX, $y + $d(8), $s(52), $s(52), $d($armW), $d(12), $s($armW), $s(12));

        // Left leg, base + overlay
        imagecopyresampled($image, $skin, $x + $d(4), $y + $d(20), $s(4), $s(20), $d(4), $d(12), $s(4), $s(12));
        imagecopyresampled($image, $skin, $x + $d(4), $y + $d(20), $s(4), $s(36), $d(4), $d(12), $s(4), $s(12));

        // Right leg, base + overlay
        imagecopyresampled($image, $skin, $x + $d(8), $y + $d(20), $s(20), $s(52), $d(4), $d(12), $s(4), $s(12));
        imagecopyresampled($image, $skin, $x + $d(8), $y + $d(20), $s(4), $s(52), $d(4), $d(12), $s(4), $s(12));
    }

    private static function scaler(float $scale): Closure
    {
        return fn (int $v) => (int) round($v * $scale);
    }

    public static function isSlimSkin(string $skinAbsPath): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        $image = imagecreatefrompng($skinAbsPath);
        imagepalettetotruecolor($image);
        $width = imagesx($image);

        if ($width !== imagesy($image)) {
            imagedestroy($image);

            return false; // Legacy skin => not slim
        }

        $s = self::scaler($width / 64);

        $checkPixels = [
            [54, 24], // Right arm, base layer
            [55, 24], // Right arm, base layer
            [46, 57], // Left arm, base layer
            [47, 57], // Left arm, base layer
        ];

        $transparentCount = 0;

        foreach ($checkPixels as [$x, $y]) {
            $rgba = imagecolorat($image, $s($x), $s($y));
            $alpha = ($rgba >> 24) & 0x7F; // GD: 0 = opaque, 127 = fully transparent

            if ($alpha === 127) {
                $transparentCount++;
            }
        }

        imagedestroy($image);

        return $transparentCount === count($checkPixels);
    }

    /**
     * Delete all cached render files for a given name.
     */
    public static function deleteAll(string $name): void
    {
        $disk = Storage::disk(self::DISK);

        foreach (RenderType::cases() as $type) {
            $disk->delete("skins/{$type->value}/{$name}");
        }
    }
}
