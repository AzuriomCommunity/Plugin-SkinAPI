<?php

namespace Azuriom\Plugin\SkinApi;

use Illuminate\Support\Facades\Storage;

class SkinAPI
{
    public static function getConstraints()
    {
        $width = (int) setting('skin.width', 64);
        $height = (int) setting('skin.height', 64);
        $scale = (int) setting('skin.scale', 1);

        if ($scale === 1) {
            return [
                'width' => $width,
                'height' => $height,
            ];
        }

        return [
            'min_width' => $width,
            'min_height' => $height,
            'max_width' => $width * $scale,
            'max_height' => $height * $scale,
        ];
    }

    public static function getRule()
    {
        $constraints = [];
        foreach (self::getConstraints() as $key => $value) {
            $constraints[] = "{$key}={$value}";
        }

        return 'dimensions:'.implode(',', $constraints);
    }

    /**
     * Code from : https://github.com/scholtzm/php-minecraft-avatars
     * Licence MIT.
     * @param mixed $type
     * @param mixed $userId
     */
    public static function makeAvatarWithTypeForUser($type, $userId)
    {
        abort_unless(extension_loaded('gd'), 403, 'Please enable the GD extension in your php.ini');

        $skin = imagecreatefrompng(Storage::disk('public')->path("skins/{$userId}.png"));
        $size = 64;
        $x = 46;
        $y = 30;
        $image = imagecreatetruecolor($size, $size);
        // Background
        // face
        imagecopyresampled($image, $skin, 0, 0, 8, 8, $size, $size, 8, 8);
        // Add second layer to skin
        imagecopyresampled($image, $skin, 0, 0, 40, 8, $size, $size, 8, 8);

        if ($type === 'combo') {
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

        if (! file_exists($dir_path = Storage::disk('public')->path($type))) {
            mkdir($dir_path, 0755, true);
        }

        imagepng($image, Storage::disk('public')->path("{$type}/{$userId}.png"));
    }
}
