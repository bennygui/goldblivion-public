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

namespace GB\State\AbilityActivation;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/AbilityActivation.php');
require_once(__DIR__ . '/../Actions/Combat.php');

trait GameStatesTrait
{
    public function stAbilityActivationEnter()
    {
        $playerId = $this->getActivePlayerId();
        $c = \GB\Actions\AbilityActivation\getActivatedComponent($playerId);

        $side = \GB\Actions\AbilityActivation\getActivatedAbilityIndex($playerId);
        if ($side !== null) {
            $creator = \BX\Action\buildActionCommandCreator($playerId, \GB\Actions\AbilityActivation\getActivatedMustCommit($playerId));
            $creator->add(new \GB\Actions\AbilityActivation\ChooseActivationSide($playerId, $side, false));
            $creator->saveOrCommit();
            $this->gamestate->nextState('activateInstant');
        } else if ($c->def()->hasAbilityChoice()) {
            throw new \BgaSystemException('BUG! Side of activation should have already be chosen');
        } else {
            $creator = \BX\Action\buildActionCommandCreator($playerId, \GB\Actions\AbilityActivation\getActivatedMustCommit($playerId));
            $creator->add(new \GB\Actions\AbilityActivation\ChooseActivationSide($playerId, 0, true));
            $creator->saveOrCommit();
            $this->gamestate->nextState('activateInstant');
        }
    }

    public function stAbilityActivationExit()
    {
        $playerId = $this->getActivePlayerId();
        if (count(\BX\StateFunction\getAllFunctionCall($playerId, \GB\Actions\AbilityActivation\StateFunction::class)) == 0) {
            $this->gamestate->nextState();
            return;
        }
        $c = \GB\Actions\AbilityActivation\getActivatedComponent($playerId);
        $creator = \BX\Action\buildActionCommandCreator($playerId, \BX\Action\ActionCommandMgr::count($playerId) == 0);
        if ($c->def()->isVillage()) {
            $creator->add(new \GB\Actions\Ability\PlayVillageEnd($playerId, $c->componentId));
        }
        $creator->add(new \BX\StateFunction\StateFunctionReturn($playerId, \GB\Actions\AbilityActivation\StateFunction::class, true));
        $creator->saveOrCommit();
    }

