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

namespace GB\Actions\Ability;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/Traits.php');

class GainBlueCardToPlayerDeck extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $sourceLocationId;
    private $humanoidOnly;

    public function __construct(int $playerId, int $componentId, int $sourceLocationId, $humanoidOnly = false)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->sourceLocationId = $sourceLocationId;
        $this->humanoidOnly = $humanoidOnly;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->getComponent();
        self::enforceComponentCardBlue($card);
        self::enforceComponentNoPlayerId($card);
        self::enforceComponentLocationId($card, $this->sourceLocationId);
        if ($this->humanoidOnly && !$card->def()->isHumanoid()) {
            throw new \BgaSystemException('Blue card must be an humanoid');
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->statGainedBlueCard += 1;

        $previousLocationPrimaryOrder = $card->locationPrimaryOrder;

        $card->modifyAction();
        $card->moveToPlayerDeck($this->playerId);
        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${player_name} adds ${cardName} to their GOLDblivion deck ${componentImage}'),
            [
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );

        if ($this->sourceLocationId == \GB\COMPONENT_LOCATION_ID_MARKET) {
            self::drawToReplaceCard($card, $previousLocationPrimaryOrder, $notifier);
        }

        self::notifyUpdateCounts($notifier);
        $this->shufflePlayerBlueDeck($notifier);
    }
}

class GainRedCardToPlayerDeck extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $redDeckPlayerId;

    public function __construct(int $playerId, ?int $componentId, ?int $redDeckPlayerId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->redDeckPlayerId = $redDeckPlayerId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->componentId === null && $this->redDeckPlayerId === null) {
            throw new \BgaSystemException('BUG! Both componentId and redDeckPlayerId are null!');
        }
        if ($this->componentId !== null && $this->redDeckPlayerId !== null) {
            throw new \BgaSystemException('BUG! Both componentId and redDeckPlayerId are not null!');
        }

        $card = null;
        if ($this->componentId === null) {
            if ($this->playerId == $this->redDeckPlayerId) {
                throw new \BgaSystemException('BUG! Both playerId and redDeckPlayerId are the same!');
            }
            $componentMgr = self::getMgr('component');
            if (!$componentMgr->atLeastOneRedDeckIsEmpty()) {
                throw new \BgaSystemException('BUG! Cannot steal red cards, both decks have cards');
            }
            $card = $componentMgr->getTopCardFromRedPlayerDeck($this->redDeckPlayerId);
            if ($card === null) {
                throw new \BgaSystemException("BUG! redDeckPlayerId {$this->redDeckPlayerId} deck is empty");
            }
            $playerMgr = self::getMgr('player');
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} takes a Combat card from the top of the combat deck of ${otherPlayerName}'),
                [
                    'otherPlayerName' => $playerMgr->getByPlayerId($this->redDeckPlayerId)->playerName,
                ]
            );
        } else {
            $card = $this->getComponent();
            self::enforceComponentCardRed($card);
            self::enforceComponentNoPlayerId($card);
            self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_MARKET);
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->statGainedRedCard += 1;

        $previousLocationPrimaryOrder = $card->locationPrimaryOrder;

        $card->modifyAction();
        $card->moveToPlayerDeck($this->playerId);
        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${player_name} adds ${cardName} to their combat deck ${componentImage}'),
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_DECK,
                    'playerId' => $this->redDeckPlayerId,
                ],
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );

        if ($this->componentId !== null) {
            self::drawToReplaceCard($card, $previousLocationPrimaryOrder, $notifier);
        }

        self::notifyUpdateCounts($notifier);
        $this->shufflePlayerRedDeck($notifier);
    }
}

class DrawBlueCardToPlayerHand extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $nbCards;

    public function __construct(int $playerId, int $nbCards)
    {
        parent::__construct($playerId);
        if ($nbCards <= 0) {
            throw new \BgaSystemException('BUG! nbCards to draw cannot be negative or zero');
        }
        $this->nbCards = $nbCards;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $drawnCards = [];
        $componentMgr = self::getMgr('component');
        for ($i = 0; $i < $this->nbCards; ++$i) {
            $card = $componentMgr->getTopCardFromBluePlayerDeck($this->playerId);
            if ($card === null) {
                break;
            }
            $drawnCards[] = $card;
            $card->modifyAction();
            $card->moveToPlayerHand($this->playerId);
        }
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} draws ${nbDrawnCards} card(s) from their GOLDblivion deck'),
            [
                'nbDrawnCards' => count($drawnCards),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_DECK,
                    'playerId' => $this->playerId,
                ],
                'components' => $drawnCards,
            ]
        );

        self::notifyUpdateCounts($notifier);
    }
}

