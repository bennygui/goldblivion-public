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

namespace GB\Actions\AbilityActivation;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../../../BX/php/StateFunction.php');
require_once(__DIR__ . '/Traits.php');

function getActivatedComponentId(int $playerId)
{
    $fct = \BX\StateFunction\getLatestFunctionCall($playerId, \GB\Actions\AbilityActivation\StateFunction::class);
    return $fct->getComponentId();
}

function getActivatedMustCommit(int $playerId)
{
    $fct = \BX\StateFunction\getLatestFunctionCall($playerId, \GB\Actions\AbilityActivation\StateFunction::class);
    return $fct->mustCommit();
}

function getActivatedComponent(int $playerId)
{
    $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
    return $componentMgr->getById(getActivatedComponentId($playerId));
}

function getActivatedAbilityIndex(int $playerId)
{
    $fct = \BX\StateFunction\getLatestFunctionCall($playerId, \GB\Actions\AbilityActivation\StateFunction::class);
    return $fct->getAbilityIndex();
}

function getActivatedAbility(int $playerId)
{
    $c = getActivatedComponent($playerId);
    return $c->def()->abilities[getActivatedAbilityIndex($playerId)];
}

function getInteractiveAbilityCounter(int $playerId)
{
    $fct = \BX\StateFunction\getLatestFunctionCall($playerId, \GB\Actions\AbilityActivation\StateFunction::class);
    return $fct->getInteractiveAbilityCounter();
}

function getInteractiveAbilityTotal(int $playerId)
{
    $fct = \BX\StateFunction\getLatestFunctionCall($playerId, \GB\Actions\AbilityActivation\StateFunction::class);
    return $fct->getInteractiveAbilityTotal();
}

function getInteractiveAbilityGain(int $playerId)
{
    $ability = getActivatedAbility($playerId);
    // All components have only 1 interactive ability or 2 of the same ability
    $gain = null;
    $ability->foreachInteractiveGain(function ($g) use (&$gain) {
        $gain = $g;
    });
    return $gain;
}


class StateFunction extends \BX\StateFunction\StateFunctionBase
{
    private $componentId;
    private $mustCommit;
    private $abilityIndex;
    private $interactiveAbilityCounter;
    private $interactiveAbilityTotal;

    public function __construct(int $componentId, bool $mustCommit, ?int $side)
    {
        parent::__construct(STATE_ABILITY_ACTIVATION_ENTER_ID, STATE_ABILITY_ACTIVATION_EXIT_ID);
        $this->componentId = $componentId;
        $this->mustCommit = $mustCommit;
        $this->abilityIndex = $side;
        $this->interactiveAbilityCounter = 0;
        $this->interactiveAbilityTotal = 0;
    }

    public function getComponentId()
    {
        return $this->componentId;
    }

    public function mustCommit()
    {
        return $this->mustCommit;
    }

    public function getAbilityIndex()
    {
        return $this->abilityIndex;
    }

    public function setAbilityIndex(int $index)
    {
        $this->abilityIndex = $index;
    }

    public function getInteractiveAbilityCounter()
    {
        return $this->interactiveAbilityCounter;
    }

    public function setInteractiveAbilityCounter(int $counter)
    {
        $this->interactiveAbilityCounter = $counter;
    }

    public function getInteractiveAbilityTotal()
    {
        return $this->interactiveAbilityTotal;
    }

    public function setInteractiveAbilityTotal(int $total)
    {
        $this->interactiveAbilityTotal = $total;
    }
}

