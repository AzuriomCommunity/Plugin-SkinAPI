<?php

namespace Azuriom\Plugin\SkinApi\Controllers\Api;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Models\Cape;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\Render\AvatarRenderer;
use Azuriom\Plugin\SkinApi\Render\RenderType;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    /**
     * Return the original skin PNG for a user (by numeric ID or username).
     */
    public function skin(string $user)
    {
        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');
        $skin = $userId ? Skin::forUser($userId) : null;

        if ($skin === null) {
            if (setting('skin.not_found_handling') === '404_status') {
                return response()->json([
                    'error' => 'Not found',
                    'message' => "No skin for user with identifier: {$user}",
                ], 404);
            }

            SkinAPI::ensureDefaultSkin();

            return Storage::disk('public')->response('skins/default.png', 'skin.png', [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache',
            ]);
        }

        return $skin->getDisk()->response($skin->getPath(), 'skin.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Return a rendered avatar PNG (type: "face" or "combo") for a user.
     */
    public function avatar(string $type, string $user)
    {
        $renderType = RenderType::tryFrom($type);

        if ($renderType === null) {
            return response()->json([
                'error' => 'Invalid type',
                'message' => 'The avatar type must be "combo" or "face".',
            ], 400);
        }

        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');
        $skin = $userId ? Skin::forUser($userId) : null;
        $disk = Storage::disk('public');

        if ($skin === null) {
            if (setting('skin.not_found_handling') === '404_status') {
                return response()->json([
                    'error' => 'Not found',
                    'message' => "No skin for user with identifier: {$user}",
                ], 404);
            }

            if ($disk->exists("skins/{$type}/default.png")) {
                return $disk->response("skins/{$type}/default.png", "{$type}.png", [
                    'Content-Type' => 'image/png',
                ]);
            }

            return response()->file(plugins()->path('skin-api', "assets/img/{$type}_steve.png"), [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache',
            ]);
        }

        $key = "skins/{$type}/{$skin->file}";

        if (! $disk->exists($key)) {
            $skinPath = $skin->getDisk()->path($skin->getPath());
            AvatarRenderer::render($renderType, $skinPath, $skin->file, $skin->slim);
        }

        return $disk->response($key, "{$type}.png", [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Upload a skin via API token.
     */
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

        $file = $request->file('skin');

        Skin::firstOrNew(['user_id' => $user->id])->fill([
            'sha256' => hash_file('sha256', $file->getPathname()),
            'slim' => AvatarRenderer::isSlimSkin($file->getPathname()),
        ])->storeImage($file, save: true);

        return response()->json(['status' => 'success']);
    }

    /**
     * Return the original cape PNG for a user.
     */
    public function cape(string $user)
    {
        abort_if(! setting('skin.capes.enable', false), 404);

        $userId = is_numeric($user) ? (int) $user : User::where('name', $user)->value('id');
        $cape = $userId ? Cape::forUser($userId) : null;

        if ($cape === null) {
            return response()->json([
                'error' => 'Not found',
                'message' => "No cape for user with identifier: {$user}",
            ], 404);
        }

        return $cape->getDisk()->response($cape->getPath(), 'cape.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Upload a cape via API token.
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

        $file = $request->file('cape');

        Cape::firstOrNew(['user_id' => $user->id])->fill([
            'sha256' => hash_file('sha256', $file->getPathname()),
        ])->storeImage($file, save: true);

        return response()->json(['status' => 'success']);
    }

    /**
     * Delete the skin of the user with the given token.
     */
    public function deleteSkin(Request $request)
    {
        $request->validate(['access_token' => 'required|string']);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'error' => 'Invalid token'], 403);
        }

        Skin::forUser($user->id)?->delete();

        return response()->json(['status' => 'success']);
    }

    /**
     * Delete the cape of the user with the given token.
     */
    public function deleteCape(Request $request)
    {
        abort_if(! setting('skin.capes.enable', false), 404);

        $request->validate(['access_token' => 'required|string']);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'error' => 'Invalid token'], 403);
        }

        Cape::forUser($user->id)?->delete();

        return response()->json(['status' => 'success']);
    }
}
