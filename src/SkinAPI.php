<?php

namespace Azuriom\Plugin\SkinApi;

use Azuriom\Plugin\SkinApi\Models\Skin;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SkinAPI
{
    public static function getConstraints(bool $cape = false): array
    {
        $prefix = $cape ? 'skin.capes.' : 'skin.';

        $width = (int) setting($prefix.'width', 64);
        $height = (int) setting($prefix.'height', 64);
        $scale = (int) setting($prefix.'scale', 1);

        if ($scale === 1) {
            return ['width' => $width, 'height' => $height];
        }

        return [
            'min_width' => $width,
            'min_height' => $height,
            'max_width' => $width * $scale,
            'max_height' => $height * $scale,
        ];
    }

    public static function getRule(bool $cape = false): string
    {
        return Rule::dimensions(static::getConstraints($cape));
    }

    public static function defaultSkin(): string
    {
        static::ensureDefaultSkin();

        return Storage::disk('public')->url('skins/default.png');
    }

    public static function ensureDefaultSkin(): void
    {
        if (! Storage::disk('public')->exists('skins/default.png')) {
            $defaultPath = plugin_path('skin-api/assets/img/steve.png');
            Storage::disk('public')->put('skins/default.png', file_get_contents($defaultPath));
        }
    }

    /**
     * Check if the skin is slim by checking specific areas for transparency, black or white pixels
     *
     * @param string $skinPath Full path to the skin file
     * @return bool true if slim, false if default
     */
    public static function isSkinSlim(string $skinPath): bool
    {
        if (!file_exists($skinPath)) {
            return false;
        }

        // Load the skin image
        $skin = @imagecreatefrompng($skinPath);
        if (!$skin) {
            return false;
        }

        // Get image dimensions
        $width = imagesx($skin);
        $scale = $width / 64; // Compute scale like in JS

        // Helper functions to check areas
        $hasTransparency = function($x, $y, $w, $h) use ($skin, $scale) {
            for ($px = $x * $scale; $px < ($x + $w) * $scale; $px++) {
                for ($py = $y * $scale; $py < ($y + $h) * $scale; $py++) {
                    $color = imagecolorat($skin, $px, $py);
                    $alpha = ($color >> 24) & 0x7F;
                    if ($alpha == 127) { // Fully transparent
                        return true;
                    }
                }
            }
            return false;
        };

        $isAreaColor = function($x, $y, $w, $h, $targetR, $targetG, $targetB) use ($skin, $scale) {
            for ($px = $x * $scale; $px < ($x + $w) * $scale; $px++) {
                for ($py = $y * $scale; $py < ($y + $h) * $scale; $py++) {
                    $color = imagecolorat($skin, $px, $py);
                    $r = ($color >> 16) & 0xFF;
                    $g = ($color >> 8) & 0xFF;
                    $b = $color & 0xFF;
                    if ($r !== $targetR || $g !== $targetG || $b !== $targetB) {
                        return false;
                    }
                }
            }
            return true;
        };

        $isAreaBlack = function($x, $y, $w, $h) use ($isAreaColor) {
            return $isAreaColor($x, $y, $w, $h, 0, 0, 0);
        };

        $isAreaWhite = function($x, $y, $w, $h) use ($isAreaColor) {
            return $isAreaColor($x, $y, $w, $h, 255, 255, 255);
        };

        // Check exactly the same coordinates and areas as in JavaScript
        $isSlim = (
            $hasTransparency(50, 16, 2, 4) ||
            $hasTransparency(54, 20, 2, 12) ||
            $hasTransparency(42, 48, 2, 4) ||
            $hasTransparency(46, 52, 2, 12)
        ) || (
            $isAreaBlack(50, 16, 2, 4) &&
            $isAreaBlack(54, 20, 2, 12) &&
            $isAreaBlack(42, 48, 2, 4) &&
            $isAreaBlack(46, 52, 2, 12)
        ) || (
            $isAreaWhite(50, 16, 2, 4) &&
            $isAreaWhite(54, 20, 2, 12) &&
            $isAreaWhite(42, 48, 2, 4) &&
            $isAreaWhite(46, 52, 2, 12)
        );

        imagedestroy($skin);
        return $isSlim;
    }

    /**
     * Get the skin type for a user
     *
     * @param int $userId
     * @return bool true if slim, false if default
     */
    public static function getUserSkinType(int $userId): bool
    {
        try {
            $skin = Skin::forUser($userId);
            if ($skin !== null) {
                return $skin->slim;
            }

            $skinPath = Storage::disk('public')->path("skins/{$userId}.png");
            if (! file_exists($skinPath)) {
                return false;
            }

            return self::isSkinSlim($skinPath);
        } catch (\Exception $e) {
            return false;
        }
    }
}
