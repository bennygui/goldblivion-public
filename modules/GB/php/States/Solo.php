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

namespace GB\State\Solo;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/Solo.php');

trait GameStatesTrait
{
    public function stSoloLostUnfilledMarket()
    {
        $this->preGameEnd();
        $playerId = $this->getActivePlayerId();
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $playerMgr->updatePlayerScoreNow($playerId, -1);
        $this->gamestate->nextState();
    }

    public function stSoloEnter()
    {
        $playerId = $this->getActivePlayerId();
        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Solo\RollMarketDice($playerId));
        $creator->commit();

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $componentId = $gameStateMgr->getSoloMarketComponentId();
        $icons = $componentMgr->getById($componentId)->def()->uniqueIcons();

        if (count($icons) > 1) {
            $this->gamestate->nextState('choose');
        } else {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \GB\Actions\Solo\MoveMarketCardToIcon($playerId, $icons[0]));
            $creator->commit();
            if (isGameSoloLostUnfilledMarket()) {
                $this->gamestate->jumpToState(STATE_SOLO_LOST_UNFILLED_MARKET_ID);
            } else {
                $this->gamestate->nextState('activate');
            }
        }
    }

    public function argSoloChooseMarketActivation()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');

                $componentId = $gameStateMgr->getSoloMarketComponentId();
                return [
                    'iconIds' => $componentMgr->getById($componentId)->def()->uniqueIcons(),
                ];
            }
        );
    }

    public function soloChooseMarketActivation(int $iconId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("soloChooseMarketActivation");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Solo\MoveMarketCardToIcon($playerId, $iconId, false));
        $creator->commit();

        if (isGameSoloLostUnfilledMarket()) {
            $this->gamestate->jumpToState(STATE_SOLO_LOST_UNFILLED_MARKET_ID);
        } else {
            $this->gamestate->nextState();
        }
    }

    public function stSoloMarketActivation()
    {
        $playerId = $this->getActivePlayerId();

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $interactive = false;
        while (!$interactive && !$gameStateMgr->isSoloActionListEmpty()) {
            $removeAction = new \GB\Actions\Solo\RemoveFirstSoloAction($playerId);
            $creator->add($removeAction);
            $soloAction = $removeAction->getFirstSoloAction();
            switch ($soloAction) {
                case \GB\SOLO_ABILITY_DICE:
                    $creator->add(new \GB\Actions\Solo\GainRollDice($playerId, 1));
                    break;
                case \GB\SOLO_ABILITY_DICE_PER_HUMAN:
                    $creator->add(new \GB\Actions\Solo\GainRollDice($playerId, 1, \GB\COMPONENT_ICON_ID_HUMAN));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_PER_ELF_1:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 1, \GB\COMPONENT_ICON_ID_ELF));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_PER_ELF_2:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 2, \GB\COMPONENT_ICON_ID_ELF));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_PER_HUMAN_2:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 2, \GB\COMPONENT_ICON_ID_HUMAN));
                    break;
                case \GB\SOLO_ABILITY_REVEAL_ENEMY:
                    if (count($componentMgr->getAllAccessibleHiddenEnemyLocationsForSoloMode()) > 0) {
                        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'revealEnemy'));
                        $interactive = true;
                    } else {
                        $creator->add(
                            new \BX\Action\SendMessage(
                                $playerId,
                                clienttranslate('All enemy tiles are visible or unreachable, no enemy to reveal')
                            )
                        );
                    }
                    break;
                case \GB\SOLO_ABILITY_DESTROY_ENEMY:
                    if (count($componentMgr->getAllEnemiesInMarket()) > 0) {
                        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'destroyEnemy'));
                        $interactive = true;
                    } else {
                        $creator->add(
                            new \BX\Action\SendMessage(
                                $playerId,
                                clienttranslate('No enemy tiles are visible, the Solo Noble has no enemy to destroy')
                            )
                        );
                    }
                    break;
                case \GB\SOLO_ABILITY_NUGGET_1:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 1));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_2:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 2));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_3:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 3));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_4:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 4));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_5:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 5));
                    break;
                case \GB\SOLO_ABILITY_NUGGET_10:
                    $creator->add(new \GB\Actions\Solo\GainNugget($playerId, 10));
                    break;
                case \GB\SOLO_ABILITY_MATERIAL_1:
                    $creator->add(new \GB\Actions\Solo\GainMaterial($playerId));
                    break;
                case \GB\SOLO_ABILITY_GOLD_1:
                    $creator->add(new \GB\Actions\Solo\GainGold($playerId));
                    break;
                case \GB\SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD:
                    $creator->add(new \GB\Actions\Solo\DestroyRightMarketCard($playerId));
                    break;
                case \GB\SOLO_ABILITY_DESTROY_PLAYER_NUGGET_1:
                    $creator->add(new \GB\Actions\Solo\DestroyPlayerNugget($playerId, 1));
                    break;
                case \GB\SOLO_ABILITY_DESTROY_PLAYER_NUGGET_2:
                    $creator->add(new \GB\Actions\Solo\DestroyPlayerNugget($playerId, 2));
                    break;
                case \GB\SOLO_ABILITY_DESTROY_PLAYER_NUGGET_3:
                    $creator->add(new \GB\Actions\Solo\DestroyPlayerNugget($playerId, 3));
                    break;
                default:
                    throw new \BgaSystemException("stSoloMarketActivation: Unknow solo action $soloAction");
            }
        }
        $isGameLost = isGameSoloLostUnfilledMarket();
        if ($isGameLost) {
            $creator->add(new \BX\ActiveState\JumpStateActionCommand($playerId, STATE_SOLO_LOST_UNFILLED_MARKET_ID));
        }
        $creator->commit();
        if ($isGameLost) {
            return;
        }
        if (!$interactive) {
            $gameStateMgr->clearSoloMarketComponentIdNow();
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \GB\Actions\Solo\SoloConversion($playerId));
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'endSolo'));
            $creator->commit();
        }
    }

    public function argSoloRevealEnemy()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $locationIds = $componentMgr->getAllAccessibleHiddenEnemyLocationsForSoloMode();
                return [
                    'locationIds' => $locationIds,
                ];
            }
        );
    }

    public function soloRevealEnemy(int $locationId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("soloRevealEnemy");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Solo\RevealEnemy($playerId, $locationId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->commit();
    }

    public function argSoloDestroyEnemy()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $components = $componentMgr->getAllEnemiesInMarket();
                return [
                    'componentIds' => array_keys($components),
                ];
            }
        );
    }

    public function soloDestroyEnemy(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("soloDestroyEnemy");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Solo\DestroyEnemy($playerId, $componentId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->commit();
    }
}
