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

trait ComponentDefMgrCardRed
{
    private static function getComponentDefCardRed()
    {
        return array_merge(
            // Blue player
            self::getComponentDefCardRedStarting(2100, COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE),
            // Yellow player
            self::getComponentDefCardRedStarting(2110, COMPONENT_SUB_CATEGORY_ID_PLAYER_YELLOW),
            // Red player
            self::getComponentDefCardRedStarting(2120, COMPONENT_SUB_CATEGORY_ID_PLAYER_RED),
            // Green player
            self::getComponentDefCardRedStarting(2130, COMPONENT_SUB_CATEGORY_ID_PLAYER_GREEN),

            self::getComponentDefCardRedDeck(),
        );
    }

    private static function getComponentDefCardRedStarting(int $baseTypeId, int $subCategoryId)
    {
        return [
            (new ComponentDefBuilder())->cardRed($baseTypeId + 0)->name(clienttranslate("Arthur"))
                ->desc(clienttranslate('Gain 0 Combat Strength.'))
                ->setupCount(3)
                ->subCategory($subCategoryId)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_COMBAT_POWER, 0)->build())
                ->build(),
            (new ComponentDefBuilder())->cardRed($baseTypeId + 1)->name(clienttranslate("Xena"))
                ->desc(clienttranslate('Gain 1 Combat Strength.'))
                ->setupCount(2)
                ->subCategory($subCategoryId)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_COMBAT_POWER, 1)->build())
                ->build(),
        ];
    }

    private static function getComponentDefCardRedDeck()
    {
        return [
            (new ComponentDefBuilder())->cardRed(2200)->name(clienttranslate("Archer"))
                ->desc(clienttranslate('Gain 1 Combat Strength and Draw 1 more Combat card.'))
                ->setupCount(8)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 1)
                        ->gain(GAIN_ID_DRAW_RED_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardRed(2201)->name(clienttranslate("Axel"))
                ->desc(clienttranslate('Choose one: Gain 1 Combat Strength OR Gain 1 material.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_COMBAT_POWER, 1)->build())
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_MATERIAL, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardRed(2202)->name(clienttranslate("Bandit"))
                ->desc(clienttranslate('Gain 1 Combat Strength and Gain 2 nuggets.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 1)
                        ->gain(GAIN_ID_GAIN_NUGGET, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardRed(2203)->name(clienttranslate("Boomers"))
                ->desc(clienttranslate('Pay 1 material to Gain 3 Combat Strength.'))
                ->setupCount(4)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_MATERIAL, 1)
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 3)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardRed(2204)->name(clienttranslate("Champion"))
                ->desc(clienttranslate('Gain 2 Combat Strength.'))
                ->setupCount(12)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_COMBAT_POWER, 2)->build())
                ->build(),
            (new ComponentDefBuilder())->cardRed(2205)->name(clienttranslate("Joker"))
                ->desc(clienttranslate('Copy a Combat card from the current fight.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_COPY_OTHER_RED_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardRed(2206)->name(clienttranslate("Karen"))
                ->desc(clienttranslate('Choose one: Gain 1 Combat Strength OR Destroy the card to gain 3 Combat Strength.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_COMBAT_POWER, 1)->build())
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 3)
                        ->gain(GAIN_ID_DESTROY_SELF, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardRed(2207)->name(clienttranslate("Thorin"))
                ->desc(clienttranslate('Gain 1 Combat Strength per Dwarf in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_COMBAT_POWER, 1, COMPONENT_ICON_ID_DWARF)->build())
                ->build(),
        ];
    }
}
