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

class EnemyMapDef
{
    public $id;
    public $isForest;
    public $isDouble;
    public $isPermanent;
    public $isAlwaysAccessible;
    public $baseDrawCount;
    public $neighborIds;

    public function __construct()
    {
        $this->id = null;
        $this->isForest = true;
        $this->isDouble = false;
        $this->isPermanent = false;
        $this->isAlwaysAccessible = false;
        $this->baseDrawCount = 0;
        $this->neighborIds = [];
    }
}

class EnemyMapDefBuilder
{
    private $def;

    public function __construct()
    {
        $this->def = new EnemyMapDef();
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

    public function forest()
    {
        $this->def->isForest = true;
        return $this;
    }

    public function mountain()
    {
        $this->def->isForest = false;
        return $this;
    }

    public function double()
    {
        $this->def->isDouble = true;
        return $this;
    }

    public function permanent()
    {
        $this->def->isPermanent = true;
        return $this;
    }

    public function alwaysAccessible()
    {
        $this->def->isAlwaysAccessible = true;
        return $this;
    }

    public function draws(int $drawCount)
    {
        $this->def->baseDrawCount = $drawCount;
        return $this;
    }

    public function neighbors(array $ids)
    {
        $this->def->neighborIds = $ids;
        return $this;
    }
}
