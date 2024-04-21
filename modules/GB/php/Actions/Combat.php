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

namespace GB\Actions\Combat;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/Traits.php');

class StartCombat extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->saveUndoCounts();

        $ps->modifyAction();
        $ps->clearCombat();

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} starts a combat'),
            []
        );

        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->notifyUndoCounts($notifier);
    }
}

class GainPower extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $combatPower;

    public function __construct(int $playerId, int $combatPower)
    {
        parent::__construct($playerId);
        $this->combatPower = $combatPower;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->saveUndoCounts();

        $ps->modifyAction();
        $ps->gainCombatPower($this->combatPower);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} gains ${combatPower} strength'),
            [
                'combatPower' => $this->combatPower,
            ]
        );

        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->notifyUndoCounts($notifier);
    }
}

class GainDraw extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $combatDraw;

    public function __construct(int $playerId, int $combatDraw)
    {
        parent::__construct($playerId);
        $this->combatDraw = $combatDraw;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $ps->modifyAction();
        $ps->gainCombatDraw($this->combatDraw);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} will draw ${combatDraw} more card(s) in combat'),
            [
                'combatDraw' => $this->combatDraw,
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class SelectEnemy extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $locationId;

    public function __construct(int $playerId, ?int $componentId, ?int $locationId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->locationId = $locationId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->componentId === null && $this->locationId === null) {
            throw new \BgaSystemException('BUG! Both componentId and locationId are null!');
        }
        if ($this->componentId !== null && $this->locationId !== null) {
            throw new \BgaSystemException('BUG! Both componentId and locationId are not null!');
        }

        $componentMgr = self::getMgr('component');
        $enemy = null;
        if ($this->componentId === null) {
            $locationIds = $componentMgr->getAllAccessibleHiddenEnemyLocations();
            if (array_search($this->locationId, $locationIds) === false) {
                throw new \BgaSystemException("BUG! locationId {$this->locationId} is not valid");
            }
            $enemy = $componentMgr->getTopEnemyFromLocationId($this->locationId);
            $enemy->modifyAction();
            $enemy->flipEnemyToMarket();
            $notifier->notify(
                NTF_FLIP_ENEMY,
                clienttranslate('${player_name} reveals a new enemy: ${componentName} ${componentImage}'),
                [
                    'locationId' => $this->locationId,
                    'component' => $enemy,
                    'componentName' => $enemy->def()->name,
                    'componentImage' => $enemy->typeId,
                    'i18n' => ['componentName'],
                ]
            );
        } else {
            $enemy = $this->getComponent();
            self::enforceComponentEnemy($enemy);
            self::enforceComponentNoPlayerId($enemy);
            self::enforceComponentLocationId($enemy, \GB\COMPONENT_LOCATION_ID_MARKET);
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $ps->modifyAction();
        $ps->gainCombatDraw($componentMgr->getEnemyLocationCombatDraw($enemy->locationPrimaryOrder));
        $ps->combatEnemyComponentId = $enemy->componentId;

        $notifier->notify(
            NTF_SELECT_ENEMY,
            clienttranslate('${player_name} will draw ${combatDraw} card(s) to fight an enemy: ${componentName} ${componentImage}'),
            [
                'selectEnemy' => $enemy->componentId,
                'combatDraw' => $ps->combatDraw,
                'componentName' => $enemy->def()->name,
                'componentImage' => $enemy->typeId,
                'i18n' => ['componentName'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }
}

class DrawRedCombatCard extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $mustDrawNow;

    public function __construct(int $playerId, bool $mustDrawNow)
    {
        parent::__construct($playerId);
        $this->mustDrawNow = $mustDrawNow;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->mustDrawNow === null) {
            $this->mustDrawNow = false;
        }
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        if ($ps->combatDraw <= 0) {
            throw new \BgaSystemException('BUG! Cannot draw red card, no combat draw left');
        }

        $ps->modifyAction();
        $ps->combatDraw();

        $componentMgr = self::getMgr('component');
        $card = $componentMgr->getTopCardFromRedPlayerDeck($this->playerId);
        if ($card === null) {
            throw new \BgaSystemException('BUG! Cannot draw red card when player deck is empty');
        }
        $card->modifyAction();
        $card->moveToPlayerRedPlayArea($this->playerId);

        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${player_name} draws combat card: ${cardName} ${componentImage}'),
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_DECK,
                    'playerId' => $this->playerId,
                ],
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );

        self::notifyUpdateCounts($notifier);

        // Apply card instant combat gains
        if (
            !$card->def()->hasAbilityCost()
            && !$card->def()->hasAbilityChoice()
            && !$card->def()->hasReactivateAbility()
        ) {
            $action = new CombatInstantGain($this->playerId, $card->componentId, 0, $this->mustDrawNow);
            $action->do($notifier);

            // Mark used
            $card->modifyAction();
            $card->isUsed = true;

            $notifier->notifyNoMessage(
                NTF_USE_COMPONENTS,
                [
                    'componentIds' => [$card->componentId],
                    'isUsed' => true,
                ]
            );
        }
    }
}

