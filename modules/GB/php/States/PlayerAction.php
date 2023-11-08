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

namespace GB\State\PlayerAction;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../../../BX/php/ActiveState.php');
require_once(__DIR__ . '/../Actions/Ability.php');
require_once(__DIR__ . '/../Actions/AbilityActivation.php');

trait GameStatesTrait
{
    public function stPlayerActionStart()
    {
        // Reset main action count
        $playerId = $this->getActivePlayerId();
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $playerStateMgr->resetPlayerActionCountNow($playerId);
        $this->giveExtraTime($playerId);
        $this->gamestate->nextState();
    }

    public function stPlayerActionLoop()
    {
        $playerId = $this->getActivePlayerId();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($playerId);

        $hasMainActions = ($ps->actionCount < nbMainAction());
        $hasBonusActions = false;
        if ($ps->nuggetCount >= \GB\Actions\Ability\ConvertNuggetToGold::CONVERT_RATIO) {
            $hasBonusActions = true;
        }
        if (count($this->getPlayableBuildings($playerId)) > 0) {
            $hasBonusActions = true;
        }
        if (count($componentMgr->getPlayerMagic($playerId)) > 0) {
            $hasBonusActions = true;
        }

        if (!$hasMainActions && !$hasBonusActions && \BX\Action\ActionCommandMgr::count($playerId) == 0) {
            $this->gamestate->nextState('nextPlayerInRound');
        } else {
            $this->gamestate->nextState('chooseAction');
        }
    }

    public function argPlayerActionChooseAction()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
                $ps = $playerStateMgr->getByPlayerId($playerId);

                $couldPlayMainActions = ($ps->actionCount < nbMainAction());
                $hasMainActions = false;
                $hasBonusActions = false;

                $actions = [
                    'hasBlueCards' => count($componentMgr->getPlayerBlueDeck($playerId)) > 0,
                ];

                // Main actions
                $cardIdsInPlayerHand = array_keys($componentMgr->getCardsInPlayerHand($playerId));
                if ($couldPlayMainActions && count($cardIdsInPlayerHand) > 0) {
                    $actions['cardIdsInPlayerHand'] = $cardIdsInPlayerHand;
                    $hasMainActions = true;
                } else {
                    $actions['cardIdsInPlayerHand'] = [];
                }

                $villageIds = array_keys($this->getPlayableVillages($playerId));
                if ($couldPlayMainActions && count($villageIds) > 0) {
                    $actions['villageIds'] = $villageIds;
                    $hasMainActions = true;
                } else {
                    $actions['villageIds'] = [];
                }

                $cardIdsInMarkets = array_keys($this->getBuyableCards($playerId));
                if ($couldPlayMainActions && count($cardIdsInMarkets) > 0) {
                    $actions['cardIdsInMarkets'] = $cardIdsInMarkets;
                    $hasMainActions = true;
                } else {
                    $actions['cardIdsInMarkets'] = [];
                }

                // Bonus actions
                if ($ps->nuggetCount >= \GB\Actions\Ability\ConvertNuggetToGold::CONVERT_RATIO) {
                    $actions['canConvertNuggetToGold'] = true;
                    $hasBonusActions = true;
                }

                $buildingCardIds = array_keys($this->getPlayableBuildings($playerId));
                if (count($buildingCardIds) > 0) {
                    $actions['buildingCardIds'] = $buildingCardIds;
                    $hasBonusActions = true;
                } else {
                    $actions['buildingCardIds'] = [];
                }

                $playerMagicIds = array_keys($componentMgr->getPlayerMagic($playerId));
                if (count($playerMagicIds) > 0) {
                    $actions['playerMagicIds'] = $playerMagicIds;
                    $hasBonusActions = true;
                } else {
                    $actions['playerMagicIds'] = [];
                }

                // Pass action
                if (!$couldPlayMainActions || $ps->passed) {
                    $actions['canEndTurn'] = true;
                } else {
                    $actions['canPass'] = true;
                    if (isGameSolo() && count($cardIdsInPlayerHand) > 0) {
                        $actions['canPass'] = false;
                    }
                }

                $log = [];
                $logArgs = [
                    'i18n' => ['mainAction', 'bonusAction', 'passAction'],
                ];

                if ($hasMainActions) {
                    $log[] = '${mainAction} (${mainActionUsed}/${mainActionTotal})';
                    $logArgs['mainAction'] = clienttranslate('Main action');
                    $logArgs['mainActionUsed'] = $ps->actionCount + 1;
                    $logArgs['mainActionTotal'] = nbMainAction();
                }
                if ($hasBonusActions) {
                    $log[] = '${bonusAction}';
                    $logArgs['bonusAction'] = clienttranslate('Bonus action');
                }

                if (!$couldPlayMainActions || $ps->passed) {
                    $log[] = '${passAction}';
                    $logArgs['passAction'] = clienttranslate('End Turn');
                } else {
                    if (!(isGameSolo() && count($cardIdsInPlayerHand) > 0)) {
                        $log[] = '${passAction}';
                        $logArgs['passAction'] = clienttranslate('Pass');
                    }
                }

