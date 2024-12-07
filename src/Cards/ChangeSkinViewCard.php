<?php

namespace Azuriom\Plugin\SkinApi\Cards;

use Azuriom\Extensions\Plugin\UserProfileCardComposer;

class ChangeSkinViewCard extends UserProfileCardComposer
{
    public function getCards(): array
    {
        // Only show the card if enabled in settings
        if (!setting('skin.show_in_profile', true)) {
            return [];
        }

        return [
            [
                'name' => trans('skin-api::messages.change'),
                'view' => 'skin-api::cards.changeskin',
            ],
        ];
    }
}
