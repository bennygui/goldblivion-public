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

namespace GB\Actions\Solo;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/Traits.php');
require_once(__DIR__ . '/../SoloBoardDefMgr.php');

class RollMarketDice extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    private $diceFace;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->diceFace === null) {
            $this->diceFace = \bga_rand(0, 5);
        }
        $order = 0;
        switch ($this->diceFace) {
            default:
            case 0:
            case 1:
                $order = 3;
                break;
            case 2:
            case 3:
                $order = 2;
                break;
            case 4:
                $order = 0;
                break;
            case 5:
                $order = 1;
                break;
        }
        $componentMgr = self::getMgr('component');
        $card = array_values(array_filter($componentMgr->getCardInBlueMarket(), fn ($c) => $c->locationPrimaryOrder == $order))[0];

        $gameStateMgr = self::getMgr('game_state');
        $gameStateMgr->setSoloMarketComponentIdAction($card->componentId);

        $notifier->notify(
            NTF_ROLL_DICE,
            clienttranslate('${soloName} rolls a dice and gets ${diceImage} which selects ${cardName} ${componentImage}'),
            [
                'soloName' => self::soloName(),
                'from' => [
                    'componentId' => $card->componentId,
                ],
                'diceId' => 0,
                'diceFace' => $this->diceFace,
                'diceImage' => $this->diceFace,
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['soloName', 'cardName'],
            ]
        );
        $notifier->notifyNoMessage(
            NTF_DESTROY_DICE,
            [
                'diceId' => 0,
            ]
        );
    }
}

class MoveMarketCardToIcon extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $icon;
    private $isAutomatic;

    public function __construct(int $playerId, int $icon, bool $isAutomatic = true)
    {
        parent::__construct($playerId);
        $this->icon = $icon;
        $this->isAutomatic = $isAutomatic;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $gameStateMgr = self::getMgr('game_state');

        $componentId = $gameStateMgr->getSoloMarketComponentId();
        $card = $componentMgr->getById($componentId);
        if (!$card->def()->hasIcon($this->icon)) {
            throw new \BgaSystemException("ComponentId $componentId does not have icon {$this->icon}");
        }

        if (!$this->isAutomatic) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} chooses ${iconName} position on the Solo Noble board'),
                [
                    'iconName' => \GB\ComponentDefMgr::getIconName($this->icon),
                    'i18n' => ['iconName'],
                ]
            );
        }

        $previousLocationPrimaryOrder = $card->locationPrimaryOrder;

        $card->modifyAction();
        $card->moveToSoloIcon($this->icon);

        $nobleId = gameSoloNoble();
        $board = \GB\SoloBoardDefMgr::getById($nobleId);
        $actionList = $board->iconAbilities[$this->icon];
        if (
            $board->iconAbilityDoubles
            && count($componentMgr->getCardsInSoloBoardIcon($this->icon)) >= SOLO_BOARD_NB_CARD_DOUBLE
        ) {
            $actionList = array_merge($actionList, $actionList);
        }
        $gameStateMgr->setSoloActionListAction($actionList);

        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${cardName} is moved to ${iconName} position on the Solo Noble board ${componentImage}'),
            [
                'components' => [$card],
                'cardName' => $card->def()->name,
                'iconName' => \GB\ComponentDefMgr::getIconName($this->icon),
                'componentImage' => $card->typeId,
                'i18n' => ['cardName', 'iconName'],
            ]
        );

        $notifier->notifyNoMessage(
            NTF_UPDATE_SOLO_ACTION_LIST,
            [
                'soloActionList' => $gameStateMgr->getSoloActionList(),
            ]
        );

        self::drawToReplaceCard($card, $previousLocationPrimaryOrder, $notifier);

        self::notifyUpdateCounts($notifier);
    }
}

