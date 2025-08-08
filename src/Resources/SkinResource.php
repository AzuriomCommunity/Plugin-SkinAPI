<?php

namespace Azuriom\Plugin\SkinApi\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Support\Facades\Storage;

class SkinResource extends JsonResource
{
    public function toArray($request)
    {
        $skinExists = Storage::disk('public')->exists("skins/{$this->id}.png");
        
        return [
            'slim' => $skinExists ? SkinAPI::getUserSkinType($this->id) : false
        ];
    }
}
