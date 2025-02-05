<?php

namespace Azuriom\Plugin\SkinApi\Cards;

use Azuriom\Extensions\Plugin\UserProfileCardComposer;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Support\Facades\View;

class ChangeSkinCapeCard extends UserProfileCardComposer
{
    public function getCards(): array
    {
        $skin = SkinAPI::skinUrl(auth()->id());

        $cards = [
            [
                'name' => trans('skin-api::messages.title'),
                'view' => 'skin-api::cards.skin',
            ],
        ];

        View::share([
            'skinUrl' => $skin ?? (SkinAPI::defaultSkin() ?? plugin_asset('skin-api', 'img/steve.png')),
            'hasSkin' => $skin !== null,
        ]);

        if (setting('skin.capes.enable', false)) {
            $cape = SkinAPI::skinUrl(auth()->id(), true);

            $cards[] = [
                'name' => trans('skin-api::messages.cape_title'),
                'view' => 'skin-api::cards.cape',
            ];

            View::share([
                'capeUrl' => $cape,
                'hasCape' => $cape !== null,
            ]);
        }

        return $cards;
    }
}