class RemoveFirstSoloAction extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;

    private $firstSoloAction;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $actionList = $gameStateMgr->getSoloActionList();
        $this->firstSoloAction = array_shift($actionList);
        $gameStateMgr->setSoloActionListAction($actionList);

        $soloActionDescription = null;
        switch ($this->firstSoloAction) {
            case \GB\SOLO_ABILITY_DICE:
                $soloActionDescription = clienttranslate('Roll a dice');
                break;
            case \GB\SOLO_ABILITY_DICE_PER_HUMAN:
                $soloActionDescription = clienttranslate('Roll a dice per Human icon');
                break;
            case \GB\SOLO_ABILITY_NUGGET_PER_ELF_1:
                $soloActionDescription = clienttranslate('Gain 1 nugget per Elf icon');
                break;
            case \GB\SOLO_ABILITY_NUGGET_PER_ELF_2:
                $soloActionDescription = clienttranslate('Gain 2 nuggets per Elf icon');
                break;
            case \GB\SOLO_ABILITY_NUGGET_PER_HUMAN_2:
                $soloActionDescription = clienttranslate('Gain 2 nuggets per Human icon');
                break;
            case \GB\SOLO_ABILITY_REVEAL_ENEMY:
                $soloActionDescription = clienttranslate('Player must reveal one enemy tile');
                break;
            case \GB\SOLO_ABILITY_DESTROY_ENEMY:
                $soloActionDescription = clienttranslate('Player must destroy one enemy tile');
                break;
            case \GB\SOLO_ABILITY_NUGGET_1:
                $soloActionDescription = clienttranslate('Gain 1 nugget');
                break;
            case \GB\SOLO_ABILITY_NUGGET_2:
                $soloActionDescription = clienttranslate('Gain 2 nuggets');
                break;
            case \GB\SOLO_ABILITY_NUGGET_3:
                $soloActionDescription = clienttranslate('Gain 3 nuggets');
                break;
            case \GB\SOLO_ABILITY_NUGGET_4:
                $soloActionDescription = clienttranslate('Gain 4 nuggets');
                break;
            case \GB\SOLO_ABILITY_NUGGET_5:
                $soloActionDescription = clienttranslate('Gain 5 nuggets');
                break;
            case \GB\SOLO_ABILITY_NUGGET_10:
                $soloActionDescription = clienttranslate('Gain 10 nuggets');
                break;
            case \GB\SOLO_ABILITY_MATERIAL_1:
                $soloActionDescription = clienttranslate('Gain 1 material');
                break;
            case \GB\SOLO_ABILITY_GOLD_1:
                $soloActionDescription = clienttranslate('Gain 1 gold');
                break;
            case \GB\SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD:
                $soloActionDescription = clienttranslate('Destroy the rightmost card from the GOLDblivion market');
                break;
            case \GB\SOLO_ABILITY_DESTROY_PLAYER_NUGGET_1:
                $soloActionDescription = clienttranslate("Destroy 1 of the player's nuggets");
                break;
            case \GB\SOLO_ABILITY_DESTROY_PLAYER_NUGGET_2:
                $soloActionDescription = clienttranslate("Destroy 2 of the player's nuggets");
                break;
            case \GB\SOLO_ABILITY_DESTROY_PLAYER_NUGGET_3:
                $soloActionDescription = clienttranslate("Destroy 3 of the player's nuggets");
                break;
            default:
                throw new \BgaSystemException("RemoveFirstSoloAction: Unknow solo action {$this->firstSoloAction}/" . count($actionList));
        }

        $notifier->notify(
            NTF_UPDATE_SOLO_ACTION_LIST,
            clienttranslate('${soloName} next ability is: ${soloActionDescription}'),
            [
                'soloName' => self::soloName(),
                'soloActionDescription' => $soloActionDescription,
                'soloActionList' => $gameStateMgr->getSoloActionList(),
                'i18n' => ['soloName', 'soloActionDescription'],
            ]
        );
    }

    public function getFirstSoloAction()
    {
        return $this->firstSoloAction;
    }
}

class GainNugget extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;
    use \GB\Actions\Traits\BaseGainTrait;

    public function __construct(int $playerId, int $count, ?int $conditionIcon = null)
    {
        parent::__construct($playerId);
        $this->count = $count;
        $this->conditionIcon = $conditionIcon;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gain = $this->getGain(true);

        $gameStateMgr = self::getMgr('game_state');
        $gameStateMgr->addSoloNuggetCountAction($gain);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${soloName} gains ${nuggetGain} ${nuggetImage}'),
            [
                'soloName' => self::soloName(),
                'nuggetGain' => $gain,
                'nuggetImage' => clienttranslate('nugget(s)'),
                'i18n' => ['soloName', 'nuggetImage'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }
}

class GainMaterial extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $gameStateMgr->addSoloMaterialCountAction(1);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${soloName} gains 1 ${materialImage}'),
            [
                'soloName' => self::soloName(),
                'materialImage' => clienttranslate('material'),
                'i18n' => ['soloName', 'materialImage'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }
}

class GainGold extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $gameEndWasTriggered = $gameStateMgr->isEndGameTriggered();

        $gameStateMgr->addSoloGoldCountAction(1);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${soloName} gains 1 ${goldImage}'),
            [
                'soloName' => self::soloName(),
                'goldImage' => clienttranslate('gold'),
                'i18n' => ['soloName', 'goldImage'],
            ]
        );

        if (!$gameEndWasTriggered && $gameStateMgr->isEndGameTriggered()) {
            $notifier->notify(
                NTF_LAST_ROUND,
                clienttranslate('${soloName} reached 10 ${goldImage}, this will be the last round'),
                [
                    'soloName' => self::soloName(),
                    'isLastRound' => true,
                    'goldImage' => clienttranslate('gold'),
                    'i18n' => ['soloName', 'goldImage'],
                ]
            );
        }

        self::notifyUpdateCounts($notifier);
    }
}

