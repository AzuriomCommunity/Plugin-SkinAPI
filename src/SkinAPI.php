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
     * With modification for HD Skins by Gru and DeepSeek :)
     *
     * @license MIT
     * @author Michael Scholtz
     */
    public static function makeAvatarWithTypeForUser(RenderType $type, string $user): void
    {
        abort_unless(extension_loaded('gd'), 403, 'Please enable the GD extension in your php.ini');
    
        $skinPath = Storage::disk('public')->path("skins/{$user}.png");
        $skin = imagecreatefrompng($skinPath);
        
        $skinWidth = imagesx($skin);
        $skinHeight = imagesy($skin);
        
        $scale = $skinWidth / 64;
        $size = 64;
        
        $x = 46;
        $y = 30;
        $image = imagecreatetruecolor($size, $size);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
    
        // function of scaling params
        $s = function ($value) use ($scale) {
            return $value * $scale;
        };
    
        // Background
        // Face layer
        imagecopyresampled(
            $image, $skin, 
            0, 0, 
            $s(8), $s(8), 
            $size, $size, 
            $s(8), $s(8)
        );
        // Second layer
        imagecopyresampled(
            $image, $skin, 
            0, 0, 
            $s(40), $s(8), 
            $size, $size, 
            $s(8), $s(8)
        );
    
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
            // Face
            imagecopyresampled(
                $image, $skin, 
                $x + 4, $y, 
                $s(8), $s(8), 
                8, 8, 
                $s(8), $s(8)
            );
            // Body
            imagecopyresampled(
                $image, $skin, 
                $x + 4, $y + 8, 
                $s(20), $s(20), 
                8, 12, 
                $s(8), $s(12)
            );
            // Left arm
            imagecopyresampled(
                $image, $skin, 
                $x, $y + 8, 
                $s(44), $s(20), 
                4, 12, 
                $s(4), $s(12)
            );
            // Right arm (flipped)
            imagecopyresampled(
                $image, $skin, 
                $x + 12, $y + 8, 
                $s(47), $s(20), 
                4, 12, 
                -$s(4), $s(12)
            );
            // Left leg
            imagecopyresampled(
                $image, $skin, 
                $x + 4, $y + 20, 
                $s(4), $s(20), 
                4, 12, 
                $s(4), $s(12)
            );
            // Right leg (flipped)
            imagecopyresampled(
                $image, $skin, 
                $x + 8, $y + 20, 
                $s(7), $s(20), 
                4, 12, 
                -$s(4), $s(12)
            );
        }
    
        if (! file_exists($dir_path = Storage::disk('public')->path($type->value))) {
            mkdir($dir_path, 0755, true);
        }
    
        imagepng($image, Storage::disk('public')->path("{$type->value}/{$user}.png"));
        
        // release resources
        imagedestroy($skin);
        imagedestroy($image);
    }
}