class PlaceBlueCardInPlayerDevelopment extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $side;
    private $undoCard;
    private $undoPlayerDevelopmentTypeId;

    public function __construct(int $playerId, int $componentId, int $side)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        if ($side != 0 && $side != 1) {
            throw new \BgaSystemException('BUG! side can only be 0 or 1');
        }
        $this->side = $side;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->getComponent();
        self::enforceComponentCardBlue($card);
        self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_PLAYER_HAND);
        self::enforceComponentCurrentPlayerId($card);

        $componentMgr = self::getMgr('component');

        $this->undoCard = \BX\Meta\deepClone($card);
        $this->saveUndoCounts();
        $this->undoPlayerDevelopmentTypeId = \BX\Meta\deepClone($componentMgr->getPlayerDevelopmentTypeId($this->playerId));

        $card->modifyAction();
        $card->moveToPlayerDevelopment($this->playerId, $this->side);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            $this->side == 0
                ? clienttranslate('${player_name} develops their left side for ${nuggetImage}')
                : clienttranslate('${player_name} develops their right side for ${materialImage}'),
            [
                'nuggetImage' => clienttranslate('nugget(s)'),
                'materialImage' => clienttranslate('material'),
                'i18n' => ['nuggetImage', 'materialImage'],
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'components' => [$card],
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_DEVELOPMENT_TYPE_ID,
            [
                'playerDevelopmentTypeId' => $componentMgr->getPlayerDevelopmentTypeId($this->playerId),
            ]
        );
        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT,
                    'playerId' => $this->playerId,
                    'locationPrimaryOrder' => $this->side,
                ],
                'components' => [$this->undoCard],
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_DEVELOPMENT_TYPE_ID,
            [
                'playerDevelopmentTypeId' => $this->undoPlayerDevelopmentTypeId,
            ]
        );
        $this->notifyUndoCounts($notifier);
    }
}

class Production extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');

        $nuggetCardCount = count($componentMgr->getPlayerCardDevelopmentNugget($this->playerId));
        $nuggetAdd = $nuggetCardCount;

        $materialCardCount = count($componentMgr->getPlayerCardDevelopmentMaterial($this->playerId));
        $materialAdd = intdiv($materialCardCount, 2);

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $ps->modifyAction();
        $ps->gainNugget($nuggetAdd);
        $materialMaxed = $ps->gainMaterial($materialAdd);

        $notifier->notify(
            NTF_UPDATE_NUGGET,
            clienttranslate('${player_name} produces ${nuggetAdd} ${nuggetImage} with ${cardCount} card(s)'),
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT,
                    'playerId' => $this->playerId,
                    'locationPrimaryOrder' => 0,
                ],
                'nuggetAdd' => $nuggetAdd,
                'nuggetCount' => $ps->nuggetCount,
                'nuggetImage' => clienttranslate('nugget(s)'),
                'cardCount' => $nuggetCardCount,
                'i18n' => ['nuggetImage'],
            ]
        );

        $notifier->notify(
            NTF_UPDATE_MATERIAL,
            clienttranslate('${player_name} produces ${materialAdd} ${materialImage} with ${cardCount} card(s)'),
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT,
                    'playerId' => $this->playerId,
                    'locationPrimaryOrder' => 1,
                ],
                'materialAdd' => $materialAdd,
                'materialCount' => $ps->materialCount,
                'materialImage' => clienttranslate('material(s)'),
                'cardCount' => $materialCardCount,
                'i18n' => ['materialImage'],
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
}

class Pass extends \BX\Action\BaseActionCommandNoUndo
{
    private $isAutomatic;

    public function __construct(int $playerId, bool $isAutomatic = false)
    {
        parent::__construct($playerId);
        $this->isAutomatic = $isAutomatic;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        if ($ps->passed) {
            throw new \BgaSystemException('BUG! Player already passed');
        }
        if ($ps->actionCount > nbMainAction()) {
            throw new \BgaSystemException('BUG! Player has no more main actions');
        }

        if (isGameSolo()) {
            $componentMgr = self::getMgr('component');
            if (count($componentMgr->getCardsInPlayerHand($this->playerId)) > 0) {
                throw new \BgaSystemException('BUG! Player cannot pass with cards in hand in solo games');
            }
        }

        $ps->modifyAction();
        $ps->passed = true;

        $notifier->notify(
            NTF_UPDATE_PASS,
            $this->isAutomatic
                ? clienttranslate('${player_name} passes automatically, they had not other possible actions')
                : clienttranslate('${player_name} passes'),
            [
                'passed' => true,
            ]
        );
    }
}

