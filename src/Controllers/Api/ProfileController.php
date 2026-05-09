<?php

namespace Azuriom\Plugin\SkinApi\Controllers\Api;

use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Models\Cape;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\Resources\CapeResource;
use Azuriom\Plugin\SkinApi\Resources\SkinResource;
use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
    public function show(string $user)
    {
        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');
        $skin = $userId ? Skin::forUser($userId) : null;
        $cape = $userId ? Cape::forUser($userId) : null;

        if ($skin === null && setting('skin.not_found_handling') === '404_status') {
            return response()->json([
                'error' => 'Not found',
                'message' => "No skin for user with identifier: {$user}",
            ], 404);
        }

        return response()->json([
            'user' => $user,
            'skin' => $skin !== null ? new SkinResource($skin) : SkinResource::forDefault($user),
            'cape' => $cape !== null ? new CapeResource($cape) : null,
        ], options: JSON_UNESCAPED_SLASHES);
    }
}
