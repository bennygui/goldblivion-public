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
 * stats.inc.php
 *
 * goldblivion game statistics description
 *
 */

require_once('modules/GB/php/Globals.php');

$stats_type = [
    // Statistics global to table
    'table' => [
        STATS_TABLE_NB_ROUND => ['id' => 10, 'name' => totranslate('Nb Rounds'), 'type' => 'int'],
    ],

    // Statistics for each player
    'player' => [
        STATS_PLAYER_GOLD_AT_END => ['id' => 10, 'name' => totranslate('Gold at game end (score)'), 'type' => 'int'],
        STATS_PLAYER_NUGGET_AT_END => ['id' => 11, 'name' => totranslate('Nuggets at game end (first tiebreaker)'), 'type' => 'int'],
        STATS_PLAYER_ENEMY_TILE_AT_END => ['id' => 12, 'name' => totranslate('Enemy tiles at game end (second tiebreaker)'), 'type' => 'int'],
        STATS_PLAYER_MATERIAL_AT_END => ['id' => 13, 'name' => totranslate('Material at game end'), 'type' => 'int'],
        STATS_PLAYER_CARDS_IN_NUGGET_DEVELOPMENT_AT_END => ['id' => 14, 'name' => totranslate('Cards in nugget development'), 'type' => 'int'],
        STATS_PLAYER_CARDS_IN_MATERIAL_DEVELOPMENT_AT_END => ['id' => 15, 'name' => totranslate('Cards in material development'), 'type' => 'int'],
        STATS_PLAYER_GAINED_NUGGET => ['id' => 16, 'name' => totranslate('Gained nuggets'), 'type' => 'int'],
        STATS_PLAYER_GAINED_MATERIAL => ['id' => 17, 'name' => totranslate('Gained material'), 'type' => 'int'],
        STATS_PLAYER_GAINED_GOLD_FROM_NUGGET => ['id' => 18, 'name' => totranslate('Gained gold from nuggets'), 'type' => 'int'],
        STATS_PLAYER_GAINED_BLUE_CARD => ['id' => 19, 'name' => totranslate('Gained and bought GOLDblivion cards'), 'type' => 'int'],
        STATS_PLAYER_GAINED_RED_CARD => ['id' => 20, 'name' => totranslate('Gained and bought Combat cards'), 'type' => 'int'],
        STATS_PLAYER_GAINED_MAGIC_TOKEN => ['id' => 21, 'name' => totranslate('Gained magic tokens'), 'type' => 'int'],
        STATS_PLAYER_COMBAT_LOST => ['id' => 22, 'name' => totranslate('Lost combat'), 'type' => 'int'],
        STATS_PLAYER_COMBAT_WON => ['id' => 23, 'name' => totranslate('Won combat'), 'type' => 'int'],
        STATS_PLAYER_COMBAT_WON_COW => ['id' => 24, 'name' => totranslate('Won combat against the Cow-Dragon'), 'type' => 'int'],
        STATS_PLAYER_DESTROYED_BLUE_MARKET => ['id' => 25, 'name' => totranslate('Destroyed GOLDblivion cards in market'), 'type' => 'int'],
        STATS_PLAYER_DESTROYED_RED_MARKET => ['id' => 26, 'name' => totranslate('Destroyed Combat cards in market'), 'type' => 'int'],
        STATS_PLAYER_ICONS_HUMAN => ['id' => 27, 'name' => totranslate('Total Human Icons on all GOLDblivion cards'), 'type' => 'int'],
        STATS_PLAYER_ICONS_ELF => ['id' => 28, 'name' => totranslate('Total Elf Icons on all GOLDblivion cards'), 'type' => 'int'],
        STATS_PLAYER_ICONS_DWARF => ['id' => 29, 'name' => totranslate('Total Dwarf Icons on all GOLDblivion cards'), 'type' => 'int'],
        STATS_PLAYER_ICONS_BUILDING => ['id' => 30, 'name' => totranslate('Total Building Icons on all GOLDblivion cards'), 'type' => 'int'],

        STATS_PLAYER_SOLO_GOLD_AT_END => ['id' => 50, 'name' => totranslate('Solo Noble: Gold at game end'), 'type' => 'int'],
        STATS_PLAYER_SOLO_NUGGET_AT_END => ['id' => 51, 'name' => totranslate('Solo Noble: Nuggets at game end (first tiebreaker)'), 'type' => 'int'],
        STATS_PLAYER_SOLO_ENEMY_TILE_AT_END => ['id' => 53, 'name' => totranslate('Solo Noble: Enemy tiles at game end (second tiebreaker)'), 'type' => 'int'],
        STATS_PLAYER_SOLO_MATERIAL_AT_END => ['id' => 52, 'name' => totranslate('Solo Noble: Material at game end'), 'type' => 'int'],
        STATS_PLAYER_SOLO_CARDS_HUMAN => ['id' => 54, 'name' => totranslate('Solo Noble: Cards under board, Human'), 'type' => 'int'],
        STATS_PLAYER_SOLO_CARDS_ELF => ['id' => 55, 'name' => totranslate('Solo Noble: Cards under board, Elf'), 'type' => 'int'],
        STATS_PLAYER_SOLO_CARDS_DWARF => ['id' => 56, 'name' => totranslate('Solo Noble: Cards under board, Dwarf'), 'type' => 'int'],
        STATS_PLAYER_SOLO_CARDS_BUILDING => ['id' => 57, 'name' => totranslate('Solo Noble: Cards under board, Building'), 'type' => 'int'],
    ],
];
