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

namespace GB;

require_once(__DIR__ . '/../../BX/php/Action.php');
require_once('ComponentDefMgr.php');
require_once('EnemyMapDefMgr.php');

// location_id:
//    blue card: supply, market, player_deck, player_hand, player_play_area, player_development, draft_market, discard
//    red card: supply, market, player_deck, player_play_area, discard
//    village tile: supply, market
//    magic token: supply, player_board, supply_visible
//    monster tile: supply, market, player_board
const COMPONENT_LOCATION_ID_SUPPLY = 1;
const COMPONENT_LOCATION_ID_MARKET = 2;
const COMPONENT_LOCATION_ID_PLAYER_DECK = 3;
const COMPONENT_LOCATION_ID_PLAYER_HAND = 4;
const COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA = 5;
const COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT = 6;
const COMPONENT_LOCATION_ID_DRAFT_MARKET = 7;
const COMPONENT_LOCATION_ID_DISCARD = 8;
const COMPONENT_LOCATION_ID_PLAYER_BOARD = 9;
const COMPONENT_LOCATION_ID_SUPPLY_VISIBLE = 10;
const COMPONENT_LOCATION_ID_SCORE = 11;
const COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING = 12;
const COMPONENT_LOCATION_ID_SOLO_BOARD = 13;

