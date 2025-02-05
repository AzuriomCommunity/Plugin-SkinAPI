<?php

namespace Azuriom\Plugin\SkinApi\Cards; 

use Azuriom\Extensions\Plugin\UserProfileCardComposer;

class ChangeSkinViewCard extends UserProfileCardComposer
{
    /**
 *      * Get the cards to add to the user profile.
 *           * Each card should contains:
 *                * - 'name' : The name of the card
 *                     * - 'view' : The view (Ex: shop::giftcards.index).
 *                          */

    public function getCards(): array
    {
        $skinUrl = "";
        return [
            [
                'name' => trans('skin-api::messages.change'),
                'view' => 'skin-api::cards.changeskin',
            ],
        ];
    }
}