class ConvertNuggetToGold extends \BX\Action\BaseActionCommand
{
    public const CONVERT_RATIO = 7;

    private $convertMaximum;
    private $undoNugget;
    private $undoIsEndGameTriggered;
    private $scoreAction;

    public function __construct(int $playerId, bool $convertMaximum = false)
    {
        parent::__construct($playerId);
        $this->convertMaximum = $convertMaximum;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $this->undoIsEndGameTriggered = $gameStateMgr->isEndGameTriggered();

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $convertedNuggets = 0;
        $scoreToAdd = 0;
        while ($ps->nuggetCount - $convertedNuggets >= self::CONVERT_RATIO) {
            $convertedNuggets += self::CONVERT_RATIO;
            $scoreToAdd += 1;
            if (!$this->convertMaximum) {
                break;
            }
        }

        $this->undoNugget = $ps->nuggetCount;

        if ($convertedNuggets > 0) {
            $ps->modifyAction();
            $ps->payNugget($convertedNuggets);
            $ps->statGainedGoldFromNugget += $scoreToAdd;

            $notifier->notifyNoMessage(
                NTF_UPDATE_NUGGET,
                [
                    'from' => [
                        'locationId' => \GB\COMPONENT_LOCATION_ID_SCORE,
                        'playerId' => $this->playerId,
                    ],
                    'nuggetCount' => $ps->nuggetCount,
                ]
            );
        }

        $this->scoreAction = new \BX\Player\UpdatePlayerScoreActionCommand($this->playerId);
        $this->scoreAction->do(
            $notifier,
            $scoreToAdd,
            clienttranslate('${player_name} gains ${scorePositive} ${goldImage} by converting ${convertedNuggets} ${nuggetImage}'),
            [
                'convertedNuggets' => $convertedNuggets,
                'goldImage' => clienttranslate('gold'),
                'nuggetImage' => clienttranslate('nugget(s)'),
                'i18n' => ['goldImage', 'nuggetImage'],
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
        $notifier->notifyNoMessage(
            NTF_UPDATE_NUGGET,
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_SCORE,
                    'playerId' => $this->playerId,
                ],
                'nuggetCount' => $this->undoNugget,
            ]
        );
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

class DestroyCard extends \BX\Action\BaseActionCommandNoUndo
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $locationId;
    private $categoryId;
    private $isAutomatic;
    private $isNoble;

    public function __construct(int $playerId, int $componentId, int $categoryId = null, bool $isAutomatic = false, bool $isNoble = false)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
        $this->categoryId = $categoryId;
        $this->isAutomatic = $isAutomatic;
        $this->isNoble = $isNoble;
        if (
            $this->categoryId !== null
            && $this->categoryId != \GB\COMPONENT_CATEGORY_ID_CARD_BLUE
            && $this->categoryId != \GB\COMPONENT_CATEGORY_ID_CARD_RED
        ) {
            throw new \BgaSystemException("BUG! Invalid categoryId: {$this->categoryId}");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->getComponent();
        if ($this->categoryId === null) {
            self::enforceComponentCard($card);
        } else if ($this->categoryId == \GB\COMPONENT_CATEGORY_ID_CARD_BLUE) {
            self::enforceComponentCardBlue($card);
        } else if ($this->categoryId == \GB\COMPONENT_CATEGORY_ID_CARD_RED) {
            self::enforceComponentCardRed($card);
        } else {
            throw new \BgaSystemException("BUG! Invalid categoryId: {$this->categoryId}");
        }
        self::enforceComponentNoPlayerId($card);
        if ($this->isNoble) {
            self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_DRAFT_MARKET);
        } else {
            self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_MARKET);

            $playerStateMgr = self::getMgr('player_state');
            $ps = $playerStateMgr->getByPlayerId($this->playerId);
            $ps->modifyAction();
            if ($card->def()->isCardBlue()) {
                $ps->statDestroyedBlueMarket += 1;
            } else {
                $ps->statDestroyedRedMarket += 1;
            }
        }