class ChooseActivationSide extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;

    private $side;
    private $isAutomatic;

    public function __construct(int $playerId, int $side, bool $isAutomatic = false)
    {
        parent::__construct($playerId);
        $this->side = $side;
        $this->isAutomatic = $isAutomatic;
        if ($this->side != 0 && $this->side != 1) {
            throw new \BgaSystemException("BUG! Invalide side value: {$this->side}");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        \BX\StateFunction\updateLatestFunctionCallAction(
            $this->playerId,
            \GB\Actions\AbilityActivation\StateFunction::class,
            function ($fct) use ($notifier) {
                $c = self::getComponentById($fct->getComponentId());
                if (!$c->def()->isValidAbilityChoice($this->side)) {
                    throw new \BgaSystemException("BUG! Component does not support side value: {$this->side}");
                }
                $fct->setAbilityIndex($this->side);
                $ability = $c->def()->abilities[$this->side];
                // Check for a reactivation loop: remove first function since it's the current one
                $allFunctions = \BX\StateFunction\getAllFunctionCall($this->playerId, \GB\Actions\AbilityActivation\StateFunction::class);
                array_shift($allFunctions);
                foreach ($allFunctions as $f) {
                    if ($f->getComponentId() == $c->componentId) {
                        throw new \BgaUserException($notifier->_('You cannot reactivate a card that was already reactivated'));
                    }
                }
                // All components have only 1 interactive ability or 2 of the same ability
                $counter = 0;
                $ability->foreachInteractiveGain(function ($gain) use (&$counter) {
                    $counter = $gain->count;
                });
                $fct->setInteractiveAbilityCounter($counter);
                $fct->setInteractiveAbilityTotal($counter);
            }
        );
        $c = getActivatedComponent($this->playerId);
        if (!$this->isAutomatic && $c->def()->hasAbilityChoice()) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                $this->side == 0
                    ? clienttranslate('${player_name} activates the left side')
                    : clienttranslate('${player_name} activates the right side'),
                []
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }

    public function mustAlwaysUndoAction()
    {
        return true;
    }
}

class UseInteractiveAbility extends \BX\Action\BaseActionCommand
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        \BX\StateFunction\updateLatestFunctionCallAction(
            $this->playerId,
            \GB\Actions\AbilityActivation\StateFunction::class,
            function ($fct) {
                $counter = $fct->getInteractiveAbilityCounter();
                --$counter;
                if ($counter < 0) {
                    throw new \BgaSystemException('BUG! Interactive ability counter is negative');
                }
                $fct->setInteractiveAbilityCounter($counter);
            }
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class PayAbility extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;

    private $componentId;
    private $abilityIndex;
    private $undoNugget;
    private $undoMaterial;

    public function __construct(int $playerId, int $componentId, int $abilityIndex)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->abilityIndex = $abilityIndex;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $component = $this->getComponent();
        $ability = $component->def()->abilities[$this->abilityIndex];

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->undoNugget = $ps->nuggetCount;
        $this->undoMaterial = $ps->materialCount;

        $ps->modifyAction();
        $nuggetPay = 0;
        $materialPay = 0;
        if (!$ability->payCost($ps->nuggetCount, $ps->materialCount, $nuggetPay, $materialPay)) {
            throw new \BgaUserException($notifier->_('You do not have enough to pay'));
        }
        if ($nuggetPay != 0) {
            $notifier->notify(
                NTF_UPDATE_NUGGET,
                clienttranslate('${player_name} pays ${nuggetPay} ${nuggetImage}'),
                [
                    'from' => [
                        'componentId' => $this->componentId,
                    ],
                    'nuggetPay' => $nuggetPay,
                    'nuggetCount' => $ps->nuggetCount,
                    'nuggetImage' => clienttranslate('nugget(s)'),
                    'i18n' => ['nuggetImage'],
                ]
            );
        }
        if ($materialPay != 0) {
            $notifier->notify(
                NTF_UPDATE_MATERIAL,
                clienttranslate('${player_name} pays ${materialPay} ${materialImage}'),
                [
                    'from' => [
                        'componentId' => $this->componentId,
                    ],
                    'materialPay' => $materialPay,
                    'materialCount' => $ps->materialCount,
                    'materialImage' => clienttranslate('material(s)'),
                    'i18n' => ['materialImage'],
                ]
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_NUGGET,
            [
                'from' => [
                    'componentId' => $this->componentId,
                ],
                'nuggetCount' => $this->undoNugget,
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_MATERIAL,
            [
                'from' => [
                    'componentId' => $this->componentId,
                ],
                'materialCount' => $this->undoMaterial,
            ]
        );
    }
}

abstract class BaseGainActionCommand extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\BaseGainTrait;

    public function __construct(int $playerId, int $count, ?int $conditionIcon)
    {
        parent::__construct($playerId);
        $this->count = $count;
        $this->conditionIcon = $conditionIcon;
    }
}

