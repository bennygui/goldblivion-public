<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * theisleofcats implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace GB\Debug;

require_once(__DIR__ . '/../../BX/php/Debug.php');

trait GameStatesTrait
{
    use \BX\Debug\GameStatesTrait;

    public function debugLoadBug()
    {
        $this->debugLoadBugInternal(function ($studioPlayerId, $replacePlayerId) {
            return array_merge(
                $this->debugGetSqlForActionCommand($studioPlayerId, $replacePlayerId),
                $this->debugGetSqlForStateFunction($studioPlayerId, $replacePlayerId),
                [
                    "UPDATE `component` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                    "UPDATE `player_state` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                    "UPDATE `game_state` SET round_first_player_id = $studioPlayerId WHERE round_first_player_id = $replacePlayerId",
                ],
            );
        });
    }

    public function debugGetAllRedCardTypes()
    {
        $playerId = $this->getCurrentPlayerId();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $componentMgr->debugGetAllRedCardTypes($playerId);
        $this->debugSendReload();
    }

    public function debugGetCombatDraw(int $combatDraw)
    {
        $playerId = $this->getCurrentPlayerId();
        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\Combat\GainDraw($playerId, $combatDraw));
        $creator->save();
        $this->debugSendReload();
    }

    public function debugGetBlueCard(int $typeId)
    {
        $playerId = $this->getCurrentPlayerId();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $componentMgr->debugGetBlueCard($playerId, $typeId);
        $this->debugSendReload();
    }

    public function debugGetNugget(int $count)
    {
        $playerId = $this->getCurrentPlayerId();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $componentId = array_keys($componentMgr->getAllVisibleForPlayer($playerId))[0];
        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\AbilityActivation\GainNugget($playerId, $componentId, $count, null));
        $creator->save();
        $this->debugSendReload();
    }

    public function debugGetMaterial(int $count)
    {
        $playerId = $this->getCurrentPlayerId();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $componentId = array_keys($componentMgr->getAllVisibleForPlayer($playerId))[0];
        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\AbilityActivation\GainMaterial($playerId, $componentId, $count, null));
        $creator->save();
        $this->debugSendReload();
    }

    public function debugResetMainAction()
    {
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::commit($playerId);
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->resetPlayerActionCountNow($playerId);
        \BX\Action\ActionRowMgrRegister::getMgr('component')->debugMoveBlueCardToHand($playerId);
        $this->debugSendReload();
    }
}