                return [
                    'actions' => [
                        'log' => implode(', ', $log),
                        'args' => $logArgs,
                    ],
                    '_private' => [
                        $playerId => $actions,
                    ]
                ];
            }
        );
    }

    public function playerActionPass()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionPass");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Ability\Pass($playerId));
        $creator->add(new \GB\Actions\Ability\ConvertNuggetToGold($playerId, true));
        $creator->commit();

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState('nextPlayerInChooseAction');
    }

    public function playerActionEndTurn()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionEndTurn");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($playerId);
        if ($ps->actionCount < nbMainAction() && !$ps->passed) {
            throw new \BgaSystemException('BUG! Cannot end turn: still has actions or not passed');
        }

        \BX\Action\ActionCommandMgr::commit($playerId);

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState('nextPlayerInChooseAction');
    }

    public function playerActionBonusConvertNuggetToGold()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionBonusConvertNuggetToGold");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\Ability\ConvertNuggetToGold($playerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'chooseActionLoop'));
        $creator->save();
    }

    public function playerActionBonusActivateBuilding(int $componentId, int $side)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionBonusActivateBuilding");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $mustCommit = $this->componentAbilityInstantGainAbilityRequiresCommit($componentId, $side);
        $creator = \BX\Action\buildActionCommandCreator($playerId, $mustCommit);
        $creator->add(new \GB\Actions\Ability\ActivateBuilding($playerId, $componentId));
        $this->callAbilityActivation($componentId, $creator, $mustCommit, $side);
        $creator->saveOrCommit();
    }

    public function playerActionBonusPlayMagic(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionBonusPlayMagic");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = \BX\Action\buildActionCommandCreator($playerId, $this->componentHasOneAbilityAndInstantGainAbilityRequiresCommit($componentId));
        $creator->add(new \GB\Actions\Ability\PlayMagic($playerId, $componentId));
        $this->callAbilityActivation($componentId, $creator);
        $creator->saveOrCommit();
    }

    public function playerActionMainPlayCard(int $componentId, int $side)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionMainPlayCard");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $mustCommit = $this->componentAbilityInstantGainAbilityRequiresCommit($componentId, $side);
        $creator = \BX\Action\buildActionCommandCreator($playerId, $mustCommit);
        $creator->add(new \GB\Actions\Ability\PlayMainAction($playerId));
        $creator->add(new \GB\Actions\Ability\PlayCardFromHand($playerId, $componentId));
        $this->callAbilityActivation($componentId, $creator, $mustCommit, $side);
        $creator->saveOrCommit();
    }

    public function playerActionMainPlayVillage(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionMainPlayVillage");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = \BX\Action\buildActionCommandCreator($playerId, $this->componentHasOneAbilityAndInstantGainAbilityRequiresCommit($componentId));
        $creator->add(new \GB\Actions\Ability\PlayMainAction($playerId));
        $creator->add(new \GB\Actions\Ability\PlayVillageStart($playerId, $componentId));
        $this->callAbilityActivation($componentId, $creator);
        $creator->saveOrCommit();
    }

    public function playerActionMainBuyCard(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerActionMainBuyCard");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Ability\PlayMainAction($playerId));
        $creator->add(new \GB\Actions\Ability\BuyCard($playerId, $componentId));
        if (isGameSoloLostUnfilledMarket()) {
            $creator->add(new \BX\ActiveState\JumpStateActionCommand($playerId, STATE_SOLO_LOST_UNFILLED_MARKET_ID));
        } else {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'chooseActionLoop'));
        }
        $creator->commit();
    }

    private function getPlayableVillages(int $playerId)
    {
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($playerId);

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $villages = $componentMgr->getVisibleVillages();
        foreach ($villages as $id => $c) {
            if (!$c->def()->canPayAnyAbilityCost($ps->nuggetCount, $ps->materialCount)) {
                unset($villages[$id]);
            }
        }
        return $villages;
    }

    private function getBuyableCards(int $playerId)
    {
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($playerId);

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $cards = $componentMgr->getCardInAllMarkets();
        foreach ($cards as $id => $c) {
            if (!$c->def()->canPayComponentCost($ps->nuggetCount, $ps->materialCount)) {
                unset($cards[$id]);
            }
        }
        return $cards;
    }

    private function getPlayableBuildings(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $cards = $componentMgr->getCardsInPlayerBuildingPlayArea($playerId);
        foreach ($cards as $id => $c) {
            if ($c->isUsed) {
                unset($cards[$id]);
            }
        }
        return $cards;
    }

    private function componentHasOneAbilityAndInstantGainAbilityRequiresCommit(int $componentId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $c = $componentMgr->getById($componentId);
        if ($c === null) {
            return false;
        }
        if ($c->def()->hasAbilityChoice()) {
            return false;
        }
        $ability = $c->def()->abilities[0];
        return $ability->instantGainRequiresCommit();
    }

    private function componentAbilityInstantGainAbilityRequiresCommit(int $componentId, int $side)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $c = $componentMgr->getById($componentId);
        if ($c === null) {
            return false;
        }
        if (!$c->def()->isValidAbilityChoice($side)) {
            return true;
        }
        $ability = $c->def()->abilities[$side];
        return $ability->instantGainRequiresCommit();
    }
}
