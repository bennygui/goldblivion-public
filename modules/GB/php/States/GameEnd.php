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

namespace GB\State\GameEnd;

require_once(__DIR__ . '/../../../BX/php/Action.php');

trait GameStatesTrait
{
    public function stPreGameEnd()
    {
        $this->preGameEnd();
        $this->gamestate->nextState();
    }

    public function preGameEnd()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');

        foreach ($playerMgr->getAll() as $p) {
            $this->setStat(
                $p->playerScore,
                STATS_PLAYER_GOLD_AT_END,
                $p->playerId
            );

            $ps = $playerStateMgr->getByPlayerId($p->playerId);
            $this->setStat(
                $ps->nuggetCount,
                STATS_PLAYER_NUGGET_AT_END,
                $p->playerId
            );
            $this->setStat(
                $ps->materialCount,
                STATS_PLAYER_MATERIAL_AT_END,
                $p->playerId
            );
            $this->setStat(
                count($componentMgr->getPlayerEnemy($p->playerId)),
                STATS_PLAYER_ENEMY_TILE_AT_END,
                $p->playerId
            );
            $this->setStat(
                count($componentMgr->getPlayerCardDevelopmentNugget($p->playerId)),
                STATS_PLAYER_CARDS_IN_NUGGET_DEVELOPMENT_AT_END,
                $p->playerId
            );
            $this->setStat(
                count($componentMgr->getPlayerCardDevelopmentMaterial($p->playerId)),
                STATS_PLAYER_CARDS_IN_MATERIAL_DEVELOPMENT_AT_END,
                $p->playerId
            );
            $this->setStat(
                $ps->statGainedNugget,
                STATS_PLAYER_GAINED_NUGGET,
                $p->playerId
            );
            $this->setStat(
                $ps->statGainedMaterial,
                STATS_PLAYER_GAINED_MATERIAL,
                $p->playerId
            );
            $this->setStat(
                $ps->statGainedGoldFromNugget,
                STATS_PLAYER_GAINED_GOLD_FROM_NUGGET,
                $p->playerId
            );
            $this->setStat(
                $ps->statGainedBlueCard,
                STATS_PLAYER_GAINED_BLUE_CARD,
                $p->playerId
            );
            $this->setStat(
                $ps->statGainedRedCard,
                STATS_PLAYER_GAINED_RED_CARD,
                $p->playerId
            );
            $this->setStat(
                $ps->statGainedMagicToken,
                STATS_PLAYER_GAINED_MAGIC_TOKEN,
                $p->playerId
            );
            $this->setStat(
                $ps->statCombatLost,
                STATS_PLAYER_COMBAT_LOST,
                $p->playerId
            );
            $this->setStat(
                $ps->statCombatWon,
                STATS_PLAYER_COMBAT_WON,
                $p->playerId
            );
            $this->setStat(
                $ps->statCombatWonCow,
                STATS_PLAYER_COMBAT_WON_COW,
                $p->playerId
            );
            $this->setStat(
                $ps->statDestroyedBlueMarket,
                STATS_PLAYER_DESTROYED_BLUE_MARKET,
                $p->playerId
            );
            $this->setStat(
                $ps->statDestroyedRedMarket,
                STATS_PLAYER_DESTROYED_RED_MARKET,
                $p->playerId
            );
            $this->setStat(
                $componentMgr->countAllPlayerIcons($p->playerId, \GB\COMPONENT_ICON_ID_HUMAN),
                STATS_PLAYER_ICONS_HUMAN,
                $p->playerId
            );
            $this->setStat(
                $componentMgr->countAllPlayerIcons($p->playerId, \GB\COMPONENT_ICON_ID_ELF),
                STATS_PLAYER_ICONS_ELF,
                $p->playerId
            );
            $this->setStat(
                $componentMgr->countAllPlayerIcons($p->playerId, \GB\COMPONENT_ICON_ID_DWARF),
                STATS_PLAYER_ICONS_DWARF,
                $p->playerId
            );
            $this->setStat(
                $componentMgr->countAllPlayerIcons($p->playerId, \GB\COMPONENT_ICON_ID_BUILDING),
                STATS_PLAYER_ICONS_BUILDING,
                $p->playerId
            );
        }

        if (isGameSolo()) {
            $playerId = $this->getActivePlayerId();

            $this->setStat(
                $gameStateMgr->getSoloGoldCount(),
                STATS_PLAYER_SOLO_GOLD_AT_END,
                $playerId
            );

            $ps = $playerStateMgr->getByPlayerId($p->playerId);
            $this->setStat(
                $gameStateMgr->getSoloNuggetCount(),
                STATS_PLAYER_SOLO_NUGGET_AT_END,
                $playerId
            );
            $this->setStat(
                $gameStateMgr->getSoloMaterialCount(),
                STATS_PLAYER_SOLO_MATERIAL_AT_END,
                $playerId
            );
            $this->setStat(
                count($componentMgr->getPlayerEnemy(null)),
                STATS_PLAYER_SOLO_ENEMY_TILE_AT_END,
                $playerId
            );
            $this->setStat(
                count($componentMgr->getCardsInSoloBoardIcon(\GB\COMPONENT_ICON_ID_HUMAN)),
                STATS_PLAYER_SOLO_CARDS_HUMAN,
                $playerId
            );
            $this->setStat(
                count($componentMgr->getCardsInSoloBoardIcon(\GB\COMPONENT_ICON_ID_ELF)),
                STATS_PLAYER_SOLO_CARDS_ELF,
                $playerId
            );
            $this->setStat(
                count($componentMgr->getCardsInSoloBoardIcon(\GB\COMPONENT_ICON_ID_DWARF)),
                STATS_PLAYER_SOLO_CARDS_DWARF,
                $playerId
            );
            $this->setStat(
                count($componentMgr->getCardsInSoloBoardIcon(\GB\COMPONENT_ICON_ID_BUILDING)),
                STATS_PLAYER_SOLO_CARDS_BUILDING,
                $playerId
            );

            $playerMgr->updatePlayerScoreAuxNow($playerId, 0);

            // Solo tiebreaker
            $playerScore = $playerMgr->getByPlayerId($playerId)->playerScore;
            $soloScore = $gameStateMgr->getSoloGoldCount();
            if ($playerScore < $soloScore) {
                $playerMgr->updatePlayerScoreNow($playerId, 0);
            } else if ($playerScore == $soloScore) {
                $playerNugget = $playerStateMgr->getByPlayerId($playerId)->nuggetCount;
                $soloNugget = $gameStateMgr->getSoloNuggetCount();
                if ($playerNugget < $soloNugget) {
                    $playerMgr->updatePlayerScoreNow($playerId, 0);
                } else if ($playerNugget == $soloNugget) {
                    $playerEnemy = count($componentMgr->getPlayerEnemy($playerId));
                    $soloEnemy = count($componentMgr->getPlayerEnemy(null));
                    if ($playerEnemy <= $soloEnemy) {
                        $playerMgr->updatePlayerScoreNow($playerId, 0);
                    }
                }
            }
        }
    }
}
