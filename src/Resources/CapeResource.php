<?php

namespace Azuriom\Plugin\SkinApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Azuriom\Plugin\SkinApi\Models\Skin */
class CapeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => route('skin-api.api.cape', $this->user->name),
            'hash' => 'sha256:'.$this->sha256,
            'last_modified' => $this->updated_at->toIso8601String(),
        ];
    }
}
