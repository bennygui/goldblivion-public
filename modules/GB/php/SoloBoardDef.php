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

const SOLO_ABILITY_DICE = 1;
const SOLO_ABILITY_DICE_PER_HUMAN = 2;
const SOLO_ABILITY_NUGGET_PER_ELF_1 = 3;
const SOLO_ABILITY_NUGGET_PER_ELF_2 = 4;
const SOLO_ABILITY_NUGGET_PER_HUMAN_2 = 5;
const SOLO_ABILITY_REVEAL_ENEMY = 6;
const SOLO_ABILITY_DESTROY_ENEMY = 7;
const SOLO_ABILITY_NUGGET_1 = 8;
const SOLO_ABILITY_NUGGET_2 = 9;
const SOLO_ABILITY_NUGGET_3 = 10;
const SOLO_ABILITY_NUGGET_4 = 11;
const SOLO_ABILITY_NUGGET_5 = 12;
const SOLO_ABILITY_NUGGET_10 = 13;
const SOLO_ABILITY_MATERIAL_1 = 14;
const SOLO_ABILITY_GOLD_1 = 15;
const SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD = 16;
const SOLO_ABILITY_DESTROY_PLAYER_NUGGET_1 = 17;
const SOLO_ABILITY_DESTROY_PLAYER_NUGGET_2 = 18;
const SOLO_ABILITY_DESTROY_PLAYER_NUGGET_3 = 19;

class SoloBoardDef
{
    public $id;
    public $name;
    public $nuggetConvertRate;
    public $materialConvertRate;
    public $materialConvertNuggetRate;
    public $magicAbilities;
    public $iconAbilities;
    public $iconAbilityDoubles;

    public function __construct()
    {
        $this->id = null;
        $this->name = null;
        $this->nuggetConvertRate = null;
        $this->materialConvertRate = null;
        $this->materialConvertNuggetRate = null;
        $this->magicAbilities = [];
        $this->iconAbilities = [
            COMPONENT_ICON_ID_HUMAN => [],
            COMPONENT_ICON_ID_ELF => [],
            COMPONENT_ICON_ID_DWARF => [],
            COMPONENT_ICON_ID_BUILDING => [],
        ];
        $this->iconAbilityDoubles = false;
    }
}

class SoloBoardDefBuilder
{
    private $def;

    public function __construct()
    {
        $this->def = new SoloBoardDef();
    }

    public function build()
    {
        return $this->def;
    }

    public function id(int $id)
    {
        $this->def->id = $id;
        return $this;
    }

    public function name(string $name)
    {
        $this->def->name = $name;
        return $this;
    }

    public function nuggetGoldRate(int $rate)
    {
        $this->def->nuggetConvertRate = $rate;
        return $this;
    }

    public function materialNuggetRate(int $materialRate, int $nuggetRate)
    {
        $this->def->materialConvertRate = $materialRate;
        $this->def->materialConvertNuggetRate = $nuggetRate;
        return $this;
    }

    public function materialGoldRate(int $rate)
    {
        $this->def->materialConvertRate = $rate;
        $this->def->materialConvertNuggetRate = null;
        return $this;
    }

    public function magic(int $ability)
    {
        $this->def->magicAbilities[] = $ability;
        return $this;
    }

    public function iconHuman(int $ability)
    {
        $this->def->iconAbilities[COMPONENT_ICON_ID_HUMAN][] = $ability;
        return $this;
    }

    public function iconElf(int $ability)
    {
        $this->def->iconAbilities[COMPONENT_ICON_ID_ELF][] = $ability;
        return $this;
    }

    public function iconDwarf(int $ability)
    {
        $this->def->iconAbilities[COMPONENT_ICON_ID_DWARF][] = $ability;
        return $this;
    }

    public function iconBuilding(int $ability)
    {
        $this->def->iconAbilities[COMPONENT_ICON_ID_BUILDING][] = $ability;
        return $this;
    }

    public function iconDoubles()
    {
        $this->def->iconAbilityDoubles = true;
        return $this;
    }
}