        $previousLocationPrimaryOrder = $card->locationPrimaryOrder;
        $card->modifyAction();
        $card->moveToDiscard();
        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            $this->isAutomatic
                ? clienttranslate('${player_name} automatically destroys ${cardName} ${componentImage}')
                : clienttranslate('${player_name} destroys ${cardName} ${componentImage}'),
            [
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );

        if (!$this->isNoble) {
            self::drawToReplaceCard($card, $previousLocationPrimaryOrder, $notifier);
        }

        self::notifyUpdateCounts($notifier);
    }
}

class PlayMainAction extends \BX\Action\BaseActionCommand
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
        $ps->actionCount += 1;
        if ($ps->actionCount > nbMainAction()) {
            throw new \BgaUserException($notifier->_('You already played all your main actions'));
        }

        $msg = clienttranslate('${player_name} plays their first main action');
        if ($ps->actionCount == 2) {
            $msg = clienttranslate('${player_name} plays their second main action');
        } else if ($ps->actionCount == 3) {
            $msg = clienttranslate('${player_name} plays their third main action');
        }

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            $msg,
            []
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class PlayCardFromHand extends \BX\Action\BaseActionCommand
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
        self::enforceComponentCardBlue($card);
        self::enforceComponentPlayerId($card, $this->playerId);
        self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_PLAYER_HAND);

        $this->undoCard = \BX\Meta\deepClone($card);
        $this->saveUndoCounts();

        $card->modifyAction();
        $card->moveToPlayerBluePlayArea($this->playerId);
        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${player_name} plays ${cardName} ${componentImage}'),
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_HAND,
                    'playerId' => $this->playerId,
                ],
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'components' => [$this->undoCard],
            ]
        );
        $this->notifyUndoCounts($notifier);
    }
}