class Component extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $componentId;
    /** @dbcol */
    public $typeId;
    /** @dbcol */
    public $playerId;
    /** @dbcol */
    public $locationId;
    /** @dbcol */
    public $locationPrimaryOrder;
    /** @dbcol */
    public $locationSecondaryOrder;
    /** @dbcol */
    public $isUsed;

    public function __construct()
    {
        $this->componentId = null;
        $this->typeId = null;
        $this->playerId = null;
        $this->locationId = null;
        $this->locationPrimaryOrder = null;
        $this->locationSecondaryOrder = null;
        $this->isUsed = false;
    }

    public function def()
    {
        return ComponentDefMgr::getByTypeId($this->typeId);
    }

    public function isVisibleForPlayer(int $playerId)
    {
        if ($this->locationId === null)
            throw new \BgaSystemException("BUG! locationId is null");
        switch ($this->locationId) {
            case COMPONENT_LOCATION_ID_SUPPLY:
                return false;
            case COMPONENT_LOCATION_ID_MARKET:
                return true;
            case COMPONENT_LOCATION_ID_PLAYER_DECK:
                return false;
            case COMPONENT_LOCATION_ID_PLAYER_HAND:
                if ($this->playerId == $playerId) {
                    return true;
                }
                return false;
            case COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA:
            case COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING:
                return true;
            case COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT:
                return false;
            case COMPONENT_LOCATION_ID_DRAFT_MARKET:
                return true;
            case COMPONENT_LOCATION_ID_DISCARD:
                return false;
            case COMPONENT_LOCATION_ID_PLAYER_BOARD:
                if ($this->playerId == $playerId || $this->def()->isEnemy()) {
                    return true;
                }
                return false;
            case COMPONENT_LOCATION_ID_SUPPLY_VISIBLE:
                return true;
            case COMPONENT_LOCATION_ID_SOLO_BOARD:
                return true;
            default:
                throw new \BgaSystemException("BUG! Unknown locationId: {$this->locationId}");
        }
    }

    public function isInPlayerBlueDeck(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_DECK
            && $this->def()->isCardBlue()
        );
    }

    public function isInPlayerRedDeck(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_DECK
            && $this->def()->isCardRed()
        );
    }

    public function isInAnyPlayerRedDeck()
    {
        return ($this->playerId !== null
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_DECK
            && $this->def()->isCardRed()
        );
    }

    public function isInPlayerHand(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_HAND
        );
    }

    public function isInPlayerBluePlayArea(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA
            && $this->def()->isCardBlue()
        );
    }

    public function isInPlayerRedPlayArea(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA
            && $this->def()->isCardRed()
        );
    }

    public function isInPlayerBuildingPlayArea(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING
        );
    }

    public function isInDraftMarket()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_DRAFT_MARKET);
    }

    public function isInPlayerDevelopmentNugget(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT
            && $this->locationPrimaryOrder == 0
        );
    }

    public function isInPlayerDevelopmentMaterial(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT
            && $this->locationPrimaryOrder == 1
        );
    }

    public function isEnemyOnPlayerBoard(?int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_BOARD
            && $this->def()->isEnemy()
        );
    }

    public function isCardInBlueMarket()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_MARKET
            && $this->def()->isCardBlue()
        );
    }

    public function isCardInBlueDeck()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_SUPPLY
            && $this->def()->isCardBlue()
        );
    }

    public function isCardInRedMarket()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_MARKET
            && $this->def()->isCardRed()
        );
    }

    public function isCardInRedDeck(int $side)
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_SUPPLY
            && $this->locationPrimaryOrder == $side
            && $this->def()->isCardRed()
        );
    }

    public function isBlueCardForPlayer(int $playerId)
    {
        return ($this->playerId == $playerId
            && ($this->locationId == COMPONENT_LOCATION_ID_PLAYER_DECK
                || $this->locationId == COMPONENT_LOCATION_ID_PLAYER_HAND
                || $this->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA
                || $this->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING
                || $this->locationId == COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT
            )
            && $this->def()->isCardBlue()
        );
    }

    public function isInSoloBoardIcon(int $icon)
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_SOLO_BOARD
            && $this->locationPrimaryOrder == $icon
        );
    }

    public function isInDiscard()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_DISCARD);
    }

    public function isVisibleVillage()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_MARKET
            && $this->def()->isVillage()
        );
    }

    public function isPlayerMagic(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == COMPONENT_LOCATION_ID_PLAYER_BOARD
            && $this->def()->isMagic()
        );
    }

    public function isSupplyMagic()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_SUPPLY
            && $this->def()->isMagic()
        );
    }

    public function isEnemyInMarket()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_MARKET
            && $this->def()->isEnemy()
        );
    }

    public function isEnemyInSupply()
    {
        return ($this->locationId == COMPONENT_LOCATION_ID_SUPPLY
            && $this->def()->isEnemy()
        );
    }

    public function moveToPlayerDeck(int $playerId)
    {
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_DECK;
        $this->locationPrimaryOrder = 0;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToPlayerHand(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerNextHandPrimaryOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_HAND;
        $this->locationPrimaryOrder = $order;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToPlayerDevelopment(int $playerId, int $side)
    {
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT;
        $this->locationPrimaryOrder = $side;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToDiscard()
    {
        $this->playerId = null;
        $this->locationId = COMPONENT_LOCATION_ID_DISCARD;
        $this->locationPrimaryOrder = 0;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToBlueMarket(int $order)
    {
        $this->playerId = null;
        $this->locationId = COMPONENT_LOCATION_ID_MARKET;
        $this->locationPrimaryOrder = $order;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToRedMarket(int $side)
    {
        $this->playerId = null;
        $this->locationId = COMPONENT_LOCATION_ID_MARKET;
        $this->locationPrimaryOrder = $side;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToPlayerBluePlayArea(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerNextBluePlayAreaPrimaryOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA;
        $this->locationPrimaryOrder = $order;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToPlayerBuildingPlayArea(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerNextBuildingPlayAreaPrimaryOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING;
        $this->locationPrimaryOrder = $order;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToPlayerRedPlayArea(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerNextRedPlayAreaSecondaryOrder($playerId, 0);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA;
        $this->locationPrimaryOrder = 0;
        $this->locationSecondaryOrder = $order;
        $this->isUsed = false;
    }

    public function moveToPlayerRedSecondaryPlayArea(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerNextRedPlayAreaSecondaryOrder($playerId, 1);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA;
        $this->locationPrimaryOrder = 1;
        $this->locationSecondaryOrder = $order;
        $this->isUsed = true;
    }

    public function moveToSupplyVisible()
    {
        $this->playerId = null;
        $this->locationId = COMPONENT_LOCATION_ID_SUPPLY_VISIBLE;
        $this->locationPrimaryOrder = 0;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveMagicToPlayerBoard(int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerFirstMagicPlayerBoardPrimaryOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_BOARD;
        $this->locationPrimaryOrder = $order;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveEnemyToPlayerBoard(?int $playerId)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getPlayerNextEnemyPlayerBoardPrimaryOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = COMPONENT_LOCATION_ID_PLAYER_BOARD;
        $this->locationPrimaryOrder = $order;
        $this->locationSecondaryOrder = 0;
        $this->isUsed = false;
    }

    public function moveToSoloIcon(int $icon)
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $order = $componentMgr->getSoloBoardIconSecondaryOrder($icon);
        $this->playerId = null;
        $this->locationId = COMPONENT_LOCATION_ID_SOLO_BOARD;
        $this->locationPrimaryOrder = $icon;
        $this->locationSecondaryOrder = $order;
        $this->isUsed = false;
    }

    public function flipVillageToSupply()
    {
        $this->locationId = COMPONENT_LOCATION_ID_SUPPLY;
    }

    public function flipVillageToMarket()
    {
        $this->locationId = COMPONENT_LOCATION_ID_MARKET;
    }

    public function flipEnemyToMarket()
    {
        $this->locationId = COMPONENT_LOCATION_ID_MARKET;
    }
}

class ComponentCountsUI extends \BX\UI\UISerializable
{
    public $magicCount;
    public $cardBlueCount;
    public $cardRedCounts;
    public $enemyCounts;
    public $cardBluePlayerDeckCounts;
    public $cardRedPlayerDeckCounts;
    public $developmentCounts;
    public $handCounts;
    public $iconCounts;
    public $magicPlayerBoardCounts;
    public $combatPowers;
    public $enemyCombatPowers;
    public $cardBlueTypeList;
    public $cardRedTypeList;
    public $discardedTypeList;
    public $soloIconCounts;
    public $soloNuggetCount;
    public $soloMaterialCount;
    public $soloGoldCount;

    public function __construct(array $playerIdArray)
    {
        $this->magicCount = 0;
        $this->cardBlueCount = 0;
        $this->cardRedCounts = [0, 0];
        $this->enemyCounts = [];
        foreach (EnemyMapDefMgr::getAll() as $e) {
            $this->enemyCounts[$e->id] = 0;
        }
        $this->cardBluePlayerDeckCounts = [];
        $this->cardRedPlayerDeckCounts = [];
        $this->developmentCounts = [[], []];
        $this->handCounts = [];
        $this->iconCounts = [];
        $this->magicPlayerBoardCounts = [];
        $this->cardBlueTypeList = [];
        $this->cardRedTypeList = [];
        foreach ($playerIdArray as $playerId) {
            $this->cardBluePlayerDeckCounts[$playerId] = 0;
            $this->cardRedPlayerDeckCounts[$playerId] = 0;
            $this->developmentCounts[0][$playerId] = 0;
            $this->developmentCounts[1][$playerId] = 0;
            $this->handCounts[$playerId] = 0;
            $this->iconCounts[$playerId] = [];
            foreach (COMPONENT_ICON_IDS as $icon) {
                $this->iconCounts[$playerId][$icon] = 0;
            }
            $this->magicPlayerBoardCounts[$playerId] = 0;
            $this->cardBlueTypeList[$playerId] = [];
            $this->cardRedTypeList[$playerId] = [];
        }
        $this->discardedTypeList = [];
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $this->combatPowers = [];
        $this->enemyCombatPowers = [];
        foreach ($playerIdArray as $playerId) {
            $this->combatPowers[$playerId] = $playerStateMgr->getPlayerCombatPower($playerId);
            $this->enemyCombatPowers[$playerId] = $playerStateMgr->getPlayerEnemyCombatPower($playerId);
        }
        $this->soloIconCounts = [];
        foreach (COMPONENT_ICON_IDS as $icon) {
            $this->soloIconCounts[$icon] = 0;
        }
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $this->soloNuggetCount = $gameStateMgr->getSoloNuggetCount();
        $this->soloMaterialCount = $gameStateMgr->getSoloMaterialCount();
        $this->soloGoldCount = $gameStateMgr->getSoloGoldCount();
    }

    public function addComponent(Component $c)
    {
        switch ($c->def()->categoryId) {
            case COMPONENT_CATEGORY_ID_CARD_BLUE:
                if ($c->playerId !== null) {
                    $this->cardBlueTypeList[$c->playerId][] = $c->typeId;
                }
                if ($c->isInDiscard()) {
                    $this->discardedTypeList[] = $c->typeId;
                }
                if ($c->locationId == COMPONENT_LOCATION_ID_SUPPLY) {
                    ++$this->cardBlueCount;
                } else if ($c->locationId == COMPONENT_LOCATION_ID_PLAYER_DECK) {
                    ++$this->cardBluePlayerDeckCounts[$c->playerId];
                } else if ($c->locationId == COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT) {
                    ++$this->developmentCounts[$c->locationPrimaryOrder][$c->playerId];
                } else if ($c->locationId == COMPONENT_LOCATION_ID_PLAYER_HAND) {
                    ++$this->handCounts[$c->playerId];
                } else if ($c->locationId == COMPONENT_LOCATION_ID_SOLO_BOARD) {
                    $this->soloIconCounts[$c->locationPrimaryOrder] += $c->def()->countIcon($c->locationPrimaryOrder);
                } else if (
                    $c->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA
                    || $c->locationId == COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING
                ) {
                    foreach (COMPONENT_ICON_IDS as $icon) {
                        $this->iconCounts[$c->playerId][$icon] += $c->def()->countIcon($icon);
                    }
                }
                break;
            case COMPONENT_CATEGORY_ID_CARD_RED:
                if ($c->playerId !== null) {
                    $this->cardRedTypeList[$c->playerId][] = $c->typeId;
                }
                if ($c->isInDiscard()) {
                    $this->discardedTypeList[] = $c->typeId;
                }
                if ($c->locationId == COMPONENT_LOCATION_ID_SUPPLY || $c->locationId == COMPONENT_LOCATION_ID_MARKET) {
                    ++$this->cardRedCounts[$c->locationPrimaryOrder];
                } else if ($c->locationId == COMPONENT_LOCATION_ID_PLAYER_DECK) {
                    ++$this->cardRedPlayerDeckCounts[$c->playerId];
                }
                break;
            case COMPONENT_CATEGORY_ID_VILLAGE:
                break;
            case COMPONENT_CATEGORY_ID_MAGIC:
                if ($c->locationId == COMPONENT_LOCATION_ID_SUPPLY) {
                    ++$this->magicCount;
                } else if ($c->locationId == COMPONENT_LOCATION_ID_PLAYER_BOARD) {
                    ++$this->magicPlayerBoardCounts[$c->playerId];
                }
                break;
            case COMPONENT_CATEGORY_ID_ENEMY:
                if (
                    $c->locationId == COMPONENT_LOCATION_ID_SUPPLY
                    && $c->def()->subCategoryId != COMPONENT_SUB_CATEGORY_ID_PERMANENT
                ) {
                    ++$this->enemyCounts[$c->locationPrimaryOrder];
                } else if ($c->locationId == COMPONENT_LOCATION_ID_PLAYER_BOARD) {
                    if ($c->playerId === null) {
                        ++$this->soloIconCounts[COMPONENT_ICON_ID_ENEMY];
                    } else {
                        ++$this->iconCounts[$c->playerId][COMPONENT_ICON_ID_ENEMY];
                    }
                }
                break;
        }
    }
}

class ComponentMgr extends \BX\Action\BaseActionRowMgr
{
    private $setupNextComponentId;

    public function __construct()
    {
        parent::__construct('component', \GB\Component::class);
    }

    public function setup(array $playerIdArray)
    {
        $this->setupNextComponentId = 0;
        $this->setupCardBlueNoble($playerIdArray);
        $this->setupCardStarting($playerIdArray, ComponentDefMgr::getAllCardBlue());
        $this->setupCardBlueDeck();

        $this->setupCardStarting($playerIdArray, ComponentDefMgr::getAllCardRed());
        $this->setupCardRedDecks();

        $this->setupVillage();

        $this->setupMagic();

        $this->setupEnemy(count($playerIdArray));

        foreach ($playerIdArray as $playerId) {
            $this->shufflePlayerBlueDeckNow($playerId);
            $this->shufflePlayerRedDeckNow($playerId);
        }
        $this->shuffleMagicNow();
    }

    private function setupCardBlueNoble(array $playerIdArray)
    {
        $nobles = array_filter(ComponentDefMgr::getAllCardBlue(), fn ($c) => $c->subCategoryId == COMPONENT_SUB_CATEGORY_ID_NOBLE);
        shuffle($nobles);
        $nobleCount = count($playerIdArray);
        if (
            $nobleCount == 1
            && \BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_SOLO_STARTING_NOBLE_ID) == GAME_OPTION_SOLO_STARTING_NOBLE_VALUE_ALL
        ) {
            $nobleCount = count($nobles);
        }

        for ($i = 0; $i < $nobleCount; ++$i) {
            $def = array_shift($nobles);
            $c = $this->db->newRow();
            $c->componentId = $this->setupNextComponentId++;
            $c->typeId = $def->typeId;
            $c->locationId = COMPONENT_LOCATION_ID_DRAFT_MARKET;
            $c->locationPrimaryOrder = $def->typeId;
            $c->locationSecondaryOrder = 0;
            $this->db->insertRow($c);
        }
    }

    private function setupCardStarting(array $playerIdArray, array $allColorCards)
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $colors = array_flip(COMPONENT_SUB_CATEGORY_IDS_PLAYER_COLORS);
        foreach ($playerIdArray as $playerId) {
            $playerColorId = $playerMgr->getByPlayerId($playerId)->playerColorId();
            $starting = array_filter($allColorCards, fn ($c) => $c->subCategoryId == $colors[$playerColorId]);
            foreach ($starting as $def) {
                for ($i = 0; $i < $def->setupCount; ++$i) {
                    $c = $this->db->newRow();
                    $c->componentId = $this->setupNextComponentId++;
                    $c->typeId = $def->typeId;
                    $c->playerId = $playerId;
                    $c->locationId = COMPONENT_LOCATION_ID_PLAYER_DECK;
                    $c->locationPrimaryOrder = 0;
                    $c->locationSecondaryOrder = 0;
                    $this->db->insertRow($c);
                }
            }
        }
    }

    private function setupCardBlueDeck()
    {
        $deck = array_filter(ComponentDefMgr::getAllCardBlue(), fn ($c) => $c->subCategoryId == COMPONENT_SUB_CATEGORY_ID_DECK);
        $allBlueCards = [];
        foreach ($deck as $def) {
            for ($i = 0; $i < $def->setupCount; ++$i) {
                $c = $this->db->newRow();
                $c->componentId = $this->setupNextComponentId++;
                $c->typeId = $def->typeId;
                $c->locationId = COMPONENT_LOCATION_ID_SUPPLY;
                $c->locationSecondaryOrder = 0;
                $allBlueCards[] = $c;
            }
        }
        shuffle($allBlueCards);
        $humanoidCards = array_filter($allBlueCards, fn ($c) => !$c->def()->isBuilding());
        $count = 0;
        foreach ($humanoidCards as $i => $c) {
            $c->locationId = COMPONENT_LOCATION_ID_MARKET;
            $c->locationPrimaryOrder = $count++;
            $this->db->insertRow($c);
            unset($allBlueCards[$i]);
            if ($count == BLUE_CARD_MARKET_SIZE) {
                break;
            }
        }
        $order = 0;
        foreach ($allBlueCards as $c) {
            $c->locationPrimaryOrder = $order++;
            $this->db->insertRow($c);
        }
    }

    private function setupCardRedDecks()
    {
        $deck = array_filter(ComponentDefMgr::getAllCardRed(), fn ($c) => $c->subCategoryId == COMPONENT_SUB_CATEGORY_ID_DECK);
        $allRedCards = [];
        foreach ($deck as $def) {
            for ($i = 0; $i < $def->setupCount; ++$i) {
                $c = $this->db->newRow();
                $c->componentId = $this->setupNextComponentId++;
                $c->typeId = $def->typeId;
                $c->locationId = COMPONENT_LOCATION_ID_SUPPLY;
                $allRedCards[] = $c;
            }
        }
        shuffle($allRedCards);
        $side = 0;
        $order = 0;
        foreach ($allRedCards as $c) {
            if ($order == 0) {
                $c->locationId = COMPONENT_LOCATION_ID_MARKET;
            }
            $c->locationPrimaryOrder = $side;
            $c->locationSecondaryOrder = $order;
            $this->db->insertRow($c);
            ++$side;
            if ($side > 1) {
                $side = 0;
                ++$order;
            }
        }
    }

    private function setupVillage()
    {
        $setup = function (int $subCategory, int $order) {
            $village = array_filter(ComponentDefMgr::getAllVillage(), fn ($c) => $c->subCategoryId == $subCategory);
            shuffle($village);
            foreach ($village as $i => $def) {
                $c = $this->db->newRow();
                $c->componentId = $this->setupNextComponentId++;
                $c->typeId = $def->typeId;
                $c->locationId = ($i == 0 ? COMPONENT_LOCATION_ID_SUPPLY : COMPONENT_LOCATION_ID_MARKET);
                $c->locationPrimaryOrder = $order;
                $c->locationSecondaryOrder = 0;
                $this->db->insertRow($c);
            }
        };
        $setup(COMPONENT_SUB_CATEGORY_ID_LEFT, 0);
        $setup(COMPONENT_SUB_CATEGORY_ID_RIGHT, 1);
    }

    private function setupMagic()
    {
        $magic = ComponentDefMgr::getAllMagic();
        foreach ($magic as $def) {
            $c = $this->db->newRow();
            $c->componentId = $this->setupNextComponentId++;
            $c->typeId = $def->typeId;
            $c->locationId = COMPONENT_LOCATION_ID_SUPPLY;
            $c->locationPrimaryOrder = 0;
            $c->locationSecondaryOrder = 0;
            $this->db->insertRow($c);
        }
    }

    private function setupEnemy(int $playerCount)
    {
        $getEnemies = function (int $subCategory) {
            $enemy = array_filter(ComponentDefMgr::getAllEnemy(), fn ($c) => $c->subCategoryId == $subCategory);
            $components = [];
            foreach ($enemy as $def) {
                for ($i = 0; $i < $def->setupCount; ++$i) {
                    $c = $this->db->newRow();
                    $c->componentId = $this->setupNextComponentId++;
                    $c->typeId = $def->typeId;
                    $c->locationId = COMPONENT_LOCATION_ID_SUPPLY;
                    $c->locationPrimaryOrder = 0;
                    $c->locationSecondaryOrder = 0;
                    $components[] = $c;
                }
            }
            shuffle($components);
            return $components;
        };
        $placeEnemies = function (array $hexes, array $components) use ($playerCount) {
            foreach ($hexes as $hex) {
                $order = 0;
                $c = array_shift($components);
                $c->locationPrimaryOrder = $hex->id;
                $c->locationSecondaryOrder = $order++;
                $this->db->insertRow($c);
                if ($playerCount == MAX_PLAYERS && $hex->isDouble) {
                    $c = array_shift($components);
                    $c->locationPrimaryOrder = $hex->id;
                    $c->locationSecondaryOrder = $order++;
                    $this->db->insertRow($c);
                }
                if ($hex->isPermanent) {
                    $enemy = array_values(
                        array_filter(ComponentDefMgr::getAllEnemy(), fn ($c) => $c->subCategoryId == COMPONENT_SUB_CATEGORY_ID_PERMANENT)
                    )[0];
                    $c = $this->db->newRow();
                    $c->componentId = $this->setupNextComponentId++;
                    $c->typeId = $enemy->typeId;
                    $c->locationId = COMPONENT_LOCATION_ID_SUPPLY;
                    $c->locationPrimaryOrder = $hex->id;
                    $c->locationSecondaryOrder = $order++;
                    $this->db->insertRow($c);
                }
            }
        };
        $placeEnemies(EnemyMapDefMgr::getAllForest(), $getEnemies(COMPONENT_SUB_CATEGORY_ID_FOREST));
        $placeEnemies(EnemyMapDefMgr::getAllMountain(), $getEnemies(COMPONENT_SUB_CATEGORY_ID_MOUNTAIN));
    }

    public function getById(int $componentId)
    {
        return $this->getRowByKey($componentId);
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getAllVisibleForPlayer(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isVisibleForPlayer($playerId));
    }

    public function getAllCounts()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $counts = new ComponentCountsUI($playerMgr->getAllPlayerIds());
        foreach ($this->getAll() as $c) {
            $counts->addComponent($c);
        }
        return $counts;
    }

    public function getComponentIdToTypeId()
    {
        return array_map(fn ($c) => $c->typeId, $this->getAll());
    }

    public function getPlayerBlueDeck(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerBlueDeck($playerId));
    }

    public function getPlayerRedDeck(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerRedDeck($playerId));
    }

    public function getPlayerIdsWithCardsInRedDeck(int $excludePlayerId)
    {
        $playerIds = [];
        foreach ($this->getAll() as $c) {
            if ($c->isInAnyPlayerRedDeck() && $c->playerId != $excludePlayerId) {
                $playerIds[$c->playerId] = true;
            }
        }
        return array_keys($playerIds);
    }

    public function shufflePlayerBlueDeckNow(int $playerId)
    {
        $deck = $this->getPlayerBlueDeck($playerId);
        shuffle($deck);
        foreach ($deck as $i => $c) {
            $c->locationPrimaryOrder = $i;
            $this->db->updateRow($c);
        }
    }

    public function sufflePlayerBlueDeckAction(int $playerId)
    {
        $deck = $this->getPlayerBlueDeck($playerId);
        if (count($deck) <= 1) {
            return false;
        }
        shuffle($deck);
        foreach ($deck as $i => $c) {
            $c->modifyAction();
            $c->locationPrimaryOrder = $i;
        }
        return true;
    }

    public function shufflePlayerRedDeckNow(int $playerId)
    {
        $deck = $this->getPlayerRedDeck($playerId);
        shuffle($deck);
        foreach ($deck as $i => $c) {
            $c->locationPrimaryOrder = $i;
            $this->db->updateRow($c);
        }
    }

    public function sufflePlayerRedDeckAction(int $playerId)
    {
        $deck = $this->getPlayerRedDeck($playerId);
        if (count($deck) <= 1) {
            return false;
        }
        shuffle($deck);
        foreach ($deck as $i => $c) {
            $c->modifyAction();
            $c->locationPrimaryOrder = $i;
        }
        return true;
    }

    public function getAllMagic()
    {
        return array_filter($this->getAll(), fn ($c) => $c->def()->isMagic());
    }

    public function shuffleMagicNow()
    {
        $magic = array_filter($this->getAllMagic(), fn ($c) => $c->playerId === null);
        $retMagic = [];
        foreach ($magic as $i => $c) {
            if ($c->locationId == COMPONENT_LOCATION_ID_SUPPLY_VISIBLE) {
                $retMagic[] = $c;
            }
            $c->locationId = COMPONENT_LOCATION_ID_SUPPLY;
            $c->locationPrimaryOrder = 0;
        }
        $retMagic = \BX\Meta\deepClone($retMagic);
        shuffle($magic);
        foreach ($magic as $i => $c) {
            $c->locationPrimaryOrder = $i;
            $this->db->updateRow($c);
        }
        return $retMagic;
    }

    public function getPlayerMagic(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isPlayerMagic($playerId));
    }

    public function getPlayerFirstMagicPlayerBoardPrimaryOrder(int $playerId)
    {
        $orders = [
            0 => 0,
            1 => 1,
            2 => 2,
        ];
        foreach ($this->getPlayerMagic($playerId) as $c) {
            unset($orders[$c->locationPrimaryOrder]);
        }
        if (count($orders) == 0) {
            throw new \BgaSystemException("BUG! All magic token order are taken for player $playerId");
        }
        return array_keys($orders)[0];
    }

    public function getNoblesInDraftMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInDraftMarket());
    }

    public function getCardsInPlayerHand(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerHand($playerId));
    }

    public function getTopCardFromBluePlayerDeck(int $playerId)
    {
        $topCard = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isInPlayerBlueDeck($playerId)) {
                continue;
            }
            if ($topCard === null || $c->locationPrimaryOrder < $topCard->locationPrimaryOrder) {
                $topCard = $c;
            }
        }
        return $topCard;
    }

    public function getTopCardFromRedPlayerDeck(int $playerId)
    {
        $topCard = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isInPlayerRedDeck($playerId)) {
                continue;
            }
            if ($topCard === null || $c->locationPrimaryOrder < $topCard->locationPrimaryOrder) {
                $topCard = $c;
            }
        }
        return $topCard;
    }

    public function getPlayerNextHandPrimaryOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getCardsInPlayerHand($playerId) as $c) {
            if ($c->locationPrimaryOrder > $max) {
                $max = $c->locationPrimaryOrder;
            }
        }
        return ($max + 1);
    }

    public function getCardsInPlayerRedPlayArea(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerRedPlayArea($playerId));
    }

    public function playerUnusedCombatCard(int $playerId)
    {
        return array_filter($this->getCardsInPlayerRedPlayArea($playerId), fn ($c) => $c->locationPrimaryOrder == 0 && !$c->isUsed);
    }

    public function playerPlayedCombatCard(int $playerId)
    {
        return array_filter($this->getCardsInPlayerRedPlayArea($playerId), fn ($c) => $c->locationPrimaryOrder == 0);
    }

    public function getPlayerCombatCardToReactivate(int $playerId)
    {
        return array_filter($this->playerPlayedCombatCard($playerId), fn ($c) => !$c->def()->hasReactivateAbility());
    }

    public function getCardsInPlayerBluePlayArea(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerBluePlayArea($playerId));
    }

    public function getCardsInPlayerBluePlayAreaForReactivation(int $playerId, int $conditionIcon, int $excludeComponentId)
    {
        return array_filter(
            $this->getAll(),
            fn ($c) => $c->componentId != $excludeComponentId
                && $c->isInPlayerBluePlayArea($playerId)
                && $c->def()->hasIcon($conditionIcon)
        );
    }

    public function getPlayerNextBluePlayAreaPrimaryOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getCardsInPlayerBluePlayArea($playerId) as $c) {
            if ($c->locationPrimaryOrder > $max) {
                $max = $c->locationPrimaryOrder;
            }
        }
        return ($max + 1);
    }


    public function getPlayerNextEnemyPlayerBoardPrimaryOrder(?int $playerId)
    {
        $max = -1;
        foreach ($this->getPlayerEnemy($playerId) as $c) {
            if ($c->locationPrimaryOrder > $max) {
                $max = $c->locationPrimaryOrder;
            }
        }
        return ($max + 1);
    }

    public function getCardsInSoloBoardIcon(int $icon)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInSoloBoardIcon($icon));
    }

    public function getSoloBoardIconSecondaryOrder(int $icon)
    {
        $max = -1;
        foreach ($this->getCardsInSoloBoardIcon($icon) as $c) {
            if ($c->locationSecondaryOrder > $max) {
                $max = $c->locationSecondaryOrder;
            }
        }
        return ($max + 1);
    }

    public function getCardsInPlayerBuildingPlayArea(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerBuildingPlayArea($playerId));
    }

    public function getUsedCardsInPlayerBuildingPlayArea(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerBuildingPlayArea($playerId) && $c->isUsed);
    }

    public function getPlayerNextBuildingPlayAreaPrimaryOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getCardsInPlayerBuildingPlayArea($playerId) as $c) {
            if ($c->locationPrimaryOrder > $max) {
                $max = $c->locationPrimaryOrder;
            }
        }
        return ($max + 1);
    }

    public function getPlayerNextRedPlayAreaSecondaryOrder(int $playerId, $primaryOrder)
    {
        $max = -1;
        foreach ($this->getCardsInPlayerRedPlayArea($playerId) as $c) {
            if ($c->locationPrimaryOrder != $primaryOrder) {
                continue;
            }
            if ($c->locationSecondaryOrder > $max) {
                $max = $c->locationSecondaryOrder;
            }
        }
        return ($max + 1);
    }

    public function getPlayerCardDevelopmentNugget(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerDevelopmentNugget($playerId));
    }

    public function getPlayerCardDevelopmentMaterial(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerDevelopmentMaterial($playerId));
    }

    public function getPlayerDevelopmentTypeId(int $playerId)
    {
        return array_values(array_map(
            fn ($c) => $c->typeId,
            $this->getPlayerCardDevelopmentNugget($playerId) + $this->getPlayerCardDevelopmentMaterial($playerId)
        ));
    }

    public function getPlayerEnemy(?int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isEnemyOnPlayerBoard($playerId));
    }

    public function getCardInBlueMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isCardInBlueMarket());
    }

    public function getHumanoidCardInBlueMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isCardInBlueMarket() && $c->def()->isHumanoid());
    }

    public function getCardInRedMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isCardInRedMarket());
    }

    public function getCardInAllMarkets()
    {
        return $this->getCardInBlueMarket() + $this->getCardInRedMarket();
    }

    public function getVisibleVillages()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isVisibleVillage());
    }

    public function atLeastOneRedDeckIsEmpty()
    {
        return ($this->specificRedDeckIsEmpty(0)
            || $this->specificRedDeckIsEmpty(1)
        );
    }

    public function specificRedDeckIsEmpty(int $side)
    {
        return ($this->getTopCardFromRedDeck($side) === null
            && count(array_filter($this->getCardInRedMarket(), fn ($c) => $c->locationPrimaryOrder == $side)) == 0

        );
    }

    public function getTopCardFromBlueDeck()
    {
        $topCard = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isCardInBlueDeck()) {
                continue;
            }
            if ($topCard === null || $c->locationPrimaryOrder < $topCard->locationPrimaryOrder) {
                $topCard = $c;
            }
        }
        return $topCard;
    }

    public function getTopCardFromRedDeck(int $side)
    {
        $topCard = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isCardInRedDeck($side)) {
                continue;
            }
            if ($topCard === null || $c->locationSecondaryOrder < $topCard->locationSecondaryOrder) {
                $topCard = $c;
            }
        }
        return $topCard;
    }

    public function getTopMagicFromSupply()
    {
        $top = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isSupplyMagic()) {
                continue;
            }
            if ($top === null || $c->locationPrimaryOrder < $top->locationPrimaryOrder) {
                $top = $c;
            }
        }
        return $top;
    }

    public function otherSideVillage(int $componentId)
    {
        $up = $this->getById($componentId);
        if (!$up->def()->isVillage()) {
            throw new \BgaSystemException("BUG! componentId $componentId is not a village");
        }
        foreach ($this->getAll() as $c) {
            if (
                $c->locationPrimaryOrder == $up->locationPrimaryOrder
                && $c->locationId != $up->locationId
                && $c->def()->isVillage()
            ) {
                return $c;
            }
        }
        throw new \BgaSystemException("BUG! Cannot find other side of componentId $componentId");
    }

    public function movePlayAreaBlueCardsToDeckNow(int $playerId)
    {
        $ret = [];
        foreach ($this->getCardsInPlayerBluePlayArea($playerId) as $c) {
            $c->moveToPlayerDeck($playerId);
            $this->db->updateRow($c);
            $ret[] = $c;
        }
        return $ret;
    }

    public function moveHandCardsToDeckNow(int $playerId)
    {
        $ret = [];
        foreach ($this->getCardsInPlayerHand($playerId) as $c) {
            $c->moveToPlayerDeck($playerId);
            $this->db->updateRow($c);
            $ret[] = $c;
        }
        return $ret;
    }

    public function movePlayAreaRedCardsToDeckNow(int $playerId)
    {
        $ret = [];
        foreach ($this->getCardsInPlayerRedPlayArea($playerId) as $c) {
            $c->moveToPlayerDeck($playerId);
            $this->db->updateRow($c);
            $ret[] = $c;
        }
        return $ret;
    }

    public function activateBuildingsNow(int $playerId)
    {
        $ret = [];
        foreach ($this->getCardsInPlayerBuildingPlayArea($playerId) as $c) {
            $c->isUsed = false;
            $this->db->updateRow($c);
            $ret[] = $c;
        }
        return $ret;
    }

    public function countPlayerIcon(int $playerId, int $conditionIcon)
    {
        $counts = $this->getAllCounts();
        return $counts->iconCounts[$playerId][$conditionIcon];
    }

    public function getAllEnemiesInMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isEnemyInMarket());
    }

    public function getAllEnemiesInSupply()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isEnemyInSupply());
    }

    public function getAllAccessibleHiddenEnemyLocations(bool $mustBeAccessible = true)
    {
        $visibleLocations = array_flip(array_map(fn ($c) => $c->locationPrimaryOrder, $this->getAllEnemiesInMarket()));
        $fullLocations = array_flip(array_map(fn ($c) => $c->locationPrimaryOrder, $this->getAllEnemiesInMarket() + $this->getAllEnemiesInSupply()));

        $locations = array_map(fn ($h) => $h->id, EnemyMapDefMgr::getAll());
        foreach ($locations as $i => $id) {
            $hex = EnemyMapDefMgr::getById($id);
            if (array_key_exists($id, $visibleLocations)) {
                unset($locations[$i]);
                continue;
            }
            if (!array_key_exists($id, $fullLocations)) {
                unset($locations[$i]);
                continue;
            }
            if ($hex->isAlwaysAccessible) {
                continue;
            }
            if ($mustBeAccessible) {
                $allFullLocations = true;
                foreach ($hex->neighborIds as $neighborId) {
                    if (!array_key_exists($neighborId, $fullLocations)) {
                        $allFullLocations = false;
                        break;
                    }
                }
                if ($allFullLocations) {
                    unset($locations[$i]);
                }
            }
        }
        return array_values($locations);
    }

    public function getAllAccessibleHiddenEnemyLocationsForSoloMode()
    {
        $normalLocations = $this->getAllAccessibleHiddenEnemyLocations();
        if (count($normalLocations) > 0) {
            return $normalLocations;
        }
        return $this->getAllAccessibleHiddenEnemyLocations(false);
    }

    public function getEnemyLocationCombatDraw(int $location)
    {
        $fullLocations = array_flip(array_map(fn ($c) => $c->locationPrimaryOrder, $this->getAllEnemiesInMarket() + $this->getAllEnemiesInSupply()));

        $hex = EnemyMapDefMgr::getById($location);
        $combatDraw = $hex->baseDrawCount;
        foreach ($hex->neighborIds as $neighborId) {
            if (!array_key_exists($neighborId, $fullLocations)) {
                $combatDraw += 1;
            }
        }
        return $combatDraw;
    }

    public function getTopEnemyFromLocationId(int $locationId)
    {
        $topEnemy = null;
        foreach ($this->getAllEnemiesInMarket() + $this->getAllEnemiesInSupply() as $c) {
            if ($c->locationPrimaryOrder != $locationId) {
                continue;
            }
            if ($topEnemy === null || $c->locationSecondaryOrder < $topEnemy->locationSecondaryOrder) {
                $topEnemy = $c;
            }
        }
        return $topEnemy;
    }

    public function countAllPlayerIcons(int $playerId, $icon)
    {
        $count = 0;
        foreach ($this->getAll() as $c) {
            if (!$c->isBlueCardForPlayer($playerId)) {
                continue;
            }
            $count += $c->def()->countIcon($icon);
        }
        return $count;
    }

    public function debugGetAllRedCardTypes(int $playerId)
    {
        $cards = [];
        foreach ($this->getAll() as $c) {
            if (!$c->isCardInRedDeck(0) && !$c->isCardInRedDeck(1)) {
                continue;
            }
            if (array_key_exists($c->typeId, $cards)) {
                continue;
            }
            $cards[$c->typeId] = $c;
        }
        foreach ($cards as $c) {
            $c->moveToPlayerDeck($playerId);
            $this->db->updateRow($c);
        }
        $this->shufflePlayerRedDeckNow($playerId);
    }

    public function debugMoveBlueCardToHand(int $playerId)
    {
        foreach ($this->getCardsInPlayerBluePlayArea($playerId) as $c) {
            $c->moveToPlayerHand($playerId);
            $this->db->updateRow($c);
        }
    }

    public function debugGetBlueCard(int $playerId, int $typeId)
    {
        $card = null;
        foreach ($this->getPlayerBlueDeck($playerId) as $c) {
            if ($c->typeId == $typeId) {
                $c->moveToPlayerHand($playerId);
                $this->db->updateRow($c);
                return;
            }
        }
        foreach ($this->getAll() as $c) {
            if ($c->isCardInBlueDeck() && $c->typeId == $typeId) {
                $c->moveToPlayerHand($playerId);
                $this->db->updateRow($c);
                return;
            }
        }
        foreach ($this->getCardsInPlayerBluePlayArea($playerId) as $c) {
            if ($c->typeId == $typeId) {
                $c->moveToPlayerHand($playerId);
                $this->db->updateRow($c);
                return;
            }
        }
    }
}
