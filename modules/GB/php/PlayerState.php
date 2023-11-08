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

require_once(__DIR__ . '/../../BX/php/Action.php');

class PlayerState extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerId;
    /** @dbcol */
    public $nuggetCount;
    /** @dbcol */
    public $materialCount;
    /** @dbcol */
    public $actionCount;
    /** @dbcol */
    public $passed;
    /** @dbcol */
    public $combatPower;
    /** @dbcol */
    public $combatDraw;
    /** @dbcol */
    public $combatEnemyComponentId;
    /** @dbcol */
    public $combatInteractiveComponentId;
    /** @dbcol */
    public $combatInteractiveReactivateComponentId;
    /** @dbcol */
    public $statGainedNugget;
    /** @dbcol */
    public $statGainedMaterial;
    /** @dbcol */
    public $statGainedGoldFromNugget;
    /** @dbcol */
    public $statGainedBlueCard;
    /** @dbcol */
    public $statGainedRedCard;
    /** @dbcol */
    public $statGainedMagicToken;
    /** @dbcol */
    public $statCombatWon;
    /** @dbcol */
    public $statCombatLost;
    /** @dbcol */
    public $statCombatWonCow;
    /** @dbcol */
    public $statDestroyedBlueMarket;
    /** @dbcol */
    public $statDestroyedRedMarket;

    public function __construct()
    {
        $this->playerId = null;
        $this->nuggetCount = 0;
        $this->materialCount = 0;
        $this->actionCount = 0;
        $this->passed = false;
        $this->combatPower = 0;
        $this->combatDraw = 0;
        $this->combatEnemyComponentId = null;
        $this->combatInteractiveComponentId = null;
        $this->combatInteractiveReactivateComponentId = null;
        $this->statGainedNugget = 0;
        $this->statGainedMaterial = 0;
        $this->statGainedGoldFromNugget = 0;
        $this->statGainedBlueCard = 0;
        $this->statGainedRedCard = 0;
        $this->statGainedMagicToken = 0;
        $this->statCombatWon = 0;
        $this->statCombatLost = 0;
        $this->statCombatWonCow = 0;
        $this->statDestroyedBlueMarket = 0;
        $this->statDestroyedRedMarket = 0;
    }

    public function gainNugget(int $count)
    {
        $this->nuggetCount += $count;
        if ($this->nuggetCount < 0) {
            throw new \BgaSystemException('BUG! nuggetCount cannot be less than 0');
        }
        if ($count > 0) {
            $this->statGainedNugget += $count;
        }
    }

    public function payNugget(int $count)
    {
        $this->gainNugget(-1 * $count);
    }

    public function loseNugget(int $count)
    {
        $this->nuggetCount -= $count;
        if ($this->nuggetCount < 0) {
            $this->nuggetCount = 0;
        }
    }

    public function gainMaterial(int $count)
    {
        $maxed = false;
        if ($count > 0) {
            $this->statGainedMaterial += min($this->materialCount + $count, MAX_MATERIAL) - $this->materialCount;
        }
        $this->materialCount += $count;
        if ($this->materialCount > MAX_MATERIAL) {
            $maxed = true;
            $this->materialCount = MAX_MATERIAL;
        }
        if ($this->materialCount < 0) {
            throw new \BgaSystemException('BUG! materialCount cannot be less than 0');
        }
        return $maxed;
    }

    public function payMaterial(int $count)
    {
        $this->gainMaterial(-1 * $count);
    }

    public function clearCombat()
    {
        $this->combatPower = 0;
        $this->combatDraw = 0;
        $this->combatEnemyComponentId = null;
        $this->combatInteractiveComponentId = null;
        $this->combatInteractiveReactivateComponentId = null;
    }

    public function gainCombatPower($count)
    {
        if ($count < 0) {
            throw new \BgaSystemException('BUG! count cannot be less than 0 for combat strength');
        }
        $this->combatPower += $count;
    }

    public function gainCombatDraw($count)
    {
        if ($count < 0) {
            throw new \BgaSystemException('BUG! count cannot be less than 0 for combat draw');
        }
        $this->combatDraw += $count;
    }

    public function combatDraw()
    {
        if ($this->combatDraw <= 0) {
            throw new \BgaSystemException('BUG! cannot combat draw below zero');
        }
        $this->combatDraw -= 1;
    }
}

class PlayerStateMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('player_state', \GB\PlayerState::class);
    }

    public function setup(array $playerIdArray)
    {
        foreach ($playerIdArray as $playerId) {
            $ps = $this->db->newRow();
            $ps->playerId = $playerId;
            $this->db->insertRow($ps);
        }
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getByPlayerId(int $playerId)
    {
        return $this->getRowByKey($playerId);
    }

    public function allPlayerPassed()
    {
        foreach ($this->getAll() as $ps) {
            if (!$ps->passed) {
                return false;
            }
        }
        return true;
    }

    public function hasPlayerPassed(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        return $ps->passed;
    }

    public function markAllPlayerNotPassedNow()
    {
        foreach ($this->getAll() as $ps) {
            $ps->passed = false;
            $this->db->updateRow($ps);
        }
    }

    public function resetPlayerActionCountNow(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->actionCount = 0;
        $this->db->updateRow($ps);
    }

    public function getPlayerCombatPower(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        return $ps->combatPower;
    }

    public function getPlayerCombatDraw(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        return $ps->combatDraw;
    }

    public function getPlayerEnemyCombatPower(int $playerId)
    {
        $componentId = $this->getPlayerCombatEnemyComponentId($playerId);
        if ($componentId === null) {
            return 0;
        }
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $enemy = $componentMgr->getById($componentId);
        if ($enemy === null) {
            return 0;
        }
        return $enemy->def()->getCombatPower();
    }

    public function getPlayerCombatEnemyComponentId(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        return $ps->combatEnemyComponentId;
    }

    public function getPlayerCombatInteractiveComponentId(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        return $ps->combatInteractiveComponentId;
    }

    public function zombiePassNow($playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->actionCount = 0;
        $ps->passed = true;
        $ps->clearCombat();
        $this->db->updateRow($ps);
    }
}
