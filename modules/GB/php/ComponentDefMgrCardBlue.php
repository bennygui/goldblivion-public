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

trait ComponentDefMgrCardBlue
{
    private static function getComponentDefCardBlue()
    {
        return array_merge(
            // Blue player
            self::getComponentDefCardBlueStarting(1100, COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE),
            // Yellow player
            self::getComponentDefCardBlueStarting(1110, COMPONENT_SUB_CATEGORY_ID_PLAYER_YELLOW),
            // Red player
            self::getComponentDefCardBlueStarting(1120, COMPONENT_SUB_CATEGORY_ID_PLAYER_RED),
            // Green player
            self::getComponentDefCardBlueStarting(1130, COMPONENT_SUB_CATEGORY_ID_PLAYER_GREEN),

            self::getComponentDefCardBlueNoble(),
            self::getComponentDefCardBlueHumanoid(),
            self::getComponentDefCardBlueBuilding(),
        );
    }

    private static function getComponentDefCardBlueStarting(int $baseTypeId, int $subCategoryId)
    {
        return [
            (new ComponentDefBuilder())->cardBlue($baseTypeId + 0)->name(clienttranslate("Tom"))
                ->desc(clienttranslate('Gain 1 nugget.'))
                ->setupCount(3)
                ->subCategory($subCategoryId)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_NUGGET, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue($baseTypeId + 1)->name(clienttranslate("Naya"))
                ->desc(clienttranslate('Gain 1 nugget per Elf icon in your play area.'))
                ->setupCount(2)
                ->subCategory($subCategoryId)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ELF)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue($baseTypeId + 2)->name(clienttranslate("Gloric"))
                ->desc(clienttranslate('Start a combat.'))
                ->setupCount(1)
                ->subCategory($subCategoryId)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_START_COMBAT, 1)->build())
                ->build(),
        ];
    }

    private static function getComponentDefCardBlueNoble()
    {
        return [
            (new ComponentDefBuilder())->cardBlue(1200)->name(clienttranslate("Ariane"))
                ->desc(clienttranslate('Destroy a card (GOLDblivion or Combat) and Gain 2 nuggets.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_NOBLE)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET, 1)
                        ->gain(GAIN_ID_GAIN_NUGGET, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1201)->name(clienttranslate("Blaze"))
                ->desc(clienttranslate('Gain a Combat card at no cost.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_NOBLE)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1202)->name(clienttranslate("Charles"))
                ->desc(clienttranslate('Choose one: Gain 2 nuggets OR Draw a GOLDblivion card from your deck.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_NOBLE)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_NUGGET, 2)->build())
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_DRAW_BLUE_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1203)->name(clienttranslate("Glasstok"))
                ->desc(clienttranslate('Roll a dice.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_NOBLE)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_ROLL_DICE, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1204)->name(clienttranslate("Jade"))
                ->desc(clienttranslate('Gain a nugget.'))
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_NOBLE)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_NUGGET, 1)->build())
                ->build(),
        ];
    }

    private static function getComponentDefCardBlueHumanoid()
    {
        return [
            (new ComponentDefBuilder())->cardBlue(1400)->name(clienttranslate("Amazone"))
                ->desc(clienttranslate('Start a combat with +1 Combat Strength.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 4)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_START_COMBAT, 1)
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1401)->name(clienttranslate("Assassins"))
                ->desc(clienttranslate('Destroy a card (GOLDblivion or Combat) and Gain 1 material.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 6)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET, 1)
                        ->gain(GAIN_ID_GAIN_MATERIAL, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1402)->name(clienttranslate("Bud & Buddy"))
                ->desc(clienttranslate('Gain 1 nugget per Elf icon in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ELF)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1403)->name(clienttranslate("Christina"))
                ->desc(clienttranslate('Gain 1 nugget per Elf icon in your play area and Gain 1 nugget per Enemy you defeated.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 2)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ELF)
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ENEMY)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1404)->name(clienttranslate("Eve & Lily"))
                ->desc(clienttranslate('Gain 2 nuggets and Draw a GOLDblivion card from your deck.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 7)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 2)
                        ->gain(GAIN_ID_DRAW_BLUE_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1405)->name(clienttranslate("Harmony"))
                ->desc(clienttranslate('Gain 2 Combat cards at no cost.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 5)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_FREE_RED_CARD, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1406)->name(clienttranslate("Picassol"))
                ->desc(clienttranslate('Roll a dice and Draw a GOLDblivion card from your deck.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 5)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_ROLL_DICE, 1)
                        ->gain(GAIN_ID_DRAW_BLUE_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1407)->name(clienttranslate("Timmy"))
                ->desc(clienttranslate('Gain 1 nugget and Draw a GOLDblivion card from your deck.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->icon(COMPONENT_ICON_ID_ELF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 1)
                        ->gain(GAIN_ID_DRAW_BLUE_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1408)->name(clienttranslate("Britney"))
                ->desc(clienttranslate('Choose one: Gain a non-building card from the GOLDblivion market at no cost OR Reactivate a Human card in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 4)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD, 1)->build())
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_REACTIVATE_ICON, 1, COMPONENT_ICON_ID_HUMAN)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1409)->name(clienttranslate("BRO"))
                ->desc(clienttranslate('Start a combat and Draw one more Combat card'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 6)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_START_COMBAT, 1)
                        ->gain(GAIN_ID_DRAW_RED_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1410)->name(clienttranslate("Bruno"))
                ->desc(clienttranslate('Destroy a card (GOLDblivion or Combat) and Gain 3 nuggets.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 2)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET, 1)
                        ->gain(GAIN_ID_GAIN_NUGGET, 3)
                        ->build()
                )
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_DRAW_BLUE_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1411)->name(clienttranslate("Magda"))
                ->desc(clienttranslate('Choose one: Gain 4 nuggets OR Gain 1 material.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 4)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_NUGGET, 4)->build())
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_MATERIAL, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1412)->name(clienttranslate("Magus"))
                ->desc(clienttranslate('Roll a dice and Gain 1 Magic token.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 5)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_ROLL_DICE, 1)
                        ->gain(GAIN_ID_GAIN_MAGIC, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1413)->name(clienttranslate("Vlad"))
                ->desc(clienttranslate('Destroy 2 cards (GOLDblivion or Combat) and Gain 2 nuggets per Enemy you defeated.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 7)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET, 2)
                        ->gain(GAIN_ID_GAIN_NUGGET, 2, COMPONENT_ICON_ID_ENEMY)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1414)->name(clienttranslate("Wanda"))
                ->desc(clienttranslate('Roll 2 dice.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->icon(COMPONENT_ICON_ID_HUMAN)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_ROLL_DICE, 2)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1415)->name(clienttranslate("Archimus"))
                ->desc(clienttranslate('Pay 1 material to gain 1 gold.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 2)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->cost(RESOURCE_TYPE_ID_MATERIAL, 1, false)
                        ->gain(GAIN_ID_GAIN_GOLD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1416)->name(clienttranslate("Boss"))
                ->desc(clienttranslate('Gain 2 nuggets and Reactivate an Elf card in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 5)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 2)
                        ->gain(GAIN_ID_REACTIVATE_ICON, 1, COMPONENT_ICON_ID_ELF)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1417)->name(clienttranslate("Colin"))
                ->desc(clienttranslate('Gain 1 material and Gain 1 nugget per Enemy you defeated.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 5)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_MATERIAL, 1)
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ENEMY)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1418)->name(clienttranslate("Goldie"))
                ->desc(clienttranslate('Gain 4 nuggets and Roll a dice.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 5)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 4)
                        ->gain(GAIN_ID_ROLL_DICE, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1419)->name(clienttranslate("Menthor"))
                ->desc(clienttranslate('Choose one: Gain a Combat card at no cost OR Start a combat.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 2)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)->build())
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_START_COMBAT, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1420)->name(clienttranslate("Red"))
                ->desc(clienttranslate('Choose one: Pay 2 nuggets to gain 1 material OR Reactivate a Building card in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 3)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability((new ComponentAbilityBuilder())->cost(RESOURCE_TYPE_ID_NUGGET, 2, false)->gain(GAIN_ID_GAIN_MATERIAL, 1)->build())
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_REACTIVATE_ICON, 1, COMPONENT_ICON_ID_BUILDING)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1421)->name(clienttranslate("Rolly Rockers"))
                ->desc(clienttranslate('Gain 1 material and Draw 1 GOLDblivion card from your deck.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 7)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_MATERIAL, 1)
                        ->gain(GAIN_ID_DRAW_BLUE_CARD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1422)->name(clienttranslate("Thardard"))
                ->desc(clienttranslate('Pay 1 material to gain 10 nuggets.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_NUGGET, 6)
                ->icon(COMPONENT_ICON_ID_DWARF)
                ->ability((new ComponentAbilityBuilder())->cost(RESOURCE_TYPE_ID_MATERIAL, 1, false)->gain(GAIN_ID_GAIN_NUGGET, 10)->build())
                ->build(),
        ];
    }

    private static function getComponentDefCardBlueBuilding()
    {
        return [
            (new ComponentDefBuilder())->cardBlue(1300)->name(clienttranslate("Bar"))
                ->desc(clienttranslate('Reactivate a Dwarf card in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 3)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_REACTIVATE_ICON, 1, COMPONENT_ICON_ID_DWARF)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1301)->name(clienttranslate("Castrum"))
                ->desc(clienttranslate('Gain 2 nuggets per Buildings in your play area and Roll 1 dice per Human in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 4)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 2, COMPONENT_ICON_ID_BUILDING)
                        ->gain(GAIN_ID_ROLL_DICE, 1, COMPONENT_ICON_ID_HUMAN)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1302)->name(clienttranslate("Conversus"))
                ->desc(clienttranslate('Choose one: Pay 1 material to gain 4 nuggets OR Pay 4 nuggets to gain 1 material.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 1)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability((new ComponentAbilityBuilder())->cost(RESOURCE_TYPE_ID_MATERIAL, 1)->gain(GAIN_ID_GAIN_NUGGET, 4)->build())
                ->ability((new ComponentAbilityBuilder())->cost(RESOURCE_TYPE_ID_NUGGET, 4)->gain(GAIN_ID_GAIN_MATERIAL, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1303)->name(clienttranslate("Forge"))
                ->desc(clienttranslate('Choose one: Gain 1 Combat card at no cost OR Pay 1 material to gain 1 gold.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 2)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)->build())
                ->ability((new ComponentAbilityBuilder())->cost(RESOURCE_TYPE_ID_MATERIAL, 1)->gain(GAIN_ID_GAIN_GOLD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1304)->name(clienttranslate("Magika"))
                ->desc(clienttranslate('Draw 1 GOLDblivion card from your deck.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 3)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_DRAW_BLUE_CARD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1305)->name(clienttranslate("Market"))
                ->desc(clienttranslate('Gain 1 nugget per Enemy you defeated and Gain 1 nugget per Human in your play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 2)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ENEMY)
                        ->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_HUMAN)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1306)->name(clienttranslate("Mine"))
                ->desc(clienttranslate('Gain 1 gold'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 4)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_GOLD, 1)->build())
                ->build(),
            (new ComponentDefBuilder())->cardBlue(1307)->name(clienttranslate("Neverland"))
                ->desc(clienttranslate('Gain 1 nugget per Elf in you play area.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_DECK)
                ->cost(RESOURCE_TYPE_ID_MATERIAL, 2)
                ->icon(COMPONENT_ICON_ID_BUILDING)
                ->ability((new ComponentAbilityBuilder())->gain(GAIN_ID_GAIN_NUGGET, 1, COMPONENT_ICON_ID_ELF)->build())
                ->build(),
        ];
    }
}