class GainRollDice extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;
    use \GB\Actions\Traits\BaseGainTrait;

    private $diceRolls;

    public function __construct(int $playerId, int $count, ?int $conditionIcon = null)
    {
        parent::__construct($playerId);
        $this->count = $count;
        $this->conditionIcon = $conditionIcon;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $nbDice = $this->getGain(true);
        if ($this->diceRolls === null) {
            $this->diceRolls = [];
            for ($i = 0; $i < $nbDice; ++$i) {
                $this->diceRolls[$i] = \bga_rand(0, 5);
            }
        }

        $gameStateMgr = self::getMgr('game_state');
        $actionList = $gameStateMgr->getSoloActionList();
        $gains = [];
        for ($i = 0; $i < $nbDice; ++$i) {
            $diceFace = $this->diceRolls[$i];
            $notifier->notify(
                NTF_ROLL_DICE,
                clienttranslate('${soloName} rolls a dice and gets: ${diceImage}'),
                [
                    'soloName' => self::soloName(),
                    'from' => [
                        'soloBoard' => true,
                    ],
                    'diceId' => $i,
                    'diceFace' => $diceFace,
                    'diceImage' => $diceFace,
                    'i18n' => ['soloName'],
                ]
            );
            switch ($diceFace) {
                default:
                case 0:
                case 1:
                    array_push($gains, \GB\SOLO_ABILITY_NUGGET_1);
                    break;
                case 2:
                case 3:
                    array_push($gains, \GB\SOLO_ABILITY_NUGGET_2);
                    break;
                case 4:
                    $nobleId = gameSoloNoble();
                    $board = \GB\SoloBoardDefMgr::getById($nobleId);
                    $gains = array_merge($gains, $board->magicAbilities);
                    break;
                case 5:
                    array_push($gains, \GB\SOLO_ABILITY_MATERIAL_1);
                    break;
            }
            $gameStateMgr->setSoloActionListAction(array_merge($gains, $actionList));

            $notifier->notifyNoMessage(
                NTF_UPDATE_SOLO_ACTION_LIST,
                [
                    'soloActionList' => $gameStateMgr->getSoloActionList(),
                ]
            );
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

class DestroyRightMarketCard extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $marketCards = array_values($componentMgr->getCardInBlueMarket());
        usort($marketCards, fn ($a, $b) => $a->locationPrimaryOrder <=> $b->locationPrimaryOrder);
        if (count($marketCards) == 0) {
            return;
        }
        $card = $marketCards[count($marketCards) - 1];

        $previousLocationPrimaryOrder = $card->locationPrimaryOrder;
        $card->modifyAction();
        $card->moveToDiscard();
        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${soloName} destroys the rightmost card in the GOLDblivion market ${cardName} ${componentImage}'),
            [
                'soloName' => self::soloName(),
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['soloName', 'cardName'],
            ]
        );

        self::drawToReplaceCard($card, $previousLocationPrimaryOrder, $notifier);

        self::notifyUpdateCounts($notifier);
    }
}

class DestroyPlayerNugget extends \BX\Action\BaseActionCommandNoUndo
{
    private $count;

    public function __construct(int $playerId, int $count)
    {
        parent::__construct($playerId);
        $this->count = $count;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $loosesAllNuggets = false;
        if ($this->count > $ps->nuggetCount) {
            $loosesAllNuggets = true;
        }

        $ps->modifyAction();
        $ps->loseNugget($this->count);

        $notifier->notify(
            NTF_UPDATE_NUGGET,
            $loosesAllNuggets
                ? clienttranslate('${player_name} looses all ${nuggetImage}')
                : clienttranslate('${player_name} looses ${lose} ${nuggetImage}'),
            [
                'nuggetCount' => $ps->nuggetCount,
                'lose' => $this->count,
                'nuggetImage' => clienttranslate('nugget(s)'),
                'i18n' => ['nuggetImage'],
            ]
        );
    }
}

