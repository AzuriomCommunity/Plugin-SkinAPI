<?php

namespace Azuriom\Plugin\SkinApi\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SkinApiController extends Controller
{
    /**
     * Show the home plugin page.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('skin-api::index', [
            'skinUrl' => route('skin-api.api.show', $request->user()->id).'?v='.Str::random(4),
        ]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'skin' => ['required', 'mimes:png', SkinAPI::getRule()],
        ]);

        $request->file('skin')->storeAs('skins', "{$request->user()->id}.png", 'public');

        return redirect()->back()->with('success', trans('skin-api::messages.updated'));
    }

}
