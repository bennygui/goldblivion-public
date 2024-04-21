<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace GB;

require_once('ComponentDef.php');

trait ComponentDefMgrVillage
{
    private static function getComponentDefVillage()
    {
        return [
            (new ComponentDefBuilder())->village(3000)->name(clienttranslate("Material Village, Gold Side"))
                ->desc(clienttranslate('Pay 2 material to gain 1 gold. The other side is: Pay 2 material to gain 1 combat card and Draw 1 GOLDblivion card.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_RIGHT)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_MATERIAL, 2)
                        ->gain(GAIN_ID_GAIN_GOLD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->village(3001)->name(clienttranslate("Material Village, Card Side"))
                ->desc(clienttranslate('Pay 2 material to gain 1 combat card and Draw 1 GOLDblivion card. The other side is: Pay 2 material to gain 1 gold.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_RIGHT)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_MATERIAL, 2)
                        ->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)
                        ->gain(GAIN_ID_DRAW_BLUE_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->village(3010)->name(clienttranslate("Nugget Village, Card Side"))
                ->desc(clienttranslate('Pay 2 nuggets to destroy 2 cards from the markets (GOLDblivion or Combat). The other side is: Pay 2 nuggets to destroy 1 card from the markets (GOLDblivion or Combat) and Roll a dice.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_LEFT)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_NUGGET, 2)
                        ->gain(GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->village(3011)->name(clienttranslate("Nugget Village, Dice Side"))
                ->desc(clienttranslate('Pay 2 nuggets to destroy 1 card from the markets (GOLDblivion or Combat) and Roll a dice. The other side is: Pay 2 nuggets to destroy 2 cards from the markets (GOLDblivion or Combat).'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_LEFT)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_NUGGET, 2)
                        ->gain(GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET, 1)
                        ->gain(GAIN_ID_ROLL_DICE, 1)
                        ->build()
                )
                ->build(),
        ];
    }
}
