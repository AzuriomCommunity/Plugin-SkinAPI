<?php

namespace Azuriom\Plugin\SkinApi\Controllers;

use Azuriom\Models\User;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(string $username)
    {
        $user = User::where('name', $username)->first();
        $disk = Storage::disk('public');

        $result = [
            'username' => $username,
            'skin' => null,
            'cape' => null,
        ];

        // Skin
        $isDefaultSkin = false;

        if ($user !== null && $disk->exists("skins/{$user->id}.png")) {
            $skinFile = "skins/{$user->id}.png";
        } elseif ($disk->exists('skins/default.png')) {
            $skinFile = 'skins/default.png';
            $isDefaultSkin = true;
        } else {
            $skinFile = null;
        }

        if ($skinFile !== null) {
            $skinPath = $disk->path($skinFile);

            $result['skin'] = [
                'url' => route('skin-api.api.show', $username),
                'hash' => 'sha256:'.hash_file('sha256', $skinPath),
                'slim' => $this->isSlimSkin($skinPath),
                'default' => $isDefaultSkin,
                'last_modified' => Carbon::createFromTimestamp($disk->lastModified($skinFile))->toIso8601String(),
            ];
        }

        // Cape
        if ($user !== null && $disk->exists("capes/{$user->id}.png")) {
            $capeFile = "capes/{$user->id}.png";
            $capePath = $disk->path($capeFile);

            $result['cape'] = [
                'url' => route('skin-api.api.cape', $username),
                'hash' => 'sha256:'.hash_file('sha256', $capePath),
                'last_modified' => Carbon::createFromTimestamp($disk->lastModified($capeFile))->toIso8601String(),
            ];
        }

        return response()->json($result, options: JSON_UNESCAPED_SLASHES);
    }

    private function isSlimSkin(string $path): bool
    {
        $img = imagecreatefrompng($path);

        $rgb = imagecolorat($img, 55, 20);
        $colors = imagecolorsforindex($img, $rgb);
        $alpha = $colors['alpha'];
        imagedestroy($img);

        return $alpha === 127;
    }
}