abstract class BaseGainActionCommandNoUndo extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\BaseGainTrait;

    public function __construct(int $playerId, int $count, ?int $conditionIcon)
    {
        parent::__construct($playerId);
        $this->count = $count;
        $this->conditionIcon = $conditionIcon;
    }
}

class GainGold extends BaseGainActionCommand
{
    private $undoIsEndGameTriggered;
    private $scoreAction;

    public function __construct(int $playerId, int $count, ?int $conditionIcon)
    {
        parent::__construct($playerId, $count, $conditionIcon);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $this->undoIsEndGameTriggered = $gameStateMgr->isEndGameTriggered();

        $this->scoreAction = new \BX\Player\UpdatePlayerScoreActionCommand($this->playerId);
        $this->scoreAction->do(
            $notifier,
            $this->getGain(),
            clienttranslate('${player_name} gains ${scorePositive} ${goldImage}'),
            [
                'goldImage' => clienttranslate('gold'),
                'nuggetImage' => clienttranslate('nugget(s)'),
                'i18n' => ['goldImage'],
            ]
        );

        if (!$this->undoIsEndGameTriggered && $gameStateMgr->isEndGameTriggered()) {
            $notifier->notify(
                NTF_LAST_ROUND,
                clienttranslate('${player_name} reached 10 ${goldImage}, this will be the last round'),
                [
                    'isLastRound' => true,
                    'goldImage' => clienttranslate('gold'),
                    'i18n' => ['goldImage'],
                ]
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->scoreAction !== null) {
            $this->scoreAction->undo($notifier);
        }
        if (!$this->undoIsEndGameTriggered) {
            $notifier->notifyNoMessage(
                NTF_LAST_ROUND,
                [
                    'isLastRound' => false,
                ]
            );
        }
    }
}

class GainNugget extends BaseGainActionCommand
{
    private $componentId;
    private $undoNugget;

    public function __construct(int $playerId, int $componentId, int $count, ?int $conditionIcon)
    {
        parent::__construct($playerId, $count, $conditionIcon);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->undoNugget = $ps->nuggetCount;

        $gain = $this->getGain();
        $ps->modifyAction();
        $ps->gainNugget($gain);

        $notifier->notify(
            NTF_UPDATE_NUGGET,
            clienttranslate('${player_name} gains ${gain} ${nuggetImage}'),
            [
                'from' => [
                    'componentId' => $this->componentId,
                ],
                'nuggetCount' => $ps->nuggetCount,
                'gain' => $gain,
                'nuggetImage' => clienttranslate('nugget(s)'),
                'i18n' => ['nuggetImage'],
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_NUGGET,
            [
                'from' => [
                    'componentId' => $this->componentId,
                ],
                'nuggetCount' => $this->undoNugget,
            ]
        );
    }
}

class GainMaterial extends BaseGainActionCommand
{
    private $componentId;
    private $undoMaterial;

    public function __construct(int $playerId, int $componentId, int $count, ?int $conditionIcon)
    {
        parent::__construct($playerId, $count, $conditionIcon);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->undoMaterial = $ps->materialCount;

        $gain = $this->getGain();
        $ps->modifyAction();
        $materialMaxed = $ps->gainMaterial($gain);

        $notifier->notify(
            NTF_UPDATE_MATERIAL,
            clienttranslate('${player_name} gains ${gain} ${materialImage}'),
            [
                'from' => [
                    'componentId' => $this->componentId,
                ],
                'materialCount' => $ps->materialCount,
                'gain' => $gain,
                'materialImage' => clienttranslate('material(s)'),
                'i18n' => ['materialimage'],
            ]
        );
        if ($materialMaxed) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} has too much material and can only keep 4'),
                []
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_MATERIAL,
            [
                'from' => [
                    'componentId' => $this->componentId,
                ],
                'materialCount' => $this->undoMaterial,
            ]
        );
    }
}

