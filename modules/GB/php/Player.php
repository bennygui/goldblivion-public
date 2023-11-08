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

require_once(__DIR__ . '/../../BX/php/Player.php');

const COLOR_ID_RED = 1;
const COLOR_ID_GREEN = 2;
const COLOR_ID_BLUE = 3;
const COLOR_ID_YELLOW = 4;

const COLOR_TO_COLOR_NAME = [
    'ff0000' => 'red',
    '008000' => 'green',
    '0000ff' => 'blue',
    'fbff00' => 'yellow',
];

const COLOR_TO_COLOR_ID = [
    'ff0000' => COLOR_ID_RED,
    '008000' => COLOR_ID_GREEN,
    '0000ff' => COLOR_ID_BLUE,
    'fbff00' => COLOR_ID_YELLOW,
];

const SOLO_NOBLE_TO_COLOR_ID = [
    GAME_OPTION_SOLO_NOBLE_VALUE_EASY_ARIANE => COLOR_ID_YELLOW,
    GAME_OPTION_SOLO_NOBLE_VALUE_NORMAL_CHARLES => COLOR_ID_BLUE,
    GAME_OPTION_SOLO_NOBLE_VALUE_HARD_BLAZE => COLOR_ID_RED,
    GAME_OPTION_SOLO_NOBLE_VALUE_HARD_JADE => COLOR_ID_GREEN,
];

class Player extends \BX\Player\Player
{
    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        $playerColorName = '';
        if (array_key_exists($ret['player_color'], COLOR_TO_COLOR_NAME)) {
            $playerColorName = COLOR_TO_COLOR_NAME[$ret['player_color']];
        }
        $ret['player_color_name'] = $playerColorName;
        return $ret;
    }

    public function playerColorId()
    {
        if (array_key_exists($this->playerColor, COLOR_TO_COLOR_ID)) {
            return COLOR_TO_COLOR_ID[$this->playerColor];
        }
        return null;
    }
}

class PlayerMgr extends \BX\Player\PlayerMgr
{
    public function __construct()
    {
        parent::__construct(\GB\Player::class);
    }

    public function setup(array $setupNewGamePlayers, array $colors)
    {
        if (count($setupNewGamePlayers) == 1) {
            $soloNobleId = \BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_SOLO_NOBLE_ID);
            if ($soloNobleId === null) {
                $soloNobleId = GAME_OPTION_SOLO_NOBLE_VALUE_EASY_ARIANE;
            }
            $colorIdToColor = array_flip(COLOR_TO_COLOR_ID);
            $nobleColor = $colorIdToColor[SOLO_NOBLE_TO_COLOR_ID[$soloNobleId]];
            $colors = array_values(array_filter($colors, fn($c) => $c != $nobleColor));
        }
        parent::setup($setupNewGamePlayers, $colors);
        return $colors;
    }

    public function isEndGameTriggered()
    {
        foreach ($this->getAll() as $p) {
            if ($p->playerScore >= ENDING_SCORE) {
                return true;
            }
        }
        return false;
    }

    public function getSoloNobleColorName()
    {
        if (!isGameSolo()) {
            return null;
        }
        $noble = gameSoloNoble();
        $colorIdToColor = array_flip(COLOR_TO_COLOR_ID);
        return COLOR_TO_COLOR_NAME[$colorIdToColor[SOLO_NOBLE_TO_COLOR_ID[$noble]]];
    }
}
