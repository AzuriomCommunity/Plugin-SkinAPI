<?php

namespace Azuriom\Plugin\SkinApi;

use Azuriom\Plugin\SkinApi\Render\RenderType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
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

    public static function defaultSkin(): ?string
    {
        if (! Storage::disk('public')->exists('skins/default.png')) {
            return null;
        }

        return Storage::disk('public')->url('skins/default.png');
    }

    public static function skinUrl(int $userId, bool $cape = false): ?string
    {
        $path = $cape ? 'skins/capes/' : 'skins/';

        if (! Storage::disk('public')->exists($path.$userId.'.png')) {
            return null;
        }

        $lastModified = Storage::disk('public')->lastModified($path.$userId.'.png');
        $hash = $lastModified ? '?h='.substr($lastModified, 4) : '';

        return url(Storage::disk('public')->url($path.$userId.'.png'.$hash));
    }

    /**
     * Code from https://github.com/scholtzm/php-minecraft-avatars
     *
     * @license MIT
     * @author Michael Scholtz
     */
    public static function makeAvatarWithTypeForUser(RenderType $type, string $user): void
    {
        abort_unless(extension_loaded('gd'), 403, 'Please enable the GD extension in your php.ini');

        $skin = imagecreatefrompng(Storage::disk('public')->path("skins/{$user}.png"));
        $size = 64;
        $x = 46;
        $y = 30;
        $image = imagecreatetruecolor($size, $size);

        // Background
        // face
        imagecopyresampled($image, $skin, 0, 0, 8, 8, $size, $size, 8, 8);
        // Add second layer to skin
        imagecopyresampled($image, $skin, 0, 0, 40, 8, $size, $size, 8, 8);

        if ($type === RenderType::COMBO) {
            $head = imagecreate(10, 10);
            $white = imagecolorallocate($head, 255, 255, 255);
            imagecopyresampled($image, $head, $x + 3, $y - 1, 0, 0, 10, 10, 10, 10);
            imagecolordeallocate($head, $white);
            imagedestroy($head);

            $torso = imagecreate(18, 14);
            $white = imagecolorallocate($torso, 255, 255, 255);
            imagecopyresampled($image, $torso, $x - 1, $y + 7, 0, 0, 18, 14, 18, 14);
            imagecolordeallocate($torso, $white);
            imagedestroy($torso);

            $legs = imagecreate(10, 14);
            $white = imagecolorallocate($legs, 255, 255, 255);
            imagecopyresampled($image, $legs, $x + 3, $y + 19, 0, 0, 10, 14, 10, 14);
            imagecolordeallocate($legs, $white);
            imagedestroy($legs);
            // white shadow - end

            // Foreground
            // face
            imagecopyresampled($image, $skin, $x + 4, $y, 8, 8, 8, 8, 8, 8);
            // body
            imagecopyresampled($image, $skin, $x + 4, $y + 8, 20, 20, 8, 12, 8, 12);
            // left arm
            imagecopyresampled($image, $skin, $x, $y + 8, 44, 20, 4, 12, 4, 12);
            // right arm - must FLIP
            imagecopyresampled($image, $skin, $x + 12, $y + 8, 47, 20, 4, 12, -4, 12);
            // left leg
            imagecopyresampled($image, $skin, $x + 4, $y + 20, 4, 20, 4, 12, 4, 12);
            // right leg - must FLIP
            imagecopyresampled($image, $skin, $x + 8, $y + 20, 7, 20, 4, 12, -4, 12);
            imagesavealpha($image, true);
        }

        if (! file_exists($dir_path = Storage::disk('public')->path($type->value))) {
            mkdir($dir_path, 0755, true);
        }

        imagepng($image, Storage::disk('public')->path("{$type->value}/{$user}.png"));
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
            if (!Schema::hasTable('skin_types')) {
                return false;
            }

            $skinType = \Azuriom\Plugin\SkinApi\Models\SkinType::firstWhere('user_id', $userId);
            
            if ($skinType === null) {
                $skinPath = Storage::disk('public')->path("skins/{$userId}.png");
                
                if (!file_exists($skinPath)) {
                    return false;
                }

                $isSlim = self::isSkinSlim($skinPath);
                self::updateUserSkinType($userId, $isSlim);
                return $isSlim;
            }
            
            return $skinType->is_slim;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update the skin type for a user
     *
     * @param int $userId
     * @param bool $isSlim
     * @return void
     */
    public static function updateUserSkinType(int $userId, bool $isSlim): void
    {
        try {
            if (!Schema::hasTable('skin_types')) {
                throw new \Exception('Table skin_types does not exist');
            }

            $existing = \Azuriom\Plugin\SkinApi\Models\SkinType::where('user_id', $userId)->first();

            if ($existing) {
                $existing->is_slim = $isSlim;
                $existing->save();
            } else {
                $new = new \Azuriom\Plugin\SkinApi\Models\SkinType();
                $new->user_id = $userId;
                $new->is_slim = $isSlim;
                $new->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