class WinCombat extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $playerStateMgr = self::getMgr('player_state');
        $enemy = $componentMgr->getById($playerStateMgr->getPlayerCombatEnemyComponentId($this->playerId));
        $enemyLocation = $enemy->locationPrimaryOrder;

        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->statCombatWon += 1;
        if ($enemy->def()->subCategoryId == \GB\COMPONENT_SUB_CATEGORY_ID_PERMANENT) {
            $ps->statCombatWonCow += 1;
        }

        $enemyPower = $enemy->def()->getCombatPower();
        $playerPower = $playerStateMgr->getPlayerCombatPower($this->playerId);
        $notifier->notify(
            NTF_COMBAT_STATUS,
            clienttranslate('${player_name} wins their combat ${playerPower} vs ${enemyPower} and gains a reward!'),
            [
                'win' => true,
                'typeId' => $enemy->typeId,
                'enemyPower' => $enemyPower,
                'playerPower' => $playerPower,
            ]
        );

        if ($enemy->def()->subCategoryId != \GB\COMPONENT_SUB_CATEGORY_ID_PERMANENT) {
            $enemy->modifyAction();
            $enemy->moveEnemyToPlayerBoard($this->playerId);
            $notifier->notify(
                NTF_UPDATE_COMPONENTS,
                clienttranslate('${player_name} gains an enemy tile: ${componentName} ${componentImage}'),
                [
                    'components' => [$enemy],
                    'componentName' => $enemy->def()->name,
                    'componentImage' => $enemy->typeId,
                    'i18n' => ['componentName'],
                ]
            );
        }

        if ($enemyLocation == \GB\EnemyMapDefMgr::getPermanent()->id && $enemy->def()->subCategoryId != \GB\COMPONENT_SUB_CATEGORY_ID_PERMANENT) {
            $cowEnemy = $componentMgr->getTopEnemyFromLocationId($enemyLocation);
            $cowEnemy->modifyAction();
            $cowEnemy->flipEnemyToMarket();
            $notifier->notify(
                NTF_FLIP_ENEMY,
                clienttranslate('${player_name} reveals the Cow-Dragon!'),
                [
                    'locationId' => $enemyLocation,
                    'component' => $cowEnemy,
                ]
            );
        }

        $notifier->notifyNoMessage(
            NTF_SELECT_ENEMY,
            [
                'selectEnemy' => null,
            ]
        );
    }
}

class LoseCombat extends \BX\Action\BaseActionCommandNoUndo
{
    private $mustDestroyCard;

    public function __construct(int $playerId, bool $mustDestroyCard)
    {
        parent::__construct($playerId);
        $this->mustDestroyCard = $mustDestroyCard;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $playerStateMgr = self::getMgr('player_state');
        $enemy = $componentMgr->getById($playerStateMgr->getPlayerCombatEnemyComponentId($this->playerId));
        $enemyPower = $enemy->def()->getCombatPower();
        $playerPower = $playerStateMgr->getPlayerCombatPower($this->playerId);

        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->statCombatLost += 1;

        $notifier->notify(
            NTF_COMBAT_STATUS,
            $this->mustDestroyCard
                ? clienttranslate('${player_name} loses their combat ${playerPower} vs ${enemyPower} and must destroy one of their combat card')
                : clienttranslate('${player_name} loses their combat ${playerPower} vs ${enemyPower} and has no combat card to destroy'),
            [
                'win' => false,
                'typeId' => $enemy->typeId,
                'enemyPower' => $enemyPower,
                'playerPower' => $playerPower,
            ]
        );

        $notifier->notifyNoMessage(
            NTF_SELECT_ENEMY,
            [
                'selectEnemy' => null,
            ]
        );
    }
}


