<?php

namespace Azuriom\Plugin\SkinApi;

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
}
