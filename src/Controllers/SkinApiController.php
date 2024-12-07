<?php

namespace Azuriom\Plugin\SkinApi\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Image;

class SkinApiController extends Controller
{
    /**
     * Default dimensions
     */
    const DEFAULT_CAPE_WIDTH = 64;
    const DEFAULT_CAPE_HEIGHT = 32;
    const DEFAULT_SKIN_WIDTH = 64;
    const DEFAULT_SKIN_HEIGHT = 64;

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
        if (!$request->hasFile('skin')) {
            return redirect()->back()->withErrors(['skin' => 'No file uploaded']);
        }

        $file = $request->file('skin');
        if (!$file->isValid()) {
            return redirect()->back()->withErrors(['skin' => 'Invalid file upload']);
        }

        // Validate file type and basic requirements
        $this->validate($request, [
            'skin' => ['required', 'file', 'mimes:png'],
        ]);

        // Get image dimensions
        $imageInfo = @getimagesize($file->getPathname());
        if (!$imageInfo) {
            return redirect()->back()->withErrors(['skin' => 'Unable to read image dimensions']);
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $constraints = SkinAPI::getConstraints();

        // Strict dimension check based on settings
        if (isset($constraints['width']) && isset($constraints['height'])) {
            if ($width !== (int)$constraints['width'] || $height !== (int)$constraints['height']) {
                return redirect()->back()->withErrors(['skin' => "Skin dimensions must be exactly {$constraints['width']}x{$constraints['height']} pixels"]);
            }
        } else {
            if ($width < $constraints['min_width'] || $height < $constraints['min_height'] ||
                $width > $constraints['max_width'] || $height > $constraints['max_height']) {
                return redirect()->back()->withErrors(['skin' => "Skin dimensions must be between {$constraints['min_width']}x{$constraints['min_height']} and {$constraints['max_width']}x{$constraints['max_height']} pixels"]);
            }
        }

        try {
            // Create storage directory if it doesn't exist
            $storagePath = storage_path('app/public/skins');
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            // Move the file directly using move
            $fileName = $request->user()->id . '.png';
            $file->move($storagePath, $fileName);

            return redirect()->back()->with('success', trans('skin-api::messages.status.updated'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['skin' => 'Failed to save skin file: ' . $e->getMessage()]);
        }
    }

    public function updateSkin(Request $request)
    {
        $request->validate([
            'skin' => ['required', 'file', 'image', 'mimes:png', 'max:2048'],
        ]);

        try {
            if (!$request->hasFile('skin') || !$request->file('skin')->isValid()) {
                $message = trans('skin-api::messages.upload.error');
                return $request->ajax() 
                    ? response()->json(['success' => false, 'message' => $message])
                    : redirect()->back()->with('error', $message);
            }

            $file = $request->file('skin');
            $tempPath = $file->getPathname();

            // Validate image dimensions
            if (!file_exists($tempPath)) {
                Log::error('Skin upload failed: Temporary file does not exist');
                $message = trans('skin-api::messages.upload.error');
                return $request->ajax() 
                    ? response()->json(['success' => false, 'message' => $message])
                    : redirect()->back()->with('error', $message);
            }

            $imageSize = getimagesize($tempPath);
            if (!$imageSize || $imageSize[0] !== self::DEFAULT_SKIN_WIDTH || $imageSize[1] !== self::DEFAULT_SKIN_HEIGHT) {
                $message = trans('skin-api::messages.upload.invalid_size', [
                    'width' => self::DEFAULT_SKIN_WIDTH,
                    'height' => self::DEFAULT_SKIN_HEIGHT
                ]);
                return $request->ajax() 
                    ? response()->json(['success' => false, 'message' => $message])
                    : redirect()->back()->with('error', $message);
            }

            // Save the skin
            $path = 'public/skins/' . auth()->id() . '.png';
            Storage::put($path, file_get_contents($tempPath));

            $message = trans('skin-api::messages.upload.success');
            return $request->ajax() 
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Skin upload failed: ' . $e->getMessage());
            $message = trans('skin-api::messages.upload.error');
            return $request->ajax() 
                ? response()->json(['success' => false, 'message' => $message])
                : redirect()->back()->with('error', $message);
        }
    }

    /**
     * Show the cape management page.
     */
    public function showCape()
    {
        $user = auth()->user();
        $hasCape = Storage::exists('public/capes/' . $user->id . '.png');
        
        // Add timestamp to cape URL to prevent caching
        $timestamp = $hasCape ? '?t=' . Storage::lastModified('public/capes/' . $user->id . '.png') : '';
        $capeUrl = $hasCape ? url('/api/skin-api/capes/' . $user->id) . $timestamp : asset('plugins/skin-api/assets/images/no-cape.png');

        return view('skin-api::capes', [
            'hasCape' => $hasCape,
            'capeUrl' => $capeUrl,
            'width' => self::DEFAULT_CAPE_WIDTH,
            'height' => self::DEFAULT_CAPE_HEIGHT
        ]);
    }

    /**
     * Upload a new cape for the user.
     */
    public function uploadCape(Request $request)
    {
        $request->validate([
            'cape' => ['required', 'file', 'image', 'mimes:png', 'max:2048'],
        ]);

        try {
            if (!$request->hasFile('cape') || !$request->file('cape')->isValid()) {
                Log::error('Cape upload failed: No valid file provided');
                return redirect()->back()->with('error', trans('skin-api::messages.cape.upload.error'));
            }

            $file = $request->file('cape');
            $tempPath = $file->getPathname();

            // Validate image dimensions
            if (!file_exists($tempPath)) {
                Log::error('Cape upload failed: Temporary file does not exist');
                return redirect()->back()->with('error', trans('skin-api::messages.cape.upload.error'));
            }

            $imageSize = getimagesize($tempPath);
            if (!$imageSize || $imageSize[0] !== self::DEFAULT_CAPE_WIDTH || $imageSize[1] !== self::DEFAULT_CAPE_HEIGHT) {
                return redirect()->back()->with('error', trans('skin-api::messages.cape.upload.dimensions', [
                    'width' => self::DEFAULT_CAPE_WIDTH,
                    'height' => self::DEFAULT_CAPE_HEIGHT
                ]));
            }

            // Save the cape
            $path = 'public/capes/' . auth()->id() . '.png';
            Storage::put($path, file_get_contents($tempPath));

            return redirect()->back()->with('success', trans('skin-api::messages.cape.upload.success'));
        } catch (\Exception $e) {
            Log::error('Cape upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', trans('skin-api::messages.cape.upload.error'));
        }
    }

    /**
     * Delete the user's cape.
     */
    public function deleteCape()
    {
        $path = 'public/capes/' . auth()->id() . '.png';
        
        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        return redirect()->back()->with('success', trans('skin-api::messages.cape.delete.success'));
    }
}
