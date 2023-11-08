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

trait ComponentDefMgrEnemy
{
    private static function getComponentDefEnemy()
    {
        return [
            (new ComponentDefBuilder())->enemy(5100)->name(clienttranslate("Forest Enemy 1, Combat Strength of 2"))
                ->desc(clienttranslate('Combat Strength of 2. If defeated: Roll 2 dice.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_FOREST)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 2)
                        ->gain(GAIN_ID_ROLL_DICE, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5101)->name(clienttranslate("Forest Enemy 2, Combat Strength of 3"))
                ->desc(clienttranslate('Combat Strength of 3. If defeated: Gain 5 nuggets.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_FOREST)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 3)
                        ->gain(GAIN_ID_GAIN_NUGGET, 5)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5102)->name(clienttranslate("Forest Enemy 3, Combat Strength of 3"))
                ->desc(clienttranslate('Combat Strength of 3. If defeated: Gain 3 nuggets and Roll a dice.'))
                ->setupCount(1)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_FOREST)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 3)
                        ->gain(GAIN_ID_GAIN_NUGGET, 3)
                        ->gain(GAIN_ID_ROLL_DICE, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5103)->name(clienttranslate("Forest Enemy 4, Combat Strength of 4"))
                ->desc(clienttranslate('Combat Strength of 4. If defeated: Gain 4 nuggets and Gain 1 material.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_FOREST)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 4)
                        ->gain(GAIN_ID_GAIN_NUGGET, 4)
                        ->gain(GAIN_ID_GAIN_MATERIAL, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5104)->name(clienttranslate("Forest Enemy 5, Combat Strength of 5"))
                ->desc(clienttranslate('Combat Strength of 5. If defeated: Gain a Combat card at no cost and Gain 1 Magic token.'))
                ->setupCount(1)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_FOREST)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 5)
                        ->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)
                        ->gain(GAIN_ID_GAIN_MAGIC, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5200)->name(clienttranslate("Mountain Enemy 1, Combat Strength of 6"))
                ->desc(clienttranslate('Combat Strength of 6. If defeated: Gain a Combat card at no cost and Gain 1 gold.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_MOUNTAIN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 6)
                        ->gain(GAIN_ID_GAIN_FREE_RED_CARD, 1)
                        ->gain(GAIN_ID_GAIN_GOLD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5201)->name(clienttranslate("Mountain Enemy 2, Combat Strength of 7"))
                ->desc(clienttranslate('Combat Strength of 7. If defeated: Roll 2 dice and Gain 1 gold.'))
                ->setupCount(1)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_MOUNTAIN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 7)
                        ->gain(GAIN_ID_ROLL_DICE, 2)
                        ->gain(GAIN_ID_GAIN_GOLD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5202)->name(clienttranslate("Mountain Enemy 3, Combat Strength of 7"))
                ->desc(clienttranslate('Combat Strength of 7. If defeated: Gain 10 nuggets.'))
                ->setupCount(1)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_MOUNTAIN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 7)
                        ->gain(GAIN_ID_GAIN_NUGGET, 10)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5203)->name(clienttranslate("Mountain Enemy 4, Combat Strength of 8"))
                ->desc(clienttranslate('Combat Strength of 8. If defeated: Gain 1 Magic token and Gain 1 gold.'))
                ->setupCount(2)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_MOUNTAIN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 8)
                        ->gain(GAIN_ID_GAIN_MAGIC, 1)
                        ->gain(GAIN_ID_GAIN_GOLD, 1)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5204)->name(clienttranslate("Mountain Enemy 5, Combat Strength of 9"))
                ->desc(clienttranslate('Combat Strength of 9. If defeated: Gain 2 gold.'))
                ->setupCount(1)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_MOUNTAIN)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 9)
                        ->gain(GAIN_ID_GAIN_GOLD, 2)
                        ->build()
                )
                ->build(),
            (new ComponentDefBuilder())->enemy(5300)->name(clienttranslate("Cow-Dragon, Combat Strength of 10"))
                ->desc(clienttranslate('Combat Strength of 10. If defeated: Gain 3 gold but do not gain an enemy tile. This enemy always stays on the main board and can be defeated multiple times.'))
                ->setupCount(1)
                ->subCategory(COMPONENT_SUB_CATEGORY_ID_PERMANENT)
                ->ability(
                    (new ComponentAbilityBuilder())
                        ->gain(GAIN_ID_GAIN_COMBAT_POWER, 10)
                        ->gain(GAIN_ID_GAIN_GOLD, 3)
                        ->build()
                )
                ->build(),
        ];
    }
}
