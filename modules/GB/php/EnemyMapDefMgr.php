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

require_once('EnemyMapDef.php');

// Enemy hexes positions:
//  \  8  9  10 11 12 /
//   \  4  5   6  7  /
//    \  1   2   3 /

class EnemyMapDefMgr
{
    private const ENEMY_ID_PERMANENT = 10;

    public static function getAll()
    {
        self::initEnemyMapDefs();
        return self::$enemyDefs;
    }

    public static function getAllForest()
    {
        self::initEnemyMapDefs();
        return array_filter(self::$enemyDefs, fn ($ed) => $ed->isForest);
    }

    public static function getAllMountain()
    {
        self::initEnemyMapDefs();
        return array_filter(self::$enemyDefs, fn ($ed) => !$ed->isForest);
    }

    public static function getPermanent()
    {
        self::initEnemyMapDefs();
        return self::getById(self::ENEMY_ID_PERMANENT);
    }

    public static function getById(int $id)
    {
        self::initEnemyMapDefs();
        if (!array_key_exists($id, self::$enemyDefs)) {
            return null;
        }
        return self::$enemyDefs[$id];
    }

    private static $enemyDefs;

    private static function initEnemyMapDefs()
    {
        if (self::$enemyDefs != null) {
            return;
        }
        self::$enemyDefs = [];
        foreach (self::getEnemyMapDefs() as $def) {
            self::$enemyDefs[$def->id] = $def;
        }
    }

    private static function getEnemyMapDefs()
    {
        return [
            (new EnemyMapDefBuilder())->id(1)->forest()->double()
                ->alwaysAccessible()
                ->draws(3)
                ->neighbors([2, 5, 4])
                ->build(),
            (new EnemyMapDefBuilder())->id(2)->mountain()->double()
                ->alwaysAccessible()
                ->draws(2)
                ->neighbors([1, 5, 6, 3])
                ->build(),
            (new EnemyMapDefBuilder())->id(3)->forest()->double()
                ->alwaysAccessible()
                ->draws(3)
                ->neighbors([2, 6, 7])
                ->build(),
            (new EnemyMapDefBuilder())->id(4)->forest()
                ->alwaysAccessible()
                ->draws(2)
                ->neighbors([1, 5, 9, 8])
                ->build(),
            (new EnemyMapDefBuilder())->id(5)->mountain()
                ->draws(0)
                ->neighbors([1, 2, 4, 6, 9, 10])
                ->build(),
            (new EnemyMapDefBuilder())->id(6)->mountain()
                ->draws(0)
                ->neighbors([2, 3, 5, 7, 10, 11])
                ->build(),
            (new EnemyMapDefBuilder())->id(7)->forest()
                ->alwaysAccessible()
                ->draws(2)
                ->neighbors([3, 6, 11, 12])
                ->build(),
            (new EnemyMapDefBuilder())->id(8)->forest()
                ->alwaysAccessible()
                ->draws(2)
                ->neighbors([4, 9])
                ->build(),
            (new EnemyMapDefBuilder())->id(9)->mountain()
                ->draws(0)
                ->neighbors([8, 4, 5, 10])
                ->build(),
            (new EnemyMapDefBuilder())->id(10)->mountain()->permanent()
                ->draws(0)
                ->neighbors([9, 5, 6, 11])
                ->build(),
            (new EnemyMapDefBuilder())->id(11)->mountain()
                ->draws(0)
                ->neighbors([10, 6, 7, 12])
                ->build(),
            (new EnemyMapDefBuilder())->id(12)->forest()
                ->alwaysAccessible()
                ->draws(2)
                ->neighbors([11, 7])
                ->build(),
        ];
    }
}
