<?php

namespace Azuriom\Plugin\SkinApi\Controllers\Api;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Render\RenderType;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    /**
     * Return the original skin of the user.
     */
    public function skin(string $user)
    {
        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');

        if ($userId === null || ! Storage::disk('public')->exists("skins/{$userId}.png")) {
            if (setting('skin.not_found_handling') === '404_status') {
                return response()->json([
                    'error' => 'Not found',
                    'message' => "No skin for user with identifier: {$user}",
                ], 404);
            }

            if (SkinAPI::defaultSkin() === null) {
                return response()->file(plugins()->path('skin-api', 'assets/img/steve.png'), [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'no-cache',
                ]);
            }

            $userId = 'default';
        }

        return Storage::disk('public')->response("skins/{$userId}.png", 'skin.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Return the avatar of the user.
     */
    public function avatar(string $type, string $user)
    {
        if ($type !== 'combo' && $type !== 'face') {
            return response()->json([
                'error' => 'Invalid type',
                'message' => 'The avatar type must be "combo" or "face".',
            ], 400);
        }

        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');

        if ($userId === null || ! Storage::disk('public')->exists("skins/{$userId}.png")) {
            if (setting('skin.not_found_handling') === '404_status') {
                return response()->json([
                    'error' => 'Not found',
                    'message' => "No skin for user with identifier: {$user}",
                ], 404);
            }

            if (SkinAPI::defaultSkin() === null) {
                return response()->file(plugins()->path('skin-api', "assets/img/{$type}_steve.png"), [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'no-cache',
                ]);
            }

            $userId = 'default';
        }

        // if the avatar does not exist or the skin is more recent than the avatar
        if (! Storage::disk('public')->exists("{$type}/{$userId}.png")
            || Storage::disk('public')->lastModified("skins/{$userId}.png") > Storage::disk('public')->lastModified("{$type}/{$userId}.png")) {
            $renderType = $type === 'combo' ? RenderType::COMBO : RenderType::AVATAR;

            SkinAPI::makeAvatarWithTypeForUser($renderType, $userId);
        }

        return Storage::disk('public')->response("{$type}/{$userId}.png", "{$type}.png", [
            'Content-Type' => 'image/png',
        ]);
    }

    public function updateSkin(Request $request)
    {
        $this->validate($request, [
            'access_token' => 'required|string',
            'skin' => ['required', 'mimes:png', SkinAPI::getRule()],
        ]);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'error' => 'Invalid token'], 403);
        }

        $request->file('skin')->storeAs('skins', $user->id.'.png', 'public');

        return response()->json(['status' => 'success']);
    }

    /**
     * Return the original cape of the user.
     */
    public function cape(string $user)
    {
        abort_if(! setting('skin.capes.enable', false), 404);

        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');

        if ($userId === null || ! Storage::disk('public')->exists("skins/capes/{$userId}.png")) {
            return response()->json([
                'error' => 'Not found',
                'message' => "No cape for user with identifier: {$user}",
            ], 404);
        }

        return Storage::disk('public')->response("skins/capes/{$userId}.png", 'cape.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Update the cape of the user with the given token.
     */
    public function updateCape(Request $request)
    {
        abort_if(! setting('skin.capes.enable', false), 404);

        $request->validate([
            'access_token' => 'required|string',
            'cape' => ['required', 'mimes:png', SkinAPI::getRule(true)],
        ]);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'error' => 'Invalid token'], 403);
        }

        $request->file('cape')->storeAs('skins/capes', $user->id.'.png', 'public');

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the skin of the user with the given token.
     */
    public function deleteSkin(Request $request)
    {
        $request->validate(['access_token' => 'required|string']);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'error' => 'Invalid token'], 403);
        }

        Storage::disk('public')->delete('skins'.$user->id.'.png');

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the cape of the user with the given token.
     */
    public function deleteCape(Request $request)
    {
        abort_if(! setting('skin.capes.enable', false), 404);

        $request->validate(['access_token' => 'required|string']);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'error' => 'Invalid token'], 403);
        }

        Storage::disk('public')->delete('skins/capes'.$user->id.'.png');

        return response()->json(['status' => 'success']);
    }
}
