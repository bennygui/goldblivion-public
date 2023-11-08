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

class ComponentAbility
{
    public $cost;
    public $gains;

    public function __construct()
    {
        $this->cost = null;
        $this->gains = [];
    }

    public function hasCost()
    {
        return ($this->cost !== null);
    }

    public function canPayCost(int $playerNuggets, int $playerMaterial)
    {
        if ($this->cost === null) {
            return true;
        }
        return $this->cost->canPayCost($playerNuggets, $playerMaterial);
    }

    public function payCost(int &$playerNuggets, int &$playerMaterial, int &$nuggetPay, int &$materialPay)
    {
        if ($this->cost === null) {
            return true;
        }
        return $this->cost->payCost($playerNuggets, $playerMaterial, $nuggetPay, $materialPay);
    }

    public function foreachGain(callable $callback)
    {
        foreach ($this->gains as $g) {
            $callback($g);
        }
    }

    public function foreachInstantGain(callable $callback)
    {
        foreach ($this->gains as $g) {
            if (array_search($g->gainTypeId, GAIN_IDS_INSTANT) === false) {
                continue;
            }
            $callback($g);
        }
    }

    public function foreachInteractiveGain(callable $callback)
    {
        foreach ($this->gains as $g) {
            if (array_search($g->gainTypeId, GAIN_IDS_INTERACTIVE) === false) {
                continue;
            }
            $callback($g);
        }
    }

    public function instantGainRequiresCommit()
    {
        foreach ($this->gains as $g) {
            if (
                array_search($g->gainTypeId, GAIN_IDS_INSTANT) !== false
                && array_search($g->gainTypeId, GAIN_IDS_REQUIRES_COMMIT) !== false
            ) {
                return true;
            }
        }
        return false;
    }

    public function hasReactivateAbility()
    {
        foreach ($this->gains as $g) {
            if (array_search($g->gainTypeId, GAIN_IDS_REACTIVATE) !== false) {
                return true;
            }
        }
        return false;
    }

    public function hasDrawRedCardAbility()
    {
        foreach ($this->gains as $g) {
            if ($g->gainTypeId == GAIN_ID_DRAW_RED_CARD) {
                return true;
            }
        }
        return false;
    }

    public function hasStartCombatAbility()
    {
        foreach ($this->gains as $g) {
            if ($g->gainTypeId == GAIN_ID_START_COMBAT) {
                return true;
            }
        }
        return false;
    }

    public function getCombatPower()
    {
        foreach ($this->gains as $g) {
            if ($g->gainTypeId == GAIN_ID_GAIN_COMBAT_POWER) {
                return $g->count;
            }
        }
        return null;
    }

    public function foreachInstantCombat(callable $callback)
    {
        foreach ($this->gains as $g) {
            if (array_search($g->gainTypeId, GAIN_IDS_INSTANT_COMBAT) === false) {
                continue;
            }
            $callback($g);
        }
    }
}

const RESOURCE_TYPE_ID_NUGGET = 1;
const RESOURCE_TYPE_ID_MATERIAL = 2;
const RESOURCE_TYPE_ID_GOLD = 3;

class ComponentCost
{
    public $resourceTypeId;
    public $count;
    public $isMandatory;

    public function __construct(int $resourceTypeId, int $count, bool $isMandatory = true)
    {
        $this->resourceTypeId = $resourceTypeId;
        $this->count = $count;
        $this->isMandatory = $isMandatory;
    }

    public function canPayCost(int $playerNuggets, int $playerMaterial)
    {
        switch ($this->resourceTypeId) {
            case RESOURCE_TYPE_ID_NUGGET:
                return ($playerNuggets >= $this->count);
            case RESOURCE_TYPE_ID_MATERIAL:
                return ($playerMaterial >= $this->count);
            default:
                throw new \BgaSystemException("BUG! Unknown ressource type {$this->resourceTypeId}");
        }
    }