class PlayVillageStart extends \BX\Action\BaseActionCommand
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
        $componentMgr = self::getMgr('component');
        $visibleVillage = $this->getComponent();
        if (!$visibleVillage->def()->isVillage()) {
            throw new \BgaSystemException("BUG! componentId {$visibleVillage->componentId} is not a village");
        }
        self::enforceComponentLocationId($visibleVillage, \GB\COMPONENT_LOCATION_ID_MARKET);

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            $visibleVillage->locationPrimaryOrder == 0
                ? clienttranslate('${player_name} activates left village tile')
                : clienttranslate('${player_name} activates right village tile'),
            []
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class PlayVillageEnd extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;

    private $componentId;
    private $otherComponentId;

    public function __construct(int $playerId, int $componentId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $visibleVillage = $this->getComponent();
        if (!$visibleVillage->def()->isVillage()) {
            throw new \BgaSystemException("BUG! componentId {$visibleVillage->componentId} is not a village");
        }
        self::enforceComponentLocationId($visibleVillage, \GB\COMPONENT_LOCATION_ID_MARKET);

        $otherVillage = $componentMgr->otherSideVillage($this->componentId);
        $this->otherComponentId = $otherVillage->componentId;

        $visibleVillage->modifyAction();
        $visibleVillage->flipVillageToSupply();
        $otherVillage->modifyAction();
        $otherVillage->flipVillageToMarket();

        $notifier->notify(
            NTF_FLIP_VILLAGE,
            $visibleVillage->locationPrimaryOrder == 0
                ? clienttranslate('${player_name} flips left village tile')
                : clienttranslate('${player_name} flips right village tile'),
            [
                'fromId' => $this->componentId,
                'toId' => $this->otherComponentId,
                'toTypeId' => $otherVillage->typeId,
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $visibleVillage = $this->getComponent();
        $notifier->notifyNoMessage(
            NTF_FLIP_VILLAGE,
            [
                'fromId' => $this->otherComponentId,
                'toId' => $this->componentId,
                'toTypeId' => $visibleVillage->typeId,
            ]
        );
    }
}

class BuyCard extends \BX\Action\BaseActionCommandNoUndo
{
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
        $card = $this->getComponent();
        $isBlue = false;
        $isBuilding = false;
        if ($card->def()->isCardBlue()) {
            $isBlue = true;
            if ($card->def()->isBuilding()) {
                $isBuilding = true;
            }
        } else if ($card->def()->isCardRed()) {
            $isBlue = false;
        } else {
            throw new \BgaSystemException("BUG! Card is not blue nor red");
        }
        self::enforceComponentNoPlayerId($card);
        self::enforceComponentLocationId($card, \GB\COMPONENT_LOCATION_ID_MARKET);

        $componentMgr = self::getMgr('component');
        if ($isBuilding) {
            $componentMgr = self::getMgr('component');
            foreach ($componentMgr->getCardsInPlayerBuildingPlayArea($this->playerId) as $otherCard) {
                if ($card->typeId == $otherCard->typeId) {
                    throw new \BgaUserException($notifier->_('You cannot have the same building twice'));
                }
            }
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $nuggetPay = 0;
        $materialPay = 0;
        if (!$card->def()->payComponentCost($ps->nuggetCount, $ps->materialCount, $nuggetPay, $materialPay)) {
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

        $previousLocationPrimaryOrder = $card->locationPrimaryOrder;
        $card->modifyAction();

        $notifText = null;
        if ($isBuilding) {
            $card->moveToPlayerBuildingPlayArea($this->playerId);
            $notifText = clienttranslate('${player_name} buys and adds it to their play area ${cardName} ${componentImage}');
            $ps->statGainedBlueCard += 1;
        } else {
            $card->moveToPlayerDeck($this->playerId);
            if ($isBlue) {
                $notifText = clienttranslate('${player_name} buys ${cardName} and adds it to their GOLDblivion deck ${componentImage}');
                $ps->statGainedBlueCard += 1;
            } else {
                $notifText = clienttranslate('${player_name} buys ${cardName} and adds it to their combat deck ${componentImage}');
                $ps->statGainedRedCard += 1;
            }
        }

        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            $notifText,
            [
                'components' => [$card],
                'cardName' => $card->def()->name,
                'componentImage' => $card->typeId,
                'i18n' => ['cardName'],
            ]
        );

        if ($isBlue && !$isBuilding) {
            $this->shufflePlayerBlueDeck($notifier);
        } else if (!$isBlue) {
            $this->shufflePlayerRedDeck($notifier);
        }

        self::drawToReplaceCard($card, $previousLocationPrimaryOrder, $notifier);

        self::notifyUpdateCounts($notifier);
    }
}

class ActivateBuilding extends \BX\Action\BaseActionCommand
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

        if ($building->isUsed) {
            throw new \BgaSystemException("BUG! building {$building->componentId} is already used");
        }

        $building->modifyAction();
        $building->isUsed = true;

        $notifier->notify(
            NTF_USE_COMPONENTS,
            clienttranslate('${player_name} activates building ${cardName} ${componentImage}'),
            [
                'componentIds' => [$this->componentId],
                'isUsed' => true,
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
                'isUsed' => false,
            ]
        );
    }
}

class PlayMagic extends \BX\Action\BaseActionCommand
{
    use \GB\Actions\Traits\ComponentQueryTrait;
    use \GB\Actions\Traits\ComponentNotificationTrait;

    private $componentId;
    private $undoMagic;

    public function __construct(int $playerId, int $componentId)
    {
        parent::__construct($playerId);
        $this->componentId = $componentId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $magic = $this->getComponent();
        if (!$magic->def()->isMagic()) {
            throw new \BgaSystemException("BUG! componentId {$magic->componentId} is not a magic token");
        }
        self::enforceComponentLocationId($magic, \GB\COMPONENT_LOCATION_ID_PLAYER_BOARD);
        self::enforceComponentPlayerId($magic, $this->playerId);

        $previousLocationPrimaryOrder = $magic->locationPrimaryOrder;
        $this->undoMagic = \BX\Meta\deepClone($magic);
        $this->saveUndoCounts();

        $magic->modifyAction();
        $magic->moveToSupplyVisible();

        $notifier->notify(
            NTF_UPDATE_COMPONENTS,
            clienttranslate('${player_name} uses their magic token ${componentName} ${componentImage}'),
            [
                'from' => [
                    'locationId' => \GB\COMPONENT_LOCATION_ID_PLAYER_BOARD,
                    'playerId' => $this->playerId,
                    'locationPrimaryOrder' => $previousLocationPrimaryOrder,
                ],
                'components' => [$magic],
                'componentName' => $magic->def()->name,
                'componentImage' => $magic->typeId,
                'i18n' => ['componentName'],
            ]
        );

        self::notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_COMPONENTS,
            [
                'components' => [$this->undoMagic],
            ]
        );
        $this->notifyUndoCounts($notifier);
    }
}
