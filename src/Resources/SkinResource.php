<?php

namespace Azuriom\Plugin\SkinApi\Resources;

use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \Azuriom\Plugin\SkinApi\Models\Skin */
class SkinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => route('skin-api.api.show', $this->user->name),
            'hash' => 'sha256:'.$this->sha256,
            'slim' => $this->slim,
            'default' => $this->file === 'default.png',
            'last_modified' => $this->updated_at->toIso8601String(),
        ];
    }

    public static function forDefault(string $user): self
    {
        $disk = Storage::disk('public');
        SkinAPI::ensureDefaultSkin();

        $skin = (new Skin())->setRelation('user', new User(['name' => $user]))
            ->forceFill([
                'file' => 'default.png',
                'sha256' => hash_file('sha256', $disk->path('skins/default.png')),
                'slim' => false,
            ])
            ->setUpdatedAt(Carbon::createFromTimestamp($disk->lastModified('skins/default.png')));

        return new self($skin);
    }
}
