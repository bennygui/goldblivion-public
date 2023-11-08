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

require_once('SoloBoardDef.php');

class SoloBoardDefMgr
{
    public static function getAll()
    {
        self::initSoloBoardDefs();
        return self::$soloBoardDefs;
    }

    public static function getById(int $id)
    {
        self::initSoloBoardDefs();
        if (!array_key_exists($id, self::$soloBoardDefs)) {
            return null;
        }
        return self::$soloBoardDefs[$id];
    }

    private static $soloBoardDefs;

    private static function initSoloBoardDefs()
    {
        if (self::$soloBoardDefs != null) {
            return;
        }
        self::$soloBoardDefs = [];
        foreach (self::getSoloBoardDefs() as $def) {
            self::$soloBoardDefs[$def->id] = $def;
        }
    }

    private static function getSoloBoardDefs()
    {
        return [
            (new SoloBoardDefBuilder())->id(GAME_OPTION_SOLO_NOBLE_VALUE_EASY_ARIANE)
                ->name(clienttranslate('Ariane'))
                ->nuggetGoldRate(7)
                ->materialNuggetRate(1, 2)
                ->magic(SOLO_ABILITY_REVEAL_ENEMY)->magic(SOLO_ABILITY_DICE)
                ->iconDoubles()

                ->iconHuman(SOLO_ABILITY_DICE)
                ->iconHuman(SOLO_ABILITY_DICE)

                ->iconElf(SOLO_ABILITY_NUGGET_PER_ELF_1)

                ->iconDwarf(SOLO_ABILITY_DESTROY_ENEMY)
                ->iconDwarf(SOLO_ABILITY_NUGGET_2)

                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_DICE)

                ->build(),
            (new SoloBoardDefBuilder())->id(GAME_OPTION_SOLO_NOBLE_VALUE_NORMAL_CHARLES)
                ->name(clienttranslate('Charles'))
                ->nuggetGoldRate(7)
                ->materialGoldRate(2)
                ->magic(SOLO_ABILITY_NUGGET_1)->magic(SOLO_ABILITY_DICE)
                ->iconDoubles()

                ->iconHuman(SOLO_ABILITY_DESTROY_PLAYER_NUGGET_2)
                ->iconHuman(SOLO_ABILITY_DICE)
                ->iconHuman(SOLO_ABILITY_DICE)

                ->iconElf(SOLO_ABILITY_NUGGET_PER_ELF_1)

                ->iconDwarf(SOLO_ABILITY_DESTROY_ENEMY)
                ->iconDwarf(SOLO_ABILITY_REVEAL_ENEMY)

                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_MATERIAL_1)

                ->build(),
            (new SoloBoardDefBuilder())->id(GAME_OPTION_SOLO_NOBLE_VALUE_HARD_BLAZE)
                ->name(clienttranslate('Blaze'))
                ->nuggetGoldRate(7)
                ->materialGoldRate(1)
                ->magic(SOLO_ABILITY_REVEAL_ENEMY)->magic(SOLO_ABILITY_DESTROY_PLAYER_NUGGET_3)

                ->iconHuman(SOLO_ABILITY_NUGGET_PER_HUMAN_2)

                ->iconElf(SOLO_ABILITY_NUGGET_PER_ELF_1)

                ->iconDwarf(SOLO_ABILITY_DESTROY_ENEMY)
                ->iconDwarf(SOLO_ABILITY_DICE)

                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_REVEAL_ENEMY)
                ->iconBuilding(SOLO_ABILITY_NUGGET_3)

                ->build(),
            (new SoloBoardDefBuilder())->id(GAME_OPTION_SOLO_NOBLE_VALUE_HARD_JADE)
                ->name(clienttranslate('Jade'))
                ->nuggetGoldRate(10)
                ->materialGoldRate(3)
                ->magic(SOLO_ABILITY_DESTROY_PLAYER_NUGGET_1)->magic(SOLO_ABILITY_DICE)

                ->iconHuman(SOLO_ABILITY_DICE_PER_HUMAN)

                ->iconElf(SOLO_ABILITY_NUGGET_PER_ELF_2)

                ->iconDwarf(SOLO_ABILITY_REVEAL_ENEMY)
                ->iconDwarf(SOLO_ABILITY_DESTROY_ENEMY)

                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD)
                ->iconBuilding(SOLO_ABILITY_MATERIAL_1)

                ->build(),
        ];
    }
}