    public function stAbilityActivationInstant()
    {
        $playerId = $this->getActivePlayerId();

        $creator = \BX\Action\buildActionCommandCreator(
            $playerId,
            $this->instantGainAbilityRequiresCommit($playerId) || \GB\Actions\AbilityActivation\getActivatedMustCommit($playerId)
        );
        if ($this->addPaymentAbility($creator)) {
            $this->addInstantGainAbility($creator);
        }
        if ($this->abilityActivationIsCombat($playerId)) {
            $this->addInstantCombatAbility($creator);
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'enterCombatAbility'));
        } else if (\GB\Actions\AbilityActivation\getInteractiveAbilityCounter($playerId) > 0) {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'enterInteractiveAbility'));
        } else {
            $creator->add(new \BX\ActiveState\JumpStateActionCommand($playerId, STATE_ABILITY_ACTIVATION_EXIT_ID));
        }
        $creator->saveOrCommit();
    }

    public function stAbilityActivationInteractiveLoop()
    {
        $playerId = $this->getActivePlayerId();
        if (\GB\Actions\AbilityActivation\getInteractiveAbilityCounter($playerId) == 0) {
            $this->gamestate->nextState('activationExit');
            return;
        }
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $componentId = \GB\Actions\AbilityActivation\getActivatedComponentId($playerId);
        $gain = \GB\Actions\AbilityActivation\getInteractiveAbilityGain($playerId);
        switch ($gain->gainTypeId) {
            case \GB\GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET:
                if (
                    count($componentMgr->getCardInAllMarkets()) > 0
                    || (isGameSolo() && $gameStateMgr->getSoloNuggetCount() > 0)
                ) {
                    $this->gamestate->nextState('destroy');
                } else {
                    $this->gamestate->nextState('activationExit');
                }
                break;
            case \GB\GAIN_ID_GAIN_FREE_RED_CARD:
                if (
                    count($componentMgr->getCardInRedMarket()) > 0
                    || count($componentMgr->getPlayerIdsWithCardsInRedDeck($playerId)) > 0
                ) {
                    $this->gamestate->nextState('gainRed');
                } else {
                    $this->gamestate->nextState('activationExit');
                }
                break;
            case \GB\GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD:
                if (count($componentMgr->getHumanoidCardInBlueMarket()) > 0) {
                    $this->gamestate->nextState('gainBlue');
                } else {
                    $this->gamestate->nextState('activationExit');
                }
                break;
            case \GB\GAIN_ID_REACTIVATE_ICON:
                if ($gain->conditionIcon == \GB\COMPONENT_ICON_ID_BUILDING) {
                    if (count($componentMgr->getUsedCardsInPlayerBuildingPlayArea($playerId)) > 0) {
                        $this->gamestate->nextState('reactivateBuilding');
                    } else {
                        $this->gamestate->nextState('activationExit');
                    }
                } else {
                    if (count($componentMgr->getCardsInPlayerBluePlayAreaForReactivation($playerId, $gain->conditionIcon, $componentId)) > 0) {
                        $this->gamestate->nextState('reactivateHumanoid');
                    } else {
                        $this->gamestate->nextState('activationExit');
                    }
                }
                break;
            default:
                throw new \BgaSystemException("Unknow interactive gain gainTypeId {$gain->gainTypeId}");
        }
    }

    public function argAbilityActivationInteractiveDestroy()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $total = \GB\Actions\AbilityActivation\getInteractiveAbilityTotal($playerId);
                $cardCount = min(
                    count($componentMgr->getCardInAllMarkets()),
                    $total
                );
                $nuggetCount = (isGameSolo()
                    ? min($gameStateMgr->getSoloNuggetCount(), $total)
                    : 0
                );
                return [
                    'cardCount' => $cardCount,
                    'componentIds' => array_keys($componentMgr->getCardInAllMarkets()),
                    'destroySoloNuggetCount' => $nuggetCount,
                ];
            }
        );
    }

    public function abilityActivationInteractiveDestroy(array $componentIds, int $soloNugget)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("abilityActivationInteractiveDestroy");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        if (!isGameSolo() && $soloNugget != 0) {
            throw new \BgaSystemException("abilityActivationInteractiveDestroy: soloNugget is $soloNugget but must be 0 when not solo");
        }

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $total = \GB\Actions\AbilityActivation\getInteractiveAbilityTotal($playerId);
        $cardCount = count($componentMgr->getCardInAllMarkets());
        if (isGameSolo()) {
            if ($soloNugget < 0) {
                throw new \BgaSystemException("abilityActivationInteractiveDestroy soloNugget is negative: $soloNugget");
            }
            $nuggetCount = $gameStateMgr->getSoloNuggetCount();
            if (count($componentIds) > $cardCount || count($componentIds) > $total) {
                throw new \BgaSystemException("abilityActivationInteractiveDestroy invalid number of cards (total=$total): " . count($componentIds) . "vs $cardCount");
            }
            if ($soloNugget > $nuggetCount || $soloNugget > $total) {
                throw new \BgaSystemException("abilityActivationInteractiveDestroy invalid number of nuggets (total=$total): $soloNugget vs $nuggetCount");
            }
            $invalidCount = true;
            switch ($total) {
                case 1:
                    // Nugget: 0
                    if ($nuggetCount == 0 && $cardCount >= 1 && $soloNugget == 0 && count($componentIds) == 1) {
                        $invalidCount = false;
                    }
                    // Nugget: 1+
                    if ($nuggetCount >= 1 && $cardCount == 0 && $soloNugget == 1 && count($componentIds) == 0) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 1 && $cardCount >= 1 && $soloNugget == 1 && count($componentIds) == 0) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 1 && $cardCount >= 1 && $soloNugget == 0 && count($componentIds) == 1) {
                        $invalidCount = false;
                    }
                    break;
                case 2:
                    // Nugget: 0
                    if ($nuggetCount == 0 && $cardCount == 1 && $soloNugget == 0 && count($componentIds) == 1) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount == 0 && $cardCount >= 2 && $soloNugget == 0 && count($componentIds) == 2) {
                        $invalidCount = false;
                    }
                    // Nugget: 1
                    if ($nuggetCount == 1 && $cardCount == 0 && $soloNugget == 1 && count($componentIds) == 0) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount == 1 && $cardCount >= 1 && $soloNugget == 1 && count($componentIds) == 1) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount == 1 && $cardCount >= 2 && $soloNugget == 0 && count($componentIds) == 2) {
                        $invalidCount = false;
                    }
                    // Nugget: 2+
                    if ($nuggetCount >= 2 && $cardCount == 0 && $soloNugget == 2 && count($componentIds) == 0) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 2 && $cardCount == 1 && $soloNugget == 2 && count($componentIds) == 0) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 2 && $cardCount == 1 && $soloNugget == 1 && count($componentIds) == 1) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 2 && $cardCount >= 2 && $soloNugget == 2 && count($componentIds) == 0) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 2 && $cardCount >= 2 && $soloNugget == 1 && count($componentIds) == 1) {
                        $invalidCount = false;
                    }
                    if ($nuggetCount >= 2 && $cardCount >= 2 && $soloNugget == 0 && count($componentIds) == 2) {
                        $invalidCount = false;
                    }
                    break;
                default:
                    throw new \BgaSystemException("abilityActivationInteractiveDestroy invalid number of actions (total=$total)");
            }
            if ($invalidCount) {
                if (count($componentIds) == 0) {
                    throw new \BgaUserException($this->_('You must select more cards'));
                } else {
                    throw new \BgaUserException($this->_('You must select less cards'));
                }
            }
        } else {
            if (count($componentIds) != min($total, $cardCount)) {
                throw new \BgaSystemException("abilityActivationInteractiveDestroy invalid number of actions " . count($componentIds) . " != min($total, $cardCount)");
            }
        }

        $creator = \BX\Action\buildActionCommandCreator($playerId, count($componentIds) > 0);
        while (\GB\Actions\AbilityActivation\getInteractiveAbilityCounter($playerId) > 0) {
            $creator->add(new \GB\Actions\AbilityActivation\UseInteractiveAbility($playerId));
        }
        if ($soloNugget > 0) {
            $creator->add(new \GB\Actions\Solo\DestroySoloNobleNugget($playerId, $soloNugget));
        }
        foreach ($componentIds as $id) {
            $creator->add(new \GB\Actions\Ability\DestroyCard($playerId, $id));
        }
        if (isGameSoloLostUnfilledMarket()) {
            $creator->add(new \BX\ActiveState\JumpStateActionCommand($playerId, STATE_SOLO_LOST_UNFILLED_MARKET_ID));
        } else {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        }
        $creator->saveOrCommit();
    }

    public function argAbilityActivationInteractiveGainRed()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $total = \GB\Actions\AbilityActivation\getInteractiveAbilityTotal($playerId);
                $count = $total + 1 - \GB\Actions\AbilityActivation\getInteractiveAbilityCounter($playerId);
                $gainFrom = clienttranslate('Gain from markets');
                $emptyRedDeck = [];
                if ($componentMgr->atLeastOneRedDeckIsEmpty() && !isGameSolo()) {
                    if (count($componentMgr->getCardInRedMarket()) > 0) {
                        $gainFrom = clienttranslate('Gain from the remaining market or from an opponent');
                    } else {
                        $gainFrom = clienttranslate('Gain from an opponent');
                    }
                    for ($side = 0; $side <= 1; ++$side) {
                        if ($componentMgr->specificRedDeckIsEmpty($side)) {
                            $emptyRedDeck[] = $side;
                        }
                    }
                }
                return [
                    'componentIds' => array_keys($componentMgr->getCardInRedMarket()),
                    'redDeckPlayerIds' => $componentMgr->atLeastOneRedDeckIsEmpty()
                        ? $componentMgr->getPlayerIdsWithCardsInRedDeck($playerId)
                        : [],
                    'emptyRedDecks' => $emptyRedDeck,
                    'gainFrom' => $gainFrom,
                    'interactiveCount' => $total <= 1 ? '' : "($count/$total)",
                    'i18n' => ['gainFrom'],
                ];
            }
        );
    }

    public function abilityActivationInteractiveGainRed(?int $componentId, ?int $redDeckPlayerId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("abilityActivationInteractiveGainRed");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\AbilityActivation\UseInteractiveAbility($playerId));
        $creator->add(new \GB\Actions\Ability\GainRedCardToPlayerDeck($playerId, $componentId, $redDeckPlayerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->commit();
    }

    public function argAbilityActivationInteractiveGainBlue()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $total = \GB\Actions\AbilityActivation\getInteractiveAbilityTotal($playerId);
                $count = $total + 1 - \GB\Actions\AbilityActivation\getInteractiveAbilityCounter($playerId);
                return [
                    'componentIds' => array_keys($componentMgr->getHumanoidCardInBlueMarket()),
                    'interactiveCount' => $total <= 1 ? '' : "($count/$total)",
                ];
            }
        );
    }

    public function abilityActivationInteractiveGainBlue(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("abilityActivationInteractiveGainBlue");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\AbilityActivation\UseInteractiveAbility($playerId));
        $creator->add(new \GB\Actions\Ability\GainBlueCardToPlayerDeck($playerId, $componentId, \GB\COMPONENT_LOCATION_ID_MARKET, true));
        if (isGameSoloLostUnfilledMarket()) {
            $creator->add(new \BX\ActiveState\JumpStateActionCommand($playerId, STATE_SOLO_LOST_UNFILLED_MARKET_ID));
        } else {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        }
        $creator->commit();
    }

    public function argAbilityActivationInteractiveReactivateHumanoid()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentId = \GB\Actions\AbilityActivation\getActivatedComponentId($playerId);
                $gain = \GB\Actions\AbilityActivation\getInteractiveAbilityGain($playerId);
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                return [
                    'componentIds' => array_keys($componentMgr->getCardsInPlayerBluePlayAreaForReactivation($playerId, $gain->conditionIcon, $componentId)),
                    'hasBlueCards' => count($componentMgr->getPlayerBlueDeck($playerId)) > 0,
                ];
            }
        );
    }

    public function abilityActivationInteractiveReactivateHumanoid(int $componentId, $side)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("abilityActivationInteractiveReactivateHumanoid");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $excludeComponentId = \GB\Actions\AbilityActivation\getActivatedComponentId($playerId);
        if ($componentId == $excludeComponentId) {
            throw new \BgaSystemException("BUG! Cannot reactivate the card itself $componentId");
        }

        $mustCommit = $this->componentAbilityInstantGainAbilityRequiresCommit($componentId, $side);
        $creator = \BX\Action\buildActionCommandCreator($playerId, $mustCommit);
        $this->callAbilityActivation($componentId, $creator, $mustCommit, $side);
        $creator->saveOrCommit();
    }

    public function argAbilityActivationInteractiveReactivateBuilding()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                return [
                    'componentIds' => array_keys($componentMgr->getUsedCardsInPlayerBuildingPlayArea($playerId)),
                ];
            }
        );
    }
    public function abilityActivationInteractiveReactivateBuilding(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("abilityActivationInteractiveReactivateBuilding");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\AbilityActivation\UseInteractiveAbility($playerId));
        $creator->add(new \GB\Actions\AbilityActivation\ReactivateBuilding($playerId, $componentId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->save();
    }

    private function addPaymentAbility(\BX\Action\ActionCommandCreatorInterface $creator)
    {
        // Return true when there is no payment, when you can pay or when the payment is not required
        $playerId = $creator->getPlayerId();
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($playerId);
        $ability = \GB\Actions\AbilityActivation\getActivatedAbility($playerId);
        if ($ability->cost === null) {
            return true;
        }
        if ($ability->cost->canPayCost($ps->nuggetCount, $ps->materialCount) || $ability->cost->isMandatory) {
            $creator->add(new \GB\Actions\AbilityActivation\PayAbility(
                $playerId,
                \GB\Actions\AbilityActivation\getActivatedComponentId($playerId),
                \GB\Actions\AbilityActivation\getActivatedAbilityIndex($playerId)
            ));
            return true;
        }
        // The ability can't be payed for but the card can be played anyway
        $creator->add(new \BX\Action\SendMessage(
            $creator->getPlayerId(),
            clienttranslate('${player_name} does not have enough to pay for the ability but the card will be played anyway'),
        ));
        return false;
    }

    private function addInstantGainAbility(\BX\Action\ActionCommandCreatorInterface $creator)
    {
        $playerId = $creator->getPlayerId();
        $componentId = \GB\Actions\AbilityActivation\getActivatedComponentId($playerId);
        $ability = \GB\Actions\AbilityActivation\getActivatedAbility($playerId);
        $ability->foreachInstantGain(function ($gain) use ($creator, $playerId, $componentId) {
            switch ($gain->gainTypeId) {
                case \GB\GAIN_ID_GAIN_NUGGET:
                    $creator->add(new \GB\Actions\AbilityActivation\GainNugget($playerId, $componentId, $gain->count, $gain->conditionIcon));
                    break;
                case \GB\GAIN_ID_GAIN_MATERIAL:
                    $creator->add(new \GB\Actions\AbilityActivation\GainMaterial($playerId, $componentId, $gain->count, $gain->conditionIcon));
                    break;
                case \GB\GAIN_ID_GAIN_GOLD:
                    $creator->add(new \GB\Actions\AbilityActivation\GainGold($playerId, $gain->count, $gain->conditionIcon));
                    break;
                case \GB\GAIN_ID_DRAW_BLUE_CARD:
                    $creator->add(new \GB\Actions\Ability\DrawBlueCardToPlayerHand($playerId, $gain->count));
                    break;
                case \GB\GAIN_ID_ROLL_DICE:
                    $creator->add(new \GB\Actions\AbilityActivation\RollDice($playerId, $componentId, $gain->count, $gain->conditionIcon));
                    break;
                case \GB\GAIN_ID_GAIN_MAGIC:
                    $creator->add(new \GB\Actions\AbilityActivation\DrawMagic($playerId));
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unknown instant gain: {$gain->gainTypeId}");
            }
        });
    }

    private function instantGainAbilityRequiresCommit(int $playerId)
    {
        $ability = \GB\Actions\AbilityActivation\getActivatedAbility($playerId);
        return $ability->instantGainRequiresCommit();
    }

    public function abilityActivationIsCombat(int $playerId)
    {
        $ability = \GB\Actions\AbilityActivation\getActivatedAbility($playerId);
        return $ability->hasStartCombatAbility();
    }

    private function addInstantCombatAbility(\BX\Action\ActionCommandCreatorInterface $creator)
    {
        $playerId = $creator->getPlayerId();
        $creator->add(new \GB\Actions\Combat\StartCombat($playerId));

        $ability = \GB\Actions\AbilityActivation\getActivatedAbility($playerId);
        $ability->foreachInstantCombat(function ($gain) use ($playerId, $creator) {
            switch ($gain->gainTypeId) {
                case \GB\GAIN_ID_GAIN_COMBAT_POWER:
                    $creator->add(new \GB\Actions\Combat\GainPower($playerId, $gain->count));
                    break;
                case \GB\GAIN_ID_DRAW_RED_CARD:
                    $creator->add(new \GB\Actions\Combat\GainDraw($playerId, $gain->count));
                    break;
                default:
                    throw new \BgaSystemException("Unknow combat gain gainTypeId {$gain->gainTypeId}");
            }
        });
    }
}
