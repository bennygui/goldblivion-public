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

namespace GB\State\Round;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../../../BX/php/Collection.php');
require_once(__DIR__ . '/../Actions/Ability.php');
require_once(__DIR__ . '/../Component.php');

const NB_CARD_TO_DRAW_ROUND_START = 5;

trait GameStatesTrait
{
    public function stRoundStart()
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $playerIdArray = $this->getPlayerIdArray();
        $hasPlayersWithCardsInHand = false;
        foreach ($playerIdArray as $playerId) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \GB\Actions\Ability\DrawBlueCardToPlayerHand($playerId, NB_CARD_TO_DRAW_ROUND_START));
            $creator->commit();
            if (!empty($componentMgr->getCardsInPlayerHand($playerId))) {
                $hasPlayersWithCardsInHand = true;
            }
        }
        if ($hasPlayersWithCardsInHand) {
            $this->gamestate->nextState('chooseCardDevelop');
        } else {
            $this->gamestate->nextState('production');
        }
    }

    public function stRoundChooseCardDevelop()
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $cardsInHandplayerIdArray = [];
        foreach ($this->getPlayerIdArray() as $playerId) {
            if (!empty($componentMgr->getCardsInPlayerHand($playerId))) {
                $cardsInHandplayerIdArray[] = $playerId;
            }
        }
        $this->gamestate->setPlayersMultiactive($cardsInHandplayerIdArray, null, true);
    }

    public function argsRoundChooseCardDevelop()
    {
        $ret = [];
        \BX\Action\ActionCommandMgr::clear();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        foreach ($this->getPlayerIdArray() as $playerId) {
            $cardIds = array_keys($componentMgr->getCardsInPlayerHand($playerId));
            $ret['_private'][$playerId]['componentIds'] = $cardIds;
            if (count($cardIds) == 0) {
                $ret['_private'][$playerId]['canSkip'] = true;
            }
        }
        return $ret;
    }

    public function playerRoundChooseCardDevelop(int $componentId, int $side)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerRoundChooseCardDevelop");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\Ability\PlaceBlueCardInPlayerDevelopment($playerId, $componentId, $side));
        $creator->save();

        $this->gamestate->setPlayerNonMultiactive($playerId, '');
    }
    
    public function playerRoundChooseCardDevelopSkip()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerRoundChooseCardDevelopSkip");
        $playerId = $this->getCurrentPlayerId();
        
        \BX\Action\ActionCommandMgr::clear();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        if (count($componentMgr->getCardsInPlayerHand($playerId)) != 0) {
            throw new \BgaSystemException("Player $playerId cannot skip develop, they have cards in hand");
        }

        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \BX\Action\SendMessage($playerId, clienttranslate('${player_name} does not have any cards and skips development')));
        $creator->save();

        $this->gamestate->setPlayerNonMultiactive($playerId, '');
    }

    public function playerRoundChooseCardDevelopUndo()
    {
        \BX\Lock\Locker::lock();
        $this->gamestate->checkPossibleAction("playerRoundChooseCardDevelopUndo");
        $playerId = $this->getCurrentPlayerId();
        if (array_search($playerId, $this->gamestate->getActivePlayerList()) !== false) {
            throw new \BgaUserException($this->_('You cannot change your development choice right now'));
        }
        \BX\Action\ActionCommandMgr::undoAll($playerId, true);

        $this->gamestate->setPlayersMultiactive([$playerId], '');
    }

    public function stRoundProduction()
    {
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \GB\Actions\Ability\Production($playerId));
            $creator->commit();
            $this->giveExtraTime($playerId);
        }

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $this->gamestate->changeActivePlayer($gameStateMgr->roundFirstPlayerId());

        $this->gamestate->nextState();
    }

    public function stRoundNextPlayer()
    {
        // Commit should already be done
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        foreach ($playerMgr->getAllPlayerIds() as $playerId) {
            if (\BX\Action\ActionCommandMgr::count($playerId) > 0) {
                throw new \BgaSystemException("BUG! Player $playerId still has uncommited actions!");
            }
            if (count(\BX\StateFunction\getAllFunctionCall($playerId)) > 0) {
                throw new \BgaSystemException("BUG! Player $playerId still has uncommited function calls!");
            }
        }

        if (isGameSolo()) {
            $this->gamestate->nextState('solo');
        } else {
            $this->gamestate->nextState('multi');
        }
    }

    public function stRoundNextPlayerPost()
    {
        // Commit should already be done
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        foreach ($playerMgr->getAllPlayerIds() as $playerId) {
            if (\BX\Action\ActionCommandMgr::count($playerId) > 0) {
                throw new \BgaSystemException("BUG! Player $playerId still has uncommited actions!");
            }
            if (count(\BX\StateFunction\getAllFunctionCall($playerId)) > 0) {
                throw new \BgaSystemException("BUG! Player $playerId still has uncommited function calls!");
            }
        }

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        if ($playerStateMgr->allPlayerPassed()) {
            // All player are now 'not passed'
            $playerStateMgr->markAllPlayerNotPassedNow();
            foreach ($playerMgr->getAllPlayerIds() as $playerId) {
                $this->notifyAllPlayers(
                    NTF_UPDATE_PASS,
                    '',
                    [
                        'playerId' => $playerId,
                        'passed' => false,
                    ]
                );
            }

            if ($gameStateMgr->isEndGameTriggered()) {
                // Tie breakers: nuggets * 100 + ennemy
                foreach ($playerMgr->getAllPlayerIds() as $playerId) {
                    $ps = $playerStateMgr->getByPlayerId($playerId);
                    $playerMgr->updatePlayerScoreAuxNow(
                        $playerId,
                        100 * $ps->nuggetCount
                            + count($componentMgr->getPlayerEnemy($playerId))
                    );
                }
                $this->gamestate->nextState('endGame');
                return;
            }

            $this->incStat(1, STATS_TABLE_NB_ROUND);
            $this->notifyAllPlayers(
                NTF_UPDATE_ROUND,
                clienttranslate('A new round starts: buildings are reactivated, cards are shuffled and used magic tokens are flipped and shuffled'),
                [
                    'round' => $this->getStat(STATS_TABLE_NB_ROUND),
                ]
            );

            // Shuffle cards, Reactivate buildings, Shuffle magic
            $playerHandOrderMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_hand_order');
            $playerHandOrderMgr->resetHandOrder();
            foreach ($playerMgr->getAllPlayerIds() as $playerId) {
                $this->notifyPlayer(
                    $playerId,
                    NTF_UPDATE_HAND_ORDER,
                    '',
                    [
                        'handOrder' => $playerHandOrderMgr->getPlayerHandComponentOrder($playerId),
                    ]
                );
                $handCards = $componentMgr->moveHandCardsToDeckNow($playerId);
                $this->notifyPlayer(
                    $playerId,
                    NTF_UPDATE_COMPONENTS,
                    '',
                    [
                        'components' => $handCards,
                        'fast' => true,
                    ]
                );
            }
            $updatedComponents = [];
            $buildings = [];
            foreach ($playerMgr->getAllPlayerIds() as $playerId) {
                $updatedComponents = array_merge(
                    $updatedComponents,
                    \BX\Meta\deepClone($componentMgr->movePlayAreaBlueCardsToDeckNow($playerId)),
                    \BX\Meta\deepClone($componentMgr->movePlayAreaRedCardsToDeckNow($playerId))
                );
                $componentMgr->shufflePlayerBlueDeckNow($playerId);
                $componentMgr->shufflePlayerRedDeckNow($playerId);
                $buildings = array_merge(
                    $buildings,
                    \BX\Meta\deepClone($componentMgr->activateBuildingsNow($playerId))
                );
            }
            $updatedComponents = array_merge(
                $updatedComponents,
                \BX\Meta\deepClone($componentMgr->shuffleMagicNow())
            );
            $this->notifyAllPlayers(
                NTF_UPDATE_COMPONENTS,
                '',
                [
                    'components' => $updatedComponents,
                    'fast' => true,
                ]
            );
            $this->notifyAllPlayers(
                NTF_SHUFFLE_RED_DECK,
                '',
                [
                    'playerIds' => $playerMgr->getAllPlayerIds(),
                ]
            );
            $this->notifyAllPlayers(
                NTF_SHUFFLE_BLUE_DECK,
                '',
                [
                    'playerIds' => $playerMgr->getAllPlayerIds(),
                ]
            );
            $this->notifyAllPlayers(
                NTF_USE_COMPONENTS,
                '',
                [
                    'componentIds' => array_map(fn ($c) => $c->componentId, $buildings),
                    'isUsed' => false,
                ]
            );
            $this->notifyAllPlayers(
                NTF_UPDATE_COUNTS,
                '',
                [
                    'componentCounts' => $componentMgr->getAllCounts(),
                ]
            );

            if (isGameSolo()) {
                $this->gamestate->nextState('startNewRound');
            } else {
                // Activate new first player
                $gameStateMgr->activateNextFirstPlayerNow();
                $roundFirstPlayerId = $gameStateMgr->roundFirstPlayerId();
                $this->notifyAllPlayers(
                    NTF_UPDATE_FIRST_PLAYER,
                    clienttranslate('${player_name} becomes the first player'),
                    [
                        'player_name' => $this->loadPlayersBasicInfos()[$roundFirstPlayerId]['player_name'],
                        'roundFirstPlayerId' => $roundFirstPlayerId,
                    ]
                );

                $this->gamestate->changeActivePlayer($roundFirstPlayerId);

                // Check if there are cards to destroy
                $cardsInMarket = array_values($componentMgr->getCardInAllMarkets());
                switch (count($cardsInMarket)) {
                    case 0:
                        $this->notifyAllPlayers(
                            \BX\Action\NTF_MESSAGE,
                            clienttranslate('No cards to destroy in markets, the first player token has no effect'),
                            []
                        );
                        $this->gamestate->nextState('startNewRound');
                        return;
                    case 1:
                        $creator = new \BX\Action\ActionCommandCreatorCommit($roundFirstPlayerId);
                        $creator->add(new \GB\Actions\Ability\DestroyCard(
                            $roundFirstPlayerId,
                            $cardsInMarket[0]->componentId,
                            null,
                            true
                        ));
                        $creator->commit();
                        $this->gamestate->nextState('startNewRound');
                        return;
                    default:
                        $this->giveExtraTime($roundFirstPlayerId);
                        $this->gamestate->nextState('chooseCardToDestroy');
                        return;
                }
            }
        } else {
            $activePlayerId = $this->getActivePlayerId();
            $playerIdArray = $playerMgr->getAllPlayerIds();
            $playerIdArray = \BX\Collection\rotateValueToFront($playerIdArray, $activePlayerId);
            $playerIdArray[] = array_shift($playerIdArray);
            $newActivePlayerId = null;
            while (count($playerIdArray) > 0) {
                $newActivePlayerId = array_shift($playerIdArray);
                if ($playerStateMgr->hasPlayerPassed($newActivePlayerId)) {
                    $newActivePlayerId = null;
                    continue;
                } else {
                    break;
                }
            }
            if ($newActivePlayerId === null) {
                throw new \BgaSystemException('BUG! No player to activate');
            }
            $this->gamestate->changeActivePlayer($newActivePlayerId);

            // Auto pass if player has no other action
            if (
                !isGameSolo()
                && \BX\BGAGlobal\GlobalMgr::isGameTurnBased()
                && count($componentMgr->getCardsInPlayerHand($newActivePlayerId)) == 0
                && count($this->getPlayableVillages($newActivePlayerId)) == 0
                && count($this->getBuyableCards($newActivePlayerId)) == 0
                && count($this->getPlayableBuildings($newActivePlayerId)) == 0
                && count($componentMgr->getPlayerMagic($newActivePlayerId)) == 0
            ) {
                $creator = new \BX\Action\ActionCommandCreatorCommit($newActivePlayerId);
                $creator->add(new \GB\Actions\Ability\Pass($newActivePlayerId, true));
                $creator->add(new \GB\Actions\Ability\ConvertNuggetToGold($newActivePlayerId, true));
                $creator->commit();
                $this->giveExtraTime($newActivePlayerId);
                $this->gamestate->nextState('nextPlayerRoundAutoPass');
            } else {
                $this->gamestate->nextState('nextPlayerRound');
            }
        }
    }

    public function argRoundChooseCardToDestroy()
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        return [
            'componentIds' => array_keys($componentMgr->getCardInAllMarkets()),
        ];
    }

    public function playerRoundChooseCardToDestroy(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerRoundChooseCardToDestroy");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Ability\DestroyCard($playerId, $componentId));
        $creator->commit();

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState();
    }
}