    public function payCost(int &$playerNuggets, int &$playerMaterial, int &$nuggetPay, int &$materialPay)
    {
        if (!$this->canPayCost($playerNuggets, $playerMaterial)) {
            return false;
        }
        switch ($this->resourceTypeId) {
            case RESOURCE_TYPE_ID_NUGGET:
                $playerNuggets -= $this->count;
                $nuggetPay = $this->count;
                break;
            case RESOURCE_TYPE_ID_MATERIAL:
                $playerMaterial -= $this->count;
                $materialPay = $this->count;
                break;
            default:
                throw new \BgaSystemException("BUG! Unknown ressource type {$this->resourceTypeId}");
        }
        return true;
    }
}

const GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET = 1;
const GAIN_ID_GAIN_NUGGET = 2;
const GAIN_ID_GAIN_MATERIAL = 3;
const GAIN_ID_GAIN_GOLD = 4;
const GAIN_ID_GAIN_FREE_RED_CARD = 5;
const GAIN_ID_DRAW_BLUE_CARD = 6;
const GAIN_ID_ROLL_DICE = 7;
const GAIN_ID_REACTIVATE_ICON = 8;
const GAIN_ID_GAIN_COMBAT_POWER = 9;
const GAIN_ID_DRAW_RED_CARD = 10;
const GAIN_ID_COPY_OTHER_RED_CARD = 11;
const GAIN_ID_DESTROY_SELF = 12;
const GAIN_ID_START_COMBAT = 13;
const GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD = 14;
const GAIN_ID_GAIN_MAGIC = 15;

const GAIN_IDS_INSTANT = [
    GAIN_ID_GAIN_NUGGET,
    GAIN_ID_GAIN_MATERIAL,
    GAIN_ID_GAIN_GOLD,
    GAIN_ID_DRAW_BLUE_CARD,
    GAIN_ID_ROLL_DICE,
    GAIN_ID_GAIN_MAGIC,
];
const GAIN_IDS_INSTANT_COMBAT = [
    GAIN_ID_GAIN_COMBAT_POWER,
    GAIN_ID_DRAW_RED_CARD,
    GAIN_ID_DESTROY_SELF,
];
const GAIN_IDS_INTERACTIVE = [
    GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET,
    GAIN_ID_GAIN_FREE_RED_CARD,
    GAIN_ID_REACTIVATE_ICON,
    GAIN_ID_COPY_OTHER_RED_CARD,
    GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD,
];
const GAIN_IDS_REQUIRES_COMMIT = [
    GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET,
    GAIN_ID_GAIN_FREE_RED_CARD,
    GAIN_ID_DRAW_BLUE_CARD,
    GAIN_ID_ROLL_DICE,
    GAIN_ID_DRAW_RED_CARD,
    GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD,
    GAIN_ID_GAIN_MAGIC,
];
const GAIN_IDS_REACTIVATE = [
    GAIN_ID_REACTIVATE_ICON,
    GAIN_ID_COPY_OTHER_RED_CARD,
];

class ComponentGain
{
    public $gainTypeId;
    public $count;
    public $conditionIcon;

    public function __construct(int $gainTypeId, int $count, ?int $conditionIcon = null)
    {
        $this->gainTypeId = $gainTypeId;
        $this->count = $count;
        $this->conditionIcon = $conditionIcon;
    }
}

class ComponentAbilityBuilder
{
    private $ab;

    public function __construct()
    {
        $this->ab = new ComponentAbility();
    }

    public function build()
    {
        return $this->ab;
    }

    public function cost(int $resourceTypeId, int $count, bool $isMandatory = true)
    {
        if ($this->ab->cost !== null)
            throw new \BgaSystemException("BUG! Ability already as a cost");
        $this->ab->cost = new ComponentCost($resourceTypeId, $count, $isMandatory);
        return $this;
    }

    public function gain(int $gainTypeId, int $count, ?int $conditionIcon = null)
    {
        $this->ab->gains[] = new ComponentGain($gainTypeId, $count, $conditionIcon);
        return $this;
    }
}
