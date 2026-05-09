<?php

namespace Azuriom\Plugin\SkinApi\Cards;

use Azuriom\Extensions\Plugin\UserProfileCardComposer;
use Azuriom\Plugin\SkinApi\Models\Cape;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Support\Facades\View;

class ChangeSkinCapeCard extends UserProfileCardComposer
{
    public function getCards(): array
    {
        $user = auth()->user();
        $cards = [];

        if ($user->can('skin-api.skin')) {
            $skin = Skin::forUser($user->id);

            $cards[] = [
                'name' => trans('skin-api::messages.title'),
                'view' => 'skin-api::cards.skin',
            ];

            View::share([
                'skinUrl' => $skin?->imageUrl() ?? SkinAPI::defaultSkin(),
                'hasSkin' => $skin !== null,
            ]);
        }

        if (setting('skin.capes.enable', false) && $user->can('skin-api.cape')) {
            $cape = Cape::forUser($user->id);

            $cards[] = [
                'name' => trans('skin-api::messages.cape_title'),
                'view' => 'skin-api::cards.cape',
            ];

            View::share([
                'capeUrl' => $cape?->imageUrl(),
                'hasCape' => $cape !== null,
            ]);
        }

        return $cards;
    }
}
