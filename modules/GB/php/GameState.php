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
require_once(__DIR__ . '/../../BX/php/Collection.php');

const GAME_STATE_ID = 0;

class GameState extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $gameStateId;
    /** @dbcol */
    public $roundFirstPlayerId;
    /** @dbcol */
    public $soloLostUnfilledMarket;
    /** @dbcol */
    public $soloNuggetCount;
    /** @dbcol */
    public $soloMaterialCount;
    /** @dbcol */
    public $soloGoldCount;
    /** @dbcol */
    public $soloMarketComponentId;
    /** @dbcol */
    public $soloActionList;

    public function __construct()
    {
        $this->gameStateId = GAME_STATE_ID;
        $this->roundFirstPlayerId = null;
        $this->soloLostUnfilledMarket = false;
        $this->soloNuggetCount = 0;
        $this->soloMaterialCount = 0;
        $this->soloGoldCount = 0;
        $this->soloMarketComponentId = null;
        $this->soloActionList = json_encode([]);
    }
}

class GameStateMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('game_state', \GB\GameState::class);
    }

    public function setup(array $playerIdArray)
    {
        $gs = $this->db->newRow();
        $gs->roundFirstPlayerId = $playerIdArray[0];
        $this->db->insertRow($gs);
    }

    public function roundFirstPlayerId()
    {
        return $this->getRowByKey(GAME_STATE_ID)->roundFirstPlayerId;
    }

    public function activateNextFirstPlayerNow()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $playerIdArray = $playerMgr->getAllPlayerIds();
        if (count($playerIdArray) <= 1) {
            return;
        }
        $playerIdArray = \BX\Collection\rotateValueToFront($playerIdArray, $this->roundFirstPlayerId());

        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->roundFirstPlayerId = $playerIdArray[1];
        $this->db->updateRow($gs);
    }

    public function setSoloLostUnfilledMarketAction()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloLostUnfilledMarket = true;
    }

    public function isSoloLostUnfilledMarket()
    {
        return $this->getRowByKey(GAME_STATE_ID)->soloLostUnfilledMarket;
    }

    public function getSoloNuggetCount()
    {
        return $this->getRowByKey(GAME_STATE_ID)->soloNuggetCount;
    }

    public function getSoloMaterialCount()
    {
        return $this->getRowByKey(GAME_STATE_ID)->soloMaterialCount;
    }

    public function getSoloGoldCount()
    {
        return $this->getRowByKey(GAME_STATE_ID)->soloGoldCount;
    }

    public function isEndGameTriggered()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        if ($playerMgr->isEndGameTriggered()) {
            return true;
        }
        if (isGameSolo()) {
            return ($this->getSoloGoldCount() >= ENDING_SCORE);
        }
        return false;
    }

    public function addSoloNuggetCountAction(int $count)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloNuggetCount += $count;
    }

    public function addSoloMaterialCountAction(int $count)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloMaterialCount += $count;
    }

    public function addSoloGoldCountAction(int $count)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloGoldCount += $count;
    }

    public function setSoloMarketComponentIdAction(int $componentId)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloMarketComponentId = $componentId;
    }

    public function clearSoloMarketComponentIdNow()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->soloMarketComponentId = null;
        $this->db->updateRow($gs);
    }

    public function getSoloMarketComponentId()
    {
        return $this->getRowByKey(GAME_STATE_ID)->soloMarketComponentId;
    }

    public function setSoloActionListAction(array $actionList)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloActionList = json_encode(array_values($actionList));
    }

    public function getSoloActionList()
    {
        return json_decode($this->getRowByKey(GAME_STATE_ID)->soloActionList);
    }

    public function isSoloActionListEmpty()
    {
        return (count($this->getSoloActionList()) == 0);
    }
}