class EndCombat extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $isZombie;
    private $undoCards;

    public function __construct(int $playerId, bool $isZombie = false)
    {
        parent::__construct($playerId);
        $this->isZombie = $isZombie;
        $this->undoCards = [];
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->saveUndoCounts();

        $ps->modifyAction();
        $ps->clearCombat();

        $componentMgr = self::getMgr('component');
        $cards = $componentMgr->playerPlayedCombatCard($this->playerId);
        foreach ($cards as $c) {
            $this->undoCards[$c->componentId] = \BX\Meta\deepClone($c);
            $c->modifyAction();
            $c->moveToPlayerRedSecondaryPlayArea($this->playerId);
        }

        $notifier->notifyNoMessage(
            NTF_USE_COMPONENTS,
            [
                'componentIds' => array_keys($cards),
                'isUsed' => true,
            ]
        );

        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            $this->isZombie
                ? ''
                : clienttranslate('${player_name} combat is over'),
            [
                'components' => array_values($cards),
                'fast' => true,
            ]
        );

        $notifier->notifyNoMessage(
            NTF_SELECT_ENEMY,
            [
                'selectEnemy' => null,
            ]
        );

        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'components' => array_values($this->undoCards),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_USE_COMPONENTS,
            [
                'componentIds' => array_keys(array_filter($this->undoCards, fn ($c) => !$c->isUsed)),
                'isUsed' => false,
            ]
        );
        $this->notifyUndoCounts($notifier);
    }
}

class DestroyRedCard extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $undoCard;

    public function __construct(int $playerId, int $componentId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->getComponent();
        self::enforceComponentCardRed($card);
        self::enforceComponentPlayerId($card, $this->playerId);
        self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA);

        $this->undoCard = \BX\Meta\deepClone($card);

        $card->modifyAction();
        $card->moveToDiscard();
        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${player_name} destroys ${cardName} ${componentImage}'),
            [
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA,
                    'playerId' => $this->playerId,
                    'locationPrimaryOrder' => 1,
                ],
                'components' => [$this->undoCard],
            ]
        );
        if ($this->undoCard->isUsed) {
            $notifier->notifyNoMessage(
                NTF_USE_COMPONENTS,
                [
                    'componentIds' => [$this->componentId],
                    'isUsed' => true,
                ]
            );
        }
    }
}

class ActivateInteractiveCombatCard extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $mustBeUsed;
    private $isReactivate;

    public function __construct(int $playerId, int $componentId, bool $mustBeUsed = false, bool $isReactivate = false)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->mustBeUsed = $mustBeUsed;
        $this->isReactivate = $isReactivate;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->getComponent();
        self::enforceComponentCardRed($card);
        self::enforceComponentPlayerId($card, $this->playerId);
        self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA);

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        if ($ps->combatInteractiveReactivateComponentId === null) {
            if (!$this->mustBeUsed && $card->isUsed) {
                throw new \BgaSystemException("BUG! componentId {$this->componentId} is already used");
            }
            if ($this->mustBeUsed && !$card->isUsed) {
                throw new \BgaSystemException("BUG! componentId {$this->componentId} is not already used");
            }
        } else {
            $this->mustBeUsed = false;
        }

        $ps->modifyAction();
        $ps->combatInteractiveComponentId = $this->componentId;
        if ($this->isReactivate) {
            $ps->combatInteractiveReactivateComponentId = $this->componentId;
        }

        if ($this->isReactivate || $ps->combatInteractiveReactivateComponentId === null) {
            $card->modifyAction();
            $card->isUsed = true;
            $notifier->notifyNoMessage(
                NTF_USE_COMPONENTS,
                [
                    'componentIds' => [$this->componentId],
                    'isUsed' => true,
                ]
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->mustBeUsed) {
            $notifier->notifyNoMessage(
                NTF_USE_COMPONENTS,
                [
                    'componentIds' => [$this->componentId],
                    'isUsed' => false,
                ]
            );
        }
    }
}

