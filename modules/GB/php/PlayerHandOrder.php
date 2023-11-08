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

class PlayerHandOrder extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerId;
    /** @dbcol @dbkey */
    public $componentId;
    /** @dbcol */
    public $componentOrder;
}

class PlayerHandOrderMgr extends \BX\Action\BaseActionRowMgr
{
    private const BASE_ORDER = 1000;

    public function __construct()
    {
        parent::__construct('player_hand_order', \GB\PlayerHandOrder::class);
    }

    public function resetHandOrder()
    {
        $this->db->deleteAllRows();
    }

    public function getPlayerHandComponentOrder(int $playerId)
    {
        return array_flip($this->getPlayerHandInOrder($playerId));
    }

    public function getPlayerHandInOrder(int $playerId)
    {
        $order = [];
        $componentIds = [];

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        foreach ($componentMgr->getCardsInPlayerHand($playerId) as $c) {
            $componentIds[$c->componentId] = true;
            $order[$c->componentId] = self::BASE_ORDER + $c->locationPrimaryOrder;
        }

        foreach ($this->db->getAllRows() as $row) {
            if ($playerId == $row->playerId) {
                $order[$row->componentId] = $row->componentOrder;
            }
        }
        $componentIds = array_keys($componentIds);

        usort($componentIds, fn ($a, $b) => $order[$a] <=> $order[$b]);

        return $componentIds;
    }

    private function moveTo(int $playerId, int $componentId, callable $insertFct)
    {
        $componentIds = $this->getPlayerHandInOrder($playerId);
        $idx = array_search($componentId, $componentIds);
        if ($idx === false) {
            return;
        }
        unset($componentIds[$idx]);
        $componentIds = array_values($insertFct(array_values($componentIds), $idx));

        $this->db->deleteRowsWhereEqual('player_id', $playerId);
        foreach ($componentIds as $i => $id) {
            $row = $this->db->newRow();
            $row->playerId = $playerId;
            $row->componentId = $id;
            $row->componentOrder = $i;
            $this->db->insertRow($row);
        }
    }

    public function moveToStart(int $playerId, int $componentId)
    {
        $this->moveTo($playerId, $componentId, function ($ids, $idx) use ($componentId) {
            array_unshift($ids, $componentId);
            return $ids;
        });
    }

    public function moveToEnd(int $playerId, int $componentId)
    {
        $this->moveTo($playerId, $componentId, function ($ids, $idx) use ($componentId) {
            $ids[] = $componentId;
            return $ids;
        });
    }

    public function moveOneLeft(int $playerId, int $componentId)
    {
        $this->moveTo($playerId, $componentId, function ($ids, $idx) use ($componentId) {
            array_splice($ids, max($idx - 1, 0), 0, [$componentId]);
            return $ids;
        });
    }

    public function moveOneRight(int $playerId, int $componentId)
    {
        $this->moveTo($playerId, $componentId, function ($ids, $idx) use ($componentId) {
            array_splice($ids, $idx + 1, 0, [$componentId]);
            return $ids;
        });
    }
}
