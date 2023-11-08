<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * goldblivion game options description
 *
 */

require_once('modules/GB/php/Globals.php');

$game_options = [
    GAME_OPTION_SOLO_NOBLE_ID => [
        'name' => totranslate('Solo Difficulty Level'),
        'default' => GAME_OPTION_SOLO_NOBLE_VALUE_EASY_ARIANE,
        'level' => 'base',
        'values' => [
            GAME_OPTION_SOLO_NOBLE_VALUE_EASY_ARIANE => [
                'name' => totranslate('Easy: Ariane'),
                'tmdisplay' => totranslate('Easy Solo (Ariane)'),
                'description' => totranslate('Easy solo difficulty level against Ariane'),
            ],
            GAME_OPTION_SOLO_NOBLE_VALUE_NORMAL_CHARLES => [
                'name' => totranslate('Normal: Charles'),
                'tmdisplay' => totranslate('Normal Solo (Charles)'),
                'description' => totranslate('Normal solo difficulty level against Charles'),
                'nobeginner' => true,
            ],
            GAME_OPTION_SOLO_NOBLE_VALUE_HARD_BLAZE => [
                'name' => totranslate('Hard: Blaze'),
                'tmdisplay' => totranslate('Hard Solo (Blaze)'),
                'description' => totranslate('Hard solo difficulty level against Blaze'),
                'nobeginner' => true,
            ],
            GAME_OPTION_SOLO_NOBLE_VALUE_HARD_JADE => [
                'name' => totranslate('Hard: Jade'),
                'tmdisplay' => totranslate('Hard Solo (Jade)'),
                'description' => totranslate('Hard solo difficulty level against Jade'),
                'nobeginner' => true,
            ],
        ],
        'displaycondition' => [
            [
                'type' => 'maxplayers',
                'value' => 1,
            ],
        ],
    ],
    GAME_OPTION_SOLO_STARTING_NOBLE_ID => [
        'name' => totranslate('Starting Noble Draft'),
        'default' => GAME_OPTION_SOLO_STARTING_NOBLE_VALUE_ALL,
        'level' => 'base',
        'values' => [
            GAME_OPTION_SOLO_STARTING_NOBLE_VALUE_ALL => [
                'name' => totranslate('All Nobles'),
                'tmdisplay' => totranslate('All Nobles'),
                'description' => totranslate('You get to choose one Noble from all the Nobles in the game at the start of the game'),
            ],
            GAME_OPTION_SOLO_STARTING_NOBLE_VALUE_ONE_RANDOM => [
                'name' => totranslate('Random Noble'),
                'tmdisplay' => totranslate('Random Noble'),
                'description' => totranslate('You get one random Noble at the start of the game'),
            ],
        ],
        'displaycondition' => [
            [
                'type' => 'maxplayers',
                'value' => 1,
            ],
        ],
    ],
];

$game_preferences = [];
