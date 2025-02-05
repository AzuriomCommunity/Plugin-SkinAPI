<?php

namespace Azuriom\Plugin\SkinApi;

use Azuriom\Plugin\SkinApi\Render\RenderType;
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
}
