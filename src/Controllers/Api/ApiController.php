<?php

namespace Azuriom\Plugin\SkinApi\Controllers\Api;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * Show the home plugin page.
     *
     * @param  string $user
     * @return \Illuminate\Http\Response
     */
    public function show(string $user)
    {
        if (Str::endsWith($user, '.png')) {
            $user = Str::beforeLast($user, '.png');
        }

        // Try to find user by ID first, then by name
        $userId = is_numeric($user) ? 
            User::where('id', $user)->value('id') : 
            User::where('name', $user)->value('id');

        if ($userId === null) {
            // Check the not found behavior setting
            $behavior = setting('skin.not_found_behavior', 'default_skin');
            
            if ($behavior === 'error_message') {
                return response()->json([
                    'error' => 'User not found',
                    'message' => "No user found with identifier: {$user}"
                ], 404);
            }
            
            // Default behavior: return steve skin
            return response()->file(base_path().'/plugins/skin-api/assets/img/steve.png', [
                'Content-Type' => 'image/png',
            ]);
        }

        $skinPath = "skins/{$userId}.png";
        
        // Debug storage path
        \Log::info('Looking for skin at: ' . storage_path("app/public/{$skinPath}"));
        \Log::info('Storage exists: ' . (Storage::disk('public')->exists($skinPath) ? 'true' : 'false'));
        
        if (!Storage::disk('public')->exists($skinPath)) {
            \Log::info('Skin file not found, returning default Steve skin');
            return response()->file(base_path().'/plugins/skin-api/assets/img/steve.png', [
                'Content-Type' => 'image/png',
            ]);
        }

        \Log::info('Found skin file, returning it');
        return Storage::disk('public')->response($skinPath, 'skin.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    public function avatar(string $type, string $user)
    {
        if (Str::endsWith($user, '.png')) {
            $user = Str::beforeLast($user, '.png');
        }

        abort_unless(
            in_array($type, ['combo', 'face'], true),
            422,
            "URL should be '/api/skin-api/avatars/face/{$user}.png' or '/api/skin-api/avatars/combo/{$user}.png'"
        );

        $userId = User::where('id', $user)->orWhere('name', $user)->value('id');

        if ($userId === null || ! Storage::disk('public')->exists("skins/{$userId}.png")) {
            return response()->file(base_path()."/plugins/skin-api/assets/img/{$type}_steve.png", [
                'Content-Type' => 'image/png',
            ]);
        }

        // if the avatar does not exist or the skin is more recent than the avatar
        if (! Storage::disk('public')->exists("{$type}/{$userId}.png")
            || Storage::disk('public')->lastModified("skins/{$userId}.png") > Storage::disk('public')->lastModified("{$type}/{$userId}.png")) {
            SkinAPI::makeAvatarWithTypeForUser($type, $userId);
        }

        return Storage::disk('public')->response("{$type}/{$userId}.png", "{$type}.png", [
            'Content-Type' => 'image/png',
        ]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'access_token' => 'required|string',
            'skin' => ['required', 'mimes:png', SkinAPI::getRule()],
        ]);

        $user = User::firstWhere('access_token', $request->input('access_token'));

        if ($user === null) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 422);
        }

        if ($user->isBanned()) {
            return response()->json(['status' => false, 'message' => 'User banned'], 422);
        }

        return $request->file('skin')->storeAs('skins', "{$user->id}.png", 'public');
    }

    /**
     * Show the user's cape.
     *
     * @param  string $user
     * @return \Illuminate\Http\Response
     */
    public function showCape(string $user)
    {
        if (Str::endsWith($user, '.png')) {
            $user = Str::beforeLast($user, '.png');
        }

        // Try to find user by ID first, then by name
        $userId = is_numeric($user) ? 
            User::where('id', $user)->value('id') : 
            User::where('name', $user)->value('id');

        if ($userId === null) {
            return response()->file(
                base_path().'/plugins/skin-api/assets/images/no-cape.png',
                [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'no-cache, must-revalidate'
                ]
            );
        }

        $capePath = "public/capes/{$userId}.png";
        
        if (!Storage::exists($capePath)) {
            return response()->file(
                base_path().'/plugins/skin-api/assets/images/no-cape.png',
                [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'no-cache, must-revalidate'
                ]
            );
        }

        return response()->file(
            Storage::path($capePath),
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Last-Modified' => gmdate('D, d M Y H:i:s', Storage::lastModified($capePath)).' GMT'
            ]
        );
    }

    /**
     * Show the user's cape.
     *
     * @param  string $identifier
     * @return \Illuminate\Http\Response
     */
    public function getCape($identifier)
    {
        $user = User::findOrFail($identifier);
        $capePath = 'public/capes/' . $user->id . '.png';

        if (!Storage::exists($capePath)) {
            abort(404);
        }

        return response()->file(Storage::path($capePath), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=600'
        ]);
    }

    /**
     * Update the user's cape.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCape(Request $request)
    {
        $request->validate([
            'cape' => ['required', 'image', 'mimes:png', 'dimensions:width=' . setting('skin.cape_width', 64) . ',height=' . setting('skin.cape_height', 32)],
        ]);

        $user = $request->user();
        $capePath = "skins/capes/{$user->id}.png";

        // Delete old cape if exists
        if (Storage::exists($capePath)) {
            Storage::delete($capePath);
        }

        // Store new cape
        $request->file('cape')->storeAs(dirname($capePath), basename($capePath));

        return response()->json([
            'message' => trans('skin-api::messages.cape.upload.success'),
        ]);
    }

    /**
     * Remove the user's cape.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function removeCape(Request $request)
    {
        $user = $request->user();
        $capePath = "skins/capes/{$user->id}.png";

        if (Storage::exists($capePath)) {
            Storage::delete($capePath);
        }

        return response()->json([
            'message' => trans('skin-api::messages.cape.delete.success'),
        ]);
    }
}
