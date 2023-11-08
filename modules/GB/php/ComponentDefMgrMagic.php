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

trait ComponentDefMgrMagic
{
    private static function getComponentDefMagic()
    {
        return [
            (new ComponentDefBuilder())->magic(4000)->name(clienttranslate("Magic Token 1"))
                ->desc(clienttranslate('Gain a non-building card from the GOLDblivion market at no cost.'))
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->magic(4001)->name(clienttranslate("Magic Token 2"))
                ->desc(clienttranslate('Pay 1 material to Gain 1 gold.'))
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_MATERIAL, 1)
                        ->gain(GAIN_ID_GAIN_GOLD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->magic(4002)->name(clienttranslate("Magic Token 3"))
                ->desc(clienttranslate('Gain 2 nuggets per Human in your play area.'))
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 2, COMPONENT_ICON_ID_HUMAN)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->magic(4003)->name(clienttranslate("Magic Token 4"))
                ->desc(clienttranslate('Start a combat with +2 Combat Strength.'))
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_START_COMBAT, 1)
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->magic(4004)->name(clienttranslate("Magic Token 5"))
                ->desc(clienttranslate('Gain 1 material.'))
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_MATERIAL, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->magic(4005)->name(clienttranslate("Magic Token 6"))
                ->desc(clienttranslate('Gain a Combat card at no cost and and Gain 1 nugget per Enemy you defeated.'))
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ENEMY)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->magic(4006)->name(clienttranslate("Magic Token 7"))
                ->desc(clienttranslate('Draw one GOLDblivion card from your deck.'))
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_DRAW_BLUE_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->magic(4007)->name(clienttranslate("Magic Token 8"))
                ->desc(clienttranslate('Reactivate a Human card in your play area.'))
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_REACTIVATE_ICON, 1, COMPONENT_ICON_ID_HUMAN)->build())
                ->build(),
        ];
    }
}
