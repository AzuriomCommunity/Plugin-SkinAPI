<?php

namespace Azuriom\Plugin\SkinApi\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\SkinApi\Models\Cape;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\Render\AvatarRenderer;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;

class MySkinController extends Controller
{
    /**
     * Show the skin (and cape) edition page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $skin = Skin::forUser($request->user()->id);
        $cape = Cape::forUser($request->user()->id);

        return view('skin-api::index', [
            'canUploadSkin' => $user->can('skin-api.skin'),
            'canUploadCape' => setting('skin.capes.enable', false) && $user->can('skin-api.cape'),
            'skinUrl' => $skin?->imageUrl() ?? SkinAPI::defaultSkin(),
            'capeUrl' => $cape?->imageUrl(),
            'hasSkin' => $skin !== null,
            'hasCape' => $cape !== null,
        ]);
    }

    /**
     * Upload a skin and/or cape for the current authenticated user.
     */
    public function updateSkinCape(Request $request)
    {
        $user = $request->user();

        abort_if(! $user->can('skin-api.skin') && ! $user->can('skin-api.cape'), 403);

        $this->validate($request, [
            'skin' => ['nullable', 'mimes:png', SkinAPI::getRule()],
            'cape' => ['nullable', 'mimes:png', SkinAPI::getRule(true)],
        ]);

        if ($request->hasFile('skin') && $user->can('skin-api.skin')) {
            $file = $request->file('skin');

            Skin::firstOrNew(['user_id' => $user->id])->fill([
                'sha256' => hash_file('sha256', $file->getPathname()),
                'slim' => AvatarRenderer::isSlimSkin($file->getPathname()),
            ])->storeImage($file, save: true);
        }

        if ($request->hasFile('cape') && $user->can('skin-api.cape') && setting('skin.capes.enable', false)) {
            $file = $request->file('cape');

            Cape::firstOrNew(['user_id' => $user->id])->fill([
                'sha256' => hash_file('sha256', $file->getPathname()),
            ])->storeImage($file, save: true);
        }

        return redirect()->back()->with('success', trans('messages.status.success'));
    }

    /**
     * Delete the skin for the currently authenticated user.
     */
    public function deleteSkin(Request $request)
    {
        Skin::forUser($request->user()->id)?->delete();

        return redirect()->back()->with('success', trans('messages.status.success'));
    }

    /**
     * Delete the cape for the currently authenticated user.
     */
    public function deleteCape(Request $request)
    {
        Cape::forUser($request->user()->id)?->delete();

        return redirect()->back()->with('success', trans('messages.status.success'));
    }
}
