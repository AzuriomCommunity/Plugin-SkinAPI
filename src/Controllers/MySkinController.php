<?php

namespace Azuriom\Plugin\SkinApi\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MySkinController extends Controller
{
    /**
     * Show the skin (and cape) edition page.
     */
    public function index(Request $request)
    {
        $skin = SkinAPI::skinUrl($request->user()->id);
        $cape = SkinAPI::skinUrl($request->user()->id, true);

        return view('skin-api::index', [
            'capesEnabled' => setting('skin.capes.enable', false),
            'skinUrl' => $skin ?? (SkinAPI::defaultSkin() ?? plugin_asset('skin-api', 'img/steve.png')),
            'capeUrl' => $cape,
            'hasSkin' => $skin !== null,
            'hasCape' => $cape !== null,
        ]);
    }

    /**
     * Upload a skin and/or cape for the current authenticated user.
     */
    public function updateSkinCape(Request $request)
    {
        $this->validate($request, [
            'skin' => ['nullable', 'mimes:png', SkinAPI::getRule()],
            'cape' => ['nullable', 'mimes:png', SkinAPI::getRule(true)],
        ]);

        if ($request->hasFile('skin')) {
            $request->file('skin')->storeAs('skins', "{$request->user()->id}.png", 'public');
        }

        if ($request->hasFile('cape') && setting('skin.capes.enable', false)) {
            $request->file('cape')->storeAs('skins/capes', "{$request->user()->id}.png", 'public');
        }

        return redirect()->back()->with('success', trans('messages.status.success'));
    }

    public function deleteSkin(Request $request)
    {
        $skinPath = "skins/{$request->user()->id}.png";

        if (Storage::disk('public')->exists($skinPath)) {
            Storage::disk('public')->delete($skinPath);
        }

        return redirect()->back()->with('success', trans('messages.status.success'));
    }

    public function deleteCape(Request $request)
    {
        $capePath = "skins/capes/{$request->user()->id}.png";

        if (Storage::disk('public')->exists($capePath)) {
            Storage::disk('public')->delete($capePath);
        }

        return redirect()->back()->with('success', trans('messages.status.success'));
    }
}
