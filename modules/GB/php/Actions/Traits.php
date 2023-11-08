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

namespace GB\Actions\Traits;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Component.php');

trait ComponentQueryTrait
{
    static private function getComponentById(?int $componentId)
    {
        if ($componentId === null) {
            throw new \BgaSystemException("BUG! componentId is null");
        }
        $componentMgr = self::getMgr('component');
        $c = $componentMgr->getById($componentId);
        if ($c === null) {
            throw new \BgaSystemException("BUG! componentId {$componentId} does not exist");
        }
        return $c;
    }

    private function getComponent()
    {
        return self::getComponentById($this->componentId);
    }

    static private function enforceComponentCardBlue(\GB\Component $c)
    {
        if (!$c->def()->isCardBlue()) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} is not a blue card");
        }
    }

    static private function enforceComponentCardRed(\GB\Component $c)
    {
        if (!$c->def()->isCardRed()) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} is not a red card");
        }
    }

    static private function enforceComponentCard(\GB\Component $c)
    {
        if (!$c->def()->isCardBlue() && !$c->def()->isCardRed()) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} is not a card");
        }
    }

    static private function enforceComponentEnemy(\GB\Component $c)
    {
        if (!$c->def()->isEnemy()) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} is not an enemy");
        }
    }

    static private function enforceComponentNoPlayerId(\GB\Component $c)
    {
        if ($c->playerId !== null) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} already owned by a player");
        }
    }

    static private function enforceComponentPlayerId(\GB\Component $c, int $playerId)
    {
        if ($c->playerId != $playerId) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} is not owned by playerId $playerId");
        }
    }

    private function enforceComponentCurrentPlayerId(\GB\Component $c)
    {
        self::enforceComponentPlayerId($c, $this->playerId);
    }

    static private function enforceComponentLocationId(\GB\Component $c, int $locationId)
    {
        if ($c->locationId != $locationId) {
            throw new \BgaSystemException("BUG! componentId {$c->componentId} is not in locationId $locationId");
        }
    }
}

trait ComponentNotificationTrait
{
    private $undoCounts;

    static private function notifyUpdateCounts(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $componentMgr = self::getMgr('component');
        $notifier->notifyNoMessage(
            NTF_UPDATE_COUNTS,
            [
                'componentCounts' => $componentMgr->getAllCounts(),
            ]
        );
    }

    private function saveUndoCounts()
    {
        $componentMgr = self::getMgr('component');
        $this->undoCounts = \BX\Meta\deepClone($componentMgr->getAllCounts());
    }

