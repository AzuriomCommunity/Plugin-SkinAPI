<?php

namespace Azuriom\Plugin\SkinApi\Controllers\Api;

use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Models\Cape;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(string $user)
    {
        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');
        $skin = $userId ? Skin::forUser($userId) : null;
        $cape = $userId ? Cape::forUser($userId) : null;
        $disk = Storage::disk('public');

        $result = [
            'user' => $user,
            'skin' => null,
            'cape' => null,
        ];

        if ($skin === null && setting('skin.not_found_handling') === '404_status') {
            return response()->json([
                'error' => 'Not found',
                'message' => "No skin for user with identifier: {$user}",
            ], 404);
        }

        if ($skin !== null) {
            $result['skin'] = [
                'url' => route('skin-api.api.show', $user),
                'hash' => 'sha256:'.$skin->sha256,
                'slim' => $skin->slim,
                'default' => false,
                'last_modified' => $skin->updated_at->toIso8601String(),
            ];
        } else {
            SkinAPI::ensureDefaultSkin();

            $result['skin'] = [
                'url' => route('skin-api.api.show', $user),
                'hash' => 'sha256:'.hash_file('sha256', $disk->path('skins/default.png')),
                'slim' => false,
                'default' => true,
                'last_modified' => Carbon::createFromTimestamp($disk->lastModified('skins/default.png'))->toIso8601String(),
            ];
        }

        if ($cape !== null) {
            $result['cape'] = [
                'url' => route('skin-api.api.cape', $user),
                'hash' => 'sha256:'.$cape->sha256,
                'last_modified' => $cape->updated_at->toIso8601String(),
            ];
        }

        return response()->json($result, options: JSON_UNESCAPED_SLASHES);
    }
}