class CombatInstantGain extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $abilityIndex;
    private $mustDrawNow;
    private $didDraw;
    private $undoActions;

    public function __construct(int $playerId, int $componentId, int $abilityIndex, bool $mustDrawNow)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->abilityIndex = $abilityIndex;
        $this->mustDrawNow = $mustDrawNow;
        $this->didDraw = false;
        $this->undoActions = [];
        if ($this->abilityIndex != 0 && $this->abilityIndex != 1) {
            throw new \BgaSystemException("BUG! abilityIndex is {$this->abilityIndex} but must be 0 or 1");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->getComponent();
        self::enforceComponentCardRed($card);
        self::enforceComponentPlayerId($card, $this->playerId);
        self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA);
        if (!$card->def()->isValidAbilityChoice($this->abilityIndex)) {
            throw new \BgaSystemException("BUG! abilityIndex {$this->abilityIndex} is not valid for componentId {$this->componentId}");
        }

        $this->saveUndoCounts();

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $componentMgr = self::getMgr('component');

        $card->def()->abilities[$this->abilityIndex]->foreachGain(function ($gain) use ($card, $notifier, $ps, $playerStateMgr, $componentMgr) {
            switch ($gain->gainTypeId) {
                case \GB\GAIN_ID_GAIN_COMBAT_POWER:
                    $gainPower = $gain->count;
                    if ($gain->conditionIcon !== null) {
                        $gainPower = ($gainPower * $componentMgr->countPlayerIcon($this->playerId, $gain->conditionIcon));
                    }
                    $ps->modifyAction();
                    $ps->gainCombatPower($gainPower);
                    $notifier->notify(
                        NTF_ATTACK_ENEMY,
                        clienttranslate('${player_name} gains ${combatPower} strength'),
                        [
                            'combatPower' => $gainPower,
                            'fromComponentId' => $card->componentId,
                            'toComponentId' => $ps->combatEnemyComponentId,
                        ]
                    );
                    self::notifyUpdateCounts($notifier);
                    break;
                case \GB\GAIN_ID_DRAW_RED_CARD:
                    $ps->modifyAction();
                    $ps->gainCombatDraw($gain->count);
                    $notifier->notify(
                        \BX\Action\NTF_MESSAGE,
                        clienttranslate('${player_name} will draw ${combatDraw} more card(s) in this fight'),
                        [
                            'combatDraw' => $gain->count,
                        ]
                    );
                    if (
                        $this->mustDrawNow
                        && $playerStateMgr->getPlayerCombatDraw($this->playerId) > 0
                        && $componentMgr->getTopCardFromRedPlayerDeck($this->playerId) !== null
                    ) {
                        $this->didDraw = true;
                        $action = new DrawRedCombatCard($this->playerId, $this->mustDrawNow);
                        $action->do($notifier);
                    }
                    break;
                case \GB\GAIN_ID_GAIN_NUGGET:
                    $action = new \GB\Actions\AbilityActivation\GainNugget($this->playerId, $card->componentId, $gain->count, $gain->conditionIcon);
                    $action->do($notifier);
                    $this->undoActions[] = $action;
                    break;
                case \GB\GAIN_ID_GAIN_MATERIAL:
                    $action = new \GB\Actions\AbilityActivation\GainMaterial($this->playerId, $card->componentId, $gain->count, $gain->conditionIcon);
                    $action->do($notifier);
                    $this->undoActions[] = $action;
                    break;
                case \GB\GAIN_ID_DESTROY_SELF:
                    $action = new DestroyRedCard(
                        $this->playerId,
                        $ps->combatInteractiveReactivateComponentId === null
                            ? $card->componentId
                            : $ps->combatInteractiveReactivateComponentId
                    );
                    $action->do($notifier);
                    $this->undoActions[] = $action;
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unknown gainTypeId {$gain->gainTypeId}");
            }
        });
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->didDraw) {
            throw new \BgaUserException($notifier->_('This action cannot be undone since you have drawn a card'));
        }
        $this->notifyUndoCounts($notifier);
        foreach ($this->undoActions as $action) {
            $action->undo($notifier);
        }
    }
}

class ClearActiveInteractiveCombatCard extends \BX\Action\BaseActionCommand
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->combatInteractiveComponentId = null;
        $ps->combatInteractiveReactivateComponentId  = null;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}
