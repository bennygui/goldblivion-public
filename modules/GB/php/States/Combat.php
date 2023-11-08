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

namespace GB\State\Combat;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/Combat.php');

trait GameStatesTrait
{
    public function argCombatSelectEnemy()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $components = $componentMgr->getAllEnemiesInMarket();
                $locationIds = $componentMgr->getAllAccessibleHiddenEnemyLocations();
                return [
                    'componentIds' => array_keys($components),
                    'locationIds' => $locationIds,
                    'playerCombatDraw' => $playerStateMgr->getPlayerCombatDraw($playerId),
                    'componentIdCombatDraw' => array_combine(
                        array_keys($components),
                        array_map(fn ($c) => $componentMgr->getEnemyLocationCombatDraw($c->locationPrimaryOrder), $components)
                    ),
                    'locationIdCombatDraw' => array_combine(
                        $locationIds,
                        array_map(fn ($id) => $componentMgr->getEnemyLocationCombatDraw($id), $locationIds)
                    ),
                    'hasCombatCard' => (count($componentMgr->getPlayerRedDeck($playerId)) > 0),
                ];
            }
        );
    }

    public function combatSelectEnemy(?int $componentId, ?int $locationId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("combatSelectEnemy");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Combat\SelectEnemy($playerId, $componentId, $locationId));

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        while (
            $playerStateMgr->getPlayerCombatDraw($playerId) > 0
            && $componentMgr->getTopCardFromRedPlayerDeck($playerId) !== null
        ) {
            $creator->add(new \GB\Actions\Combat\DrawRedCombatCard($playerId));
        }

        if (count($componentMgr->playerUnusedCombatCard($playerId)) > 0) {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'interactive'));
        } else {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'endCombat'));
        }
        $creator->commit();
    }

    public function stCombatWinOrLose()
    {
        $playerId = $this->getActivePlayerId();
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $enemy = $componentMgr->getById($playerStateMgr->getPlayerCombatEnemyComponentId($playerId));

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        if ($playerStateMgr->getPlayerCombatPower($playerId) >= $enemy->def()->getCombatPower()) {
            // Win
            $creator->add(new \GB\Actions\Combat\WinCombat($playerId));
            $creator->add(new \GB\Actions\Combat\EndCombat($playerId));
            $this->callAbilityActivation($enemy->componentId, $creator, true);
        } else {
            // Lose
            if (count($componentMgr->playerPlayedCombatCard($playerId)) > 0) {
                $creator->add(new \GB\Actions\Combat\LoseCombat($playerId, true));
                $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'loseDestroyCard'));
            } else {
                $creator->add(new \GB\Actions\Combat\LoseCombat($playerId, false));
                $creator->add(new \GB\Actions\Combat\EndCombat($playerId));
                $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'activationExit'));
            }
        }
        $this->giveExtraTime($playerId);
        $creator->commit();
    }

    public function argCombatLoseDestroyRedCard()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                return [
                    'componentIds' => array_keys($componentMgr->playerPlayedCombatCard($playerId)),
                ];
            }
        );
    }

    public function combatLoseDestroyRedCard(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("combatLoseDestroyRedCard");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\Combat\DestroyRedCard($playerId, $componentId));
        $creator->add(new \GB\Actions\Combat\EndCombat($playerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->save();
    }

    public function argCombatInteractive()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                $componentIds = array_keys($componentMgr->playerUnusedCombatCard($playerId));
                return [
                    'actions' => count($componentIds) == 0
                        ? clienttranslate('End the fight')
                        : clienttranslate('Activate a combat card or End the combat'),
                    'componentIds' => $componentIds,
                    'i18n' => ['actions'],
                ];
            }
        );
    }

    public function combatInteractive(int $componentId, int $side)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("combatInteractive");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $validCards = $componentMgr->playerUnusedCombatCard($playerId);
        if (!array_key_exists($componentId, $validCards)) {
            throw new \BgaSystemException("BUG! componentId $componentId is not a valid combat interactive card for playerId $playerId");
        }

        $card = $validCards[$componentId];
        if ($card->def()->hasAbilityChoice()) {
            if ($side != 0 && $side != 1) {
                throw new \BgaSystemException("BUG! side is $side but must be 0 or 1");
            }

            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \GB\Actions\Combat\ActivateInteractiveCombatCard($playerId, $componentId, false));
            $creator->add(new \GB\Actions\Combat\CombatInstantGain($playerId, $componentId, $side, true));
            $creator->add(new \GB\Actions\Combat\ClearActiveInteractiveCombatCard($playerId));
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'interactive'));
            $creator->save();
            return;
        }

        if ($card->def()->hasReactivateAbility()) {
            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \GB\Actions\Combat\ActivateInteractiveCombatCard($playerId, $componentId, false, true));
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'reactivate'));
            $creator->save();
            return;
        }

        // The only remaining option is the 'Pay Material for 3 combat points'
        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \GB\Actions\Combat\ActivateInteractiveCombatCard($playerId, $componentId));
        $creator->add(new \GB\Actions\AbilityActivation\PayAbility($playerId, $componentId, 0));
        $creator->add(new \GB\Actions\Combat\CombatInstantGain($playerId, $componentId, 0, true));
        $creator->add(new \GB\Actions\Combat\ClearActiveInteractiveCombatCard($playerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'interactive'));
        $creator->save();
    }

    public function combatInteractiveEndCombat()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("combatInteractiveEndCombat");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        foreach ($componentMgr->playerUnusedCombatCard($playerId) as $card) {
            if ($card->def()->hasAbilityChoice()) {
                throw new \BgaUserException(self::_(clienttranslate('You must choose a side to activate for your combat cards')));
            }
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'endCombat'));
        $creator->save();
    }

    public function argCombatInteractiveReactivateRedCard()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
                return [
                    'componentIds' => array_keys($componentMgr->getPlayerCombatCardToReactivate($playerId)),
                ];
            }
        );
    }

    public function combatInteractiveReactivateRedCard(int $componentId, int $side)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("combatInteractiveReactivateRedCard");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $validCards = $componentMgr->getPlayerCombatCardToReactivate($playerId);
        if (!array_key_exists($componentId, $validCards)) {
            throw new \BgaSystemException("BUG! componentId $componentId is not a valid combat card to copy for playerId $playerId");
        }

        $card = $validCards[$componentId];
        if ($card->def()->hasAbilityChoice()) {
            if ($side != 0 && $side != 1) {
                throw new \BgaSystemException("BUG! side is $side but must be 0 or 1");
            }

            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \GB\Actions\Combat\ActivateInteractiveCombatCard($playerId, $componentId, false, false));
            $creator->add(new \GB\Actions\Combat\CombatInstantGain($playerId, $componentId, $side, true));
            $creator->add(new \GB\Actions\Combat\ClearActiveInteractiveCombatCard($playerId));
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'interactive'));
            $creator->save();
            return;
        }

        if ($card->def()->hasReactivateAbility()) {
            throw new \BgaSystemException("BUG! componentId $componentId to copy cannot have a reactivate ability for $playerId");
        }

        $mustCommit = ($card->def()->hasDrawRedCardAbility()
            && $playerStateMgr->getPlayerCombatDraw($playerId) > 0
            && $componentMgr->getTopCardFromRedPlayerDeck($playerId) !== null);
        $creator = \BX\Action\buildActionCommandCreator($playerId, $mustCommit);
        $creator->add(new \GB\Actions\AbilityActivation\PayAbility($playerId, $componentId, 0));
        $creator->add(new \GB\Actions\Combat\CombatInstantGain($playerId, $componentId, 0, true));
        $creator->add(new \GB\Actions\Combat\ClearActiveInteractiveCombatCard($playerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'interactive'));
        $creator->saveOrCommit();
    }
}