class RevealEnemy extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $locationId;

    public function __construct(int $playerId, int $locationId)
    {
        parent::__construct($playerId);
        $this->locationId = $locationId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $locationIds = $componentMgr->getAllAccessibleHiddenEnemyLocationsForSoloMode();
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

        self::notifyUpdateCounts($notifier);
    }
}

class DestroyEnemy extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;

    public function __construct(int $playerId, int $componentId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $enemy = $this->getComponent();
        self::enforceComponentEnemy($enemy);
        self::enforceComponentNoPlayerId($enemy);
        self::enforceComponentLocationId($enemy, \GB\COMPONENT_LOCATION_ID_MARKET);

        $enemyLocation = $enemy->locationPrimaryOrder;
        if ($enemy->def()->subCategoryId == \GB\COMPONENT_SUB_CATEGORY_ID_PERMANENT) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} chooses an enemy: ${componentName} ${componentImage}'),
                [
                    'componentName' => $enemy->def()->name,
                    'componentImage' => $enemy->typeId,
                    'i18n' => ['componentName'],
                ]
            );
        } else {
            $enemy->modifyAction();
            $enemy->moveEnemyToPlayerBoard(null);

            $notifier->notify(
                NTF_UPDATE_COMPONENTS,
                clienttranslate('${player_name} destroys an enemy: ${componentName} ${componentImage}'),
                [
                    'components' => [$enemy],
                    'componentName' => $enemy->def()->name,
                    'componentImage' => $enemy->typeId,
                    'i18n' => ['componentName'],
                ]
            );
        }

        if ($enemyLocation == \GB\EnemyMapDefMgr::getPermanent()->id && $enemy->def()->subCategoryId != \GB\COMPONENT_SUB_CATEGORY_ID_PERMANENT) {
            $componentMgr = self::getMgr('component');
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

        $gameStateMgr = self::getMgr('game_state');
        $gains = [];
        $enemy->def()->abilities[0]->foreachGain(function ($g) use (&$gains) {
            switch ($g->gainTypeId) {
                case \GB\GAIN_ID_GAIN_NUGGET:
                    switch ($g->count) {
                        case 1:
                            $gains[] = \GB\SOLO_ABILITY_NUGGET_1;
                            break;
                        case 2:
                            $gains[] = \GB\SOLO_ABILITY_NUGGET_2;
                            break;
                        case 3:
                            $gains[] = \GB\SOLO_ABILITY_NUGGET_3;
                            break;
                        case 4:
                            $gains[] = \GB\SOLO_ABILITY_NUGGET_4;
                            break;
                        case 5:
                            $gains[] = \GB\SOLO_ABILITY_NUGGET_5;
                            break;
                        case 10:
                            $gains[] = \GB\SOLO_ABILITY_NUGGET_10;
                            break;
                        default:
                            throw new \BgaSystemException("Unsupported count {$g->count} to convert to solo nugget ability");
                    }
                    break;
                case \GB\GAIN_ID_GAIN_MATERIAL:
                    for ($i = 0; $i < $g->count; ++$i) {
                        $gains[] = \GB\SOLO_ABILITY_MATERIAL_1;
                    }
                    break;
                case \GB\GAIN_ID_GAIN_GOLD:
                    for ($i = 0; $i < $g->count; ++$i) {
                        $gains[] = \GB\SOLO_ABILITY_GOLD_1;
                    }
                    break;
                case \GB\GAIN_ID_ROLL_DICE:
                    for ($i = 0; $i < $g->count; ++$i) {
                        $gains[] = \GB\SOLO_ABILITY_DICE;
                    }
                    break;
                case \GB\GAIN_ID_GAIN_MAGIC:
                    $nobleId = gameSoloNoble();
                    $board = \GB\SoloBoardDefMgr::getById($nobleId);
                    for ($i = 0; $i < $g->count; ++$i) {
                        $gains = array_merge($gains, $board->magicAbilities);
                    }
                    break;
                case \GB\GAIN_ID_GAIN_COMBAT_POWER:
                case \GB\GAIN_ID_GAIN_FREE_RED_CARD:
                    break;
                default:
                    throw new \BgaSystemException("Unsupported gainTypeId {$g->gainTypeId} to convert to solo ability");
            }
        });
        $actionList = $gameStateMgr->getSoloActionList();
        $gameStateMgr->setSoloActionListAction(array_merge($gains, $actionList));

        $notifier->notify(
            NTF_UPDATE_SOLO_ACTION_LIST,
            clienttranslate('${soloName} gets new abilities'),
            [
                'soloName' => self::soloName(),
                'soloActionList' => $gameStateMgr->getSoloActionList(),
                'i18n' => ['soloName'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }
}

class SoloConversion extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\SoloTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('End of Solo Noble turn: conversions are applied'),
            []
        );
        $gameStateMgr = self::getMgr('game_state');
        $gameEndWasTriggered = $gameStateMgr->isEndGameTriggered();

        $nobleId = gameSoloNoble();
        $board = \GB\SoloBoardDefMgr::getById($nobleId);

        // Material
        if ($board->materialConvertNuggetRate !== null) {
            $materialCount = $gameStateMgr->getSoloMaterialCount();
            $materialPay = intdiv($materialCount, $board->materialConvertRate);
            $nuggetGain = $materialPay * $board->materialConvertNuggetRate;

            $gameStateMgr->addSoloNuggetCountAction($nuggetGain);
            $gameStateMgr->addSoloMaterialCountAction(-1 * $materialPay);

            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${soloName} converts ${materialPay} ${materialImage} to ${nuggetGain} ${nuggetImage}'),
                [
                    'soloName' => self::soloName(),
                    'materialPay' => $materialPay,
                    'nuggetGain' => $nuggetGain,
                    'materialImage' => clienttranslate('material'),
                    'nuggetImage' => clienttranslate('nugget(s)'),
                    'i18n' => ['soloName', 'materialImage', 'nuggetImage'],
                ]
            );
        } else {
            $materialCount = $gameStateMgr->getSoloMaterialCount();
            $goldGain = intdiv($materialCount, $board->materialConvertRate);
            $materialPay = $goldGain * $board->materialConvertRate;

            $gameStateMgr->addSoloGoldCountAction($goldGain);
            $gameStateMgr->addSoloMaterialCountAction(-1 * $materialPay);

            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${soloName} converts ${materialPay} ${materialImage} to ${goldGain} ${goldImage}'),
                [
                    'soloName' => self::soloName(),
                    'materialPay' => $materialPay,
                    'goldGain' => $goldGain,
                    'materialImage' => clienttranslate('material'),
                    'goldImage' => clienttranslate('gold'),
                    'i18n' => ['soloName', 'materialImage', 'goldImage'],
                ]
            );
        }

        // Nuggets
        $nuggetCount = $gameStateMgr->getSoloNuggetCount();
        $goldGain = intdiv($nuggetCount, $board->nuggetConvertRate);
        $nuggetPay = $goldGain * $board->nuggetConvertRate;

        $gameStateMgr->addSoloGoldCountAction($goldGain);
        $gameStateMgr->addSoloNuggetCountAction(-1 * $nuggetPay);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${soloName} converts ${nuggetPay} ${nuggetImage} to ${goldGain} ${goldImage}'),
            [
                'soloName' => self::soloName(),
                'nuggetPay' => $nuggetPay,
                'goldGain' => $goldGain,
                'nuggetImage' => clienttranslate('material'),
                'goldImage' => clienttranslate('gold'),
                'i18n' => ['soloName', 'nuggetImage', 'goldImage'],
            ]
        );

        if (!$gameEndWasTriggered && $gameStateMgr->isEndGameTriggered()) {
            $notifier->notify(
                NTF_LAST_ROUND,
                clienttranslate('${soloName} reached 10 ${goldImage}, this will be the last round'),
                [
                    'soloName' => self::soloName(),
                    'isLastRound' => true,
                    'goldImage' => clienttranslate('gold'),
                    'i18n' => ['soloName', 'goldImage'],
                ]
            );
        }

        self::notifyUpdateCounts($notifier);
    }
}

class DestroySoloNobleNugget extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $count;

    public function __construct(int $playerId, int $count)
    {
        parent::__construct($playerId);
        $this->count = $count;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->saveUndoCounts();

        $gameStateMgr = self::getMgr('game_state');
        if ($this->count > $gameStateMgr->getSoloNuggetCount()) {
            throw new \BgaSystemException('Trying to destroy too many solo noble nugget');
        }
        $gameStateMgr->addSoloNuggetCountAction(-1 * $this->count);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} destroys ${count} ${nuggetImage} of the Solo Noble'),
            [
                'count' => $this->count,
                'nuggetImage' => clienttranslate('nugget(s)'),
                'i18n' => ['nuggetImage'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->notifyUndoCounts($notifier);
    }
}