class DrawMagic extends \BX\Action\BaseActionCommandNoUndo
{
    private const MAX_MAGIC = 3;

    use \GB\Actions\Traits\ComponentNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        if (count($componentMgr->getPlayerMagic($this->playerId)) >= self::MAX_MAGIC) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} gains no magic token since they already have 3 tokens'),
                []
            );
            return;
        }

        $magic = $componentMgr->getTopMagicFromSupply();
        if ($magic === null) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} gains no magic token since there are none left in the supply'),
                []
            );
            return;
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->statGainedMagicToken += 1;

        $magic->modifyAction();
        $magic->moveMagicToPlayerBoard($this->playerId);
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} gains a magic token'),
            []
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_SUPPLY,
                ],
                'components' => [$magic],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }
}

class RollDice extends BaseGainActionCommandNoUndo
{
    private $componentId;
    private $diceRolls;

    public function __construct(int $playerId, int $componentId, int $count, ?int $conditionIcon)
    {
        parent::__construct($playerId, $count, $conditionIcon);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $nbDice = $this->getGain();
        if ($this->diceRolls === null) {
            $this->diceRolls = [];
            for ($i = 0; $i < $nbDice; ++$i) {
                $this->diceRolls[$i] = \bga_rand(0, 5);
            }
        }
        for ($i = 0; $i < $nbDice; ++$i) {
            $diceFace = $this->diceRolls[$i];
            $notifier->notify(
                NTF_ROLL_DICE,
                clienttranslate('${player_name} rolls a dice and gets: ${diceImage}'),
                [
                    'from' => [
                        'componentId' => $this->componentId,
                    ],
                    'diceId' => $i,
                    'diceFace' => $diceFace,
                    'diceImage' => $diceFace,
                ]
            );
            $gain = null;
            switch ($diceFace) {
                default:
                case 0:
                case 1:
                    $gain = new GainNugget($this->playerId, $this->componentId, 1, null);
                    break;
                case 2:
                case 3:
                    $gain = new GainNugget($this->playerId,  $this->componentId, 2, null);
                    break;
                case 4:
                    $gain = new DrawMagic($this->playerId);
                    break;
                case 5:
                    $gain = new GainMaterial($this->playerId,  $this->componentId, 1, null);
                    break;
            }
            $gain->do($notifier);
        }
        for ($i = 0; $i < $nbDice; ++$i) {
            $notifier->notifyNoMessage(
                NTF_DESTROY_DICE,
                [
                    'diceId' => $i,
                ]
            );
        }
    }
}

class ReactivateBuilding extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;

    private $componentId;

    public function __construct(int $playerId, int $componentId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $building = $this->getComponent();
        if (!$building->def()->isBuilding()) {
            throw new \BgaSystemException("BUG! componentId {$building->componentId} is not a building");
        }
        self::enforceComponentLocationId($building, \GB\COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING);
        self::enforceComponentPlayerId($building, $this->playerId);

        if (!$building->isUsed) {
            throw new \BgaSystemException("BUG! building {$building->componentId} is not used");
        }

        $building->modifyAction();
        $building->isUsed = false;

        $notifier->notify(
            NTF_USE_COMPONENTS,
            clienttranslate('${player_name} reactivates building ${cardName} ${componentImage}'),
            [
                'componentIds' => [$this->componentId],
                'isUsed' => false,
                'cardName' => $building->def()->name,
                'componentImage' => $building->typeId,
                'i18n' => ['cardName'],
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_USE_COMPONENTS,
            [
                'componentIds' => [$this->componentId],
                'isUsed' => true,
            ]
        );
    }
}