    private function notifyUndoCounts(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_COUNTS,
            [
                'componentCounts' => $this->undoCounts,
            ]
        );
    }

    static private function shufflePlayersBlueDeck(\BX\Action\BaseActionCommandNotifier $notifier, array $playerIdArray)
    {
        if (empty($playerIdArray)) {
            return;
        }
        $atLeastOneShuffled = false;
        $componentMgr = self::getMgr('component');
        foreach ($playerIdArray as $playerId) {
            if ($componentMgr->sufflePlayerBlueDeckAction($playerId)) {
                $atLeastOneShuffled = true;
            }
        }
        if ($atLeastOneShuffled) {
            $notifier->notifyNoMessage(
                NTF_SHUFFLE_BLUE_DECK,
                [
                    'playerIds' => $playerIdArray,
                ]
            );
        }
    }

    private function shufflePlayerBlueDeck(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        self::shufflePlayersBlueDeck($notifier, [$this->playerId]);
    }

    static private function shufflePlayersRedDeck(\BX\Action\BaseActionCommandNotifier $notifier, array $playerIdArray)
    {
        if (empty($playerIdArray)) {
            return;
        }
        $atLeastOneShuffled = false;
        $componentMgr = self::getMgr('component');
        foreach ($playerIdArray as $playerId) {
            if ($componentMgr->sufflePlayerRedDeckAction($playerId)) {
                $atLeastOneShuffled = true;
            }
        }
        if ($atLeastOneShuffled) {
            $notifier->notifyNoMessage(
                NTF_SHUFFLE_RED_DECK,
                [
                    'playerIds' => $playerIdArray,
                ]
            );
        }
    }

    private function shufflePlayerRedDeck(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        self::shufflePlayersRedDeck($notifier, [$this->playerId]);
    }

    static private function drawToReplaceCard(
        \GB\Component $cardToReplace,
        int $previousLocationPrimaryOrder,
        \BX\Action\BaseActionCommandNotifier $notifier
    ) {
        $componentMgr = self::getMgr('component');
        if ($cardToReplace->def()->isCardBlue()) {
            $drawnCard = $componentMgr->getTopCardFromBlueDeck();
            if ($drawnCard === null) {
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    isGameSolo()
                        ? clienttranslate('GOLDblivion deck is empty, you lost the game!')
                        : clienttranslate('GOLDblivion deck is empty, the market will not be refilled'),
                    []
                );
                if (isGameSolo()) {
                    $gameStateMgr = self::getMgr('game_state');
                    $gameStateMgr->setSoloLostUnfilledMarketAction();
                }
            } else {
                if (isGameSolo()) {
                    $marketCards = array_values($componentMgr->getCardInBlueMarket());
                    usort($marketCards, fn ($a, $b) => $a->locationPrimaryOrder <=> $b->locationPrimaryOrder);
                    $updatedMarket = [];
                    foreach ($marketCards as $i => $c) {
                        $order = $i + 1;
                        if ($c->locationPrimaryOrder == $order) {
                            continue;
                        }
                        $c->modifyAction();
                        $c->locationPrimaryOrder = $order;
                        $updatedMarket[] = $c;
                    }
                    if (count($updatedMarket) > 0) {
                        $notifier->notify(
                            NTF_UPDATE_COMPONENTS,
                            clienttranslate('GOLDblivion cards in market slide to the right'),
                            [
                                'components' => $updatedMarket,
                                'fast' => true,
                            ]
                        );
                    }
                    $previousLocationPrimaryOrder = 0;
                }
                $drawnCard->modifyAction();
                $drawnCard->moveToBlueMarket($previousLocationPrimaryOrder);
                $notifier->notify(
                    NTF_UPDATE_COMPONENTS,
                    clienttranslate('GOLDblivion card ${cardName} is drawn to refill the market ${componentImage}'),
                    [
                        'from' => [
                            'locationId' => \GB\COMPONENT_LOCATION_ID_SUPPLY,
                        ],
                        'components' => [$drawnCard],
                        'cardName' => $drawnCard->def()->name,
                        'componentImage' => $drawnCard->typeId,
                        'i18n' => ['cardName'],
                    ]
                );
            }
        } else {
            $drawnCard = $componentMgr->getTopCardFromRedDeck($previousLocationPrimaryOrder);
            if ($drawnCard !== null) {
                $drawnCard->modifyAction();
                $drawnCard->moveToRedMarket($previousLocationPrimaryOrder);
                $notifier->notify(
                    NTF_UPDATE_COMPONENTS,
                    clienttranslate('Combat card ${cardName} is drawn to refill the market ${componentImage}'),
                    [
                        'from' => [
                            'locationId' => \GB\COMPONENT_LOCATION_ID_SUPPLY,
                            'locationPrimaryOrder' => $previousLocationPrimaryOrder,
                        ],
                        'components' => [$drawnCard],
                        'cardName' => $drawnCard->def()->name,
                        'componentImage' => $drawnCard->typeId,
                        'i18n' => ['cardName'],
                    ]
                );
            }
        }
    }
}

trait BaseGainTrait
{
    protected $count;
    protected $conditionIcon;

    protected function getGain(bool $forSoloNoble = false)
    {
        if ($this->conditionIcon === null) {
            return $this->count;
        }
        $componentMgr = self::getMgr('component');
        if ($forSoloNoble) {
            return ($this->count * count($componentMgr->getCardsInSoloBoardIcon($this->conditionIcon)));
        } else {
            return ($this->count * $componentMgr->countPlayerIcon($this->playerId, $this->conditionIcon));
        }
    }
}

trait SoloTrait
{
    static private function soloName()
    {
        $nobleId = gameSoloNoble();
        $typeId = null;
        switch ($nobleId) {
            case GAME_OPTION_SOLO_NOBLE_VALUE_EASY_ARIANE:
                $typeId = 1200;
                break;
            case GAME_OPTION_SOLO_NOBLE_VALUE_NORMAL_CHARLES:
                $typeId = 1202;
                break;
            case GAME_OPTION_SOLO_NOBLE_VALUE_HARD_BLAZE:
                $typeId = 1201;
                break;
            case GAME_OPTION_SOLO_NOBLE_VALUE_HARD_JADE:
                $typeId = 1204;
                break;
            default:
                throw new \BgaSystemException("Unknown nobleId $nobleId for soloName");
        }
        return \GB\ComponentDefMgr::getByTypeId($typeId)->name;
    }
}
