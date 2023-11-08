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

namespace GB\State\HandOrder;

require_once(__DIR__ . '/../../../BX/php/Action.php');

trait GameStatesTrait
{
    public function handOrderToStart(int $componentId)
    {
        $this->handOrderMove($componentId, 'moveToStart');
    }

    public function handOrderToLeft(int $componentId)
    {
        $this->handOrderMove($componentId, 'moveOneLeft');
    }

    public function handOrderToRight(int $componentId)
    {
        $this->handOrderMove($componentId, 'moveOneRight');
    }

    public function handOrderToEnd(int $componentId)
    {
        $this->handOrderMove($componentId, 'moveToEnd');
    }

    private function handOrderMove(int $componentId, string $moveFct)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if (array_search($playerId, $this->getPlayerIdArray()) === false) {
            throw new \BgaSystemException('BUG! Only players can tag cards');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);

        $playerHandOrderMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_hand_order');
        $order = $playerHandOrderMgr->getPlayerHandComponentOrder($playerId);
        if (!array_key_exists($componentId, $order)) {
            throw new \BgaSystemException('BUG! Only cards from hand can be tagged');
        }

        $playerHandOrderMgr->{$moveFct}($playerId, $componentId);

        $this->notifyPlayer(
            $playerId,
            NTF_UPDATE_HAND_ORDER,
            '',
            [
                'handOrder' => $playerHandOrderMgr->getPlayerHandComponentOrder($playerId),
            ]
        );
    }
}
