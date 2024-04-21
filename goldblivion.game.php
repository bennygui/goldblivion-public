<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * goldblivion.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
require_once("modules/BX/php/DB.php");
require_once("modules/BX/php/Lock.php");
require_once("modules/BX/php/Action.php");
require_once("modules/BX/php/UI.php");
require_once("modules/BX/php/Player.php");
require_once("modules/BX/php/ActiveState.php");
require_once("modules/BX/php/MultiActiveState.php");
require_once("modules/BX/php/StateFunction.php");
require_once("modules/GB/php/Globals.php");
require_once("modules/GB/php/Player.php");
require_once("modules/GB/php/Component.php");
require_once("modules/GB/php/PlayerState.php");
require_once("modules/GB/php/PlayerHandOrder.php");
require_once("modules/GB/php/GameState.php");
require_once("modules/GB/php/EnemyMapDefMgr.php");
require_once("modules/GB/php/States/Common.php");
require_once("modules/GB/php/States/PlayerSetup.php");
require_once("modules/GB/php/States/Round.php");
require_once("modules/GB/php/States/PlayerAction.php");
require_once("modules/GB/php/States/AbilityActivation.php");
require_once("modules/GB/php/States/Combat.php");
require_once("modules/GB/php/States/HandOrder.php");
require_once("modules/GB/php/States/Solo.php");
require_once("modules/GB/php/States/GameEnd.php");

require_once("modules/GB/php/Debug.php");

\BX\Action\BaseActionCommandNotifier::sendPrivateNotificationMessage(true);
\BX\StateFunction\registerStateFunctionMgr();
\BX\Action\ActionRowMgrRegister::registerMgr('player', \GB\PlayerMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('component', \GB\ComponentMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('player_state', \GB\PlayerStateMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('player_hand_order', \GB\PlayerHandOrderMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('game_state', \GB\GameStateMgr::class);

class goldblivion extends Table
{
    use BX\Action\GameActionsTrait;
    use BX\ActiveState\GameStatesTrait;
    use BX\MultiActiveState\GameStatesTrait;
    use GB\State\Common\GameStatesTrait;
    use GB\State\PlayerSetup\GameStatesTrait;
    use GB\State\Round\GameStatesTrait;
    use GB\State\PlayerAction\GameStatesTrait;
    use GB\State\AbilityActivation\GameStatesTrait;
    use GB\State\Combat\GameStatesTrait;
    use GB\State\HandOrder\GameStatesTrait;
    use GB\State\Solo\GameStatesTrait;
    use GB\State\GameEnd\GameStatesTrait;

    use GB\Debug\GameStatesTrait;

    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        \BX\Action\BaseActionCommandNotifier::setGame($this);

        self::initGameStateLabels([]);
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "goldblivion";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = [])
    {
        $gameinfos = self::getGameinfos();

        \BX\Lock\Locker::setup();
        $colors = \BX\Action\ActionRowMgrRegister::getMgr('player')->setup(
            $players,
            $gameinfos['player_colors']
        );

        self::reattributeColorsBasedOnPreferences($players, $colors);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        $this->initStat('table', STATS_TABLE_NB_ROUND, 1);

        $playerIdArray = $this->getPlayerIdArray();
        \BX\StateFunction\stateFunctionSetup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('component')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('game_state')->setup($playerIdArray);

        // Activate last player in player order: last player choose Noble first
        $this->gamestate->changeActivePlayer($playerIdArray[count($playerIdArray) - 1]);

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = [];

        $playerId = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        \BX\Action\ActionCommandMgr::apply($playerId);

        $playerIdArray = $this->getPlayerIdArray();

        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $playerHandOrderMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_hand_order');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');

        $stateId = $this->gamestate->state_id();
        $gameHasEnded = ($stateId == STATE_GAME_END_ID);
        $result['players'] = $playerMgr->getAllForUI($gameHasEnded);
        if ($gameHasEnded && isGameSolo()) {
            foreach (array_keys($result['players']) as $pId) {
                $result['players'][$pId]['score'] = $this->getStat( STATS_PLAYER_GOLD_AT_END, $pId);
            }
        }
        $result['components'] = $componentMgr->getAllVisibleForPlayer($playerId);
        $result['playerDevelopmentTypeId'] = $componentMgr->getPlayerDevelopmentTypeId($playerId);
        $result['playerStates'] = $playerStateMgr->getAll();
        $result['playerHandOrder'] = $playerHandOrderMgr->getPlayerHandComponentOrder($playerId);
        $result['roundFirstPlayerId'] = $gameStateMgr->roundFirstPlayerId();
        $result['componentCounts'] = $componentMgr->getAllCounts();
        $result['componentDefs'] = \GB\ComponentDefMgr::getAll();
        $result['componentIdToTypeId'] = $componentMgr->getComponentIdToTypeId();
        $result['enemyMapDefs'] = \GB\EnemyMapDefMgr::getAll();
        $result['isLastRound'] = (!$gameHasEnded && $gameStateMgr->isEndGameTriggered());
        $result['round'] = $this->getStat(STATS_TABLE_NB_ROUND);
        $result['soloBoardDef'] = \GB\SoloBoardDefMgr::getAll();
        $result['soloNoble'] = gameSoloNoble();
        $result['soloNobleColorName'] = $playerMgr->getSoloNobleColorName();
        $result['soloActionList'] = $gameStateMgr->getSoloActionList();

        return $result;
    }

    protected function initTable()
    {
        parent::initTable();
        \BX\DB\RowMgrRegister::clearAllMgrCache();
    }

    public function currentPlayerId()
    {
        return $this->getCurrentPlayerId();
    }

    public function _($text)
    {
        return parent::_($text);
    }

    public function getPlayerIdArray()
    {
        $playersInfos = $this->loadPlayersBasicInfos();
        $playerIdArray = array_keys($playersInfos);
        usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
            return ($playersInfos[$p1]['player_no'] <=> $playersInfos[$p2]['player_no']);
        });
        return $playerIdArray;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $round = $this->getStat(STATS_TABLE_NB_ROUND);
        $round = min(($round === null ? 0 : 10 * $round), 70);

        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $score = min(10 * max(array_map(fn ($p) => $p->playerScore, $playerMgr->getAll())), 100);

        if (isGameSolo()) {
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $score = max(min(10 * $gameStateMgr->getSoloGoldCount(), 100), $score);
        }

        return max($round, $score);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */
    function zombieTurn($state, $playerId)
    {
        $this->notifyAllPlayers(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('The next actions are done automatically since player ${player_name} left'),
            [
                'player_name' => $this->loadPlayersBasicInfos()[$playerId]['player_name'],
            ]
        );
        \BX\Action\ActionCommandMgr::apply($playerId);
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $statename = $state['name'];
        switch ($statename) {
            case STATE_PLAYER_SETUP_CHOOSE_NOBLE:
                // Take random noble
                $componentIds = array_keys($componentMgr->getNoblesInDraftMarket());
                shuffle($componentIds);
                $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                $creator->add(new \GB\Actions\Ability\GainBlueCardToPlayerDeck($playerId, $componentIds[0], \GB\COMPONENT_LOCATION_ID_DRAFT_MARKET));
                $creator->commit();
                $this->gamestate->nextState();
                break;
            case STATE_ROUND_CHOOSE_CARD_DEVELOP:
                // Do nothing
                $this->gamestate->setPlayerNonMultiactive($playerId, '');
                break;
            case STATE_ROUND_CHOOSE_CARD_TO_DESTROY:
                // Do nothing
                $this->gamestate->nextState();
                break;
            case STATE_PLAYER_ACTION_CHOOSE_ACTION:
            case STATE_ABILITY_ACTIVATION_INSTANT:
            case STATE_ABILITY_ACTIVATION_INTERACTIVE_DESTROY:
            case STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_RED:
            case STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_BLUE:
            case STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_HUMANOID:
            case STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_BUILDING:
            case STATE_COMBAT_SELECT_ENEMY:
            case STATE_COMBAT_INTERACTIVE:
            case STATE_COMBAT_INTERACTIVE_REACTIVATE_RED_CARD:
            case STATE_COMBAT_LOSE_DESTROY_RED_CARD:
                \BX\Action\ActionCommandMgr::zombieRemoveAll($playerId);
                \BX\StateFunction\zombieRemoveAll($playerId);
                $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                $creator->add(new \GB\Actions\Combat\EndCombat($playerId, true));
                $creator->commit();
                $this->notifyAllPlayers(
                    NTF_SELECT_ENEMY,
                    '',
                    [
                        'selectEnemy' => null,
                    ]
                );
                $playerStateMgr->zombiePassNow($playerId);
                $this->notifyAllPlayers(
                    NTF_UPDATE_PASS,
                    clienttranslate('${player_name} passes (automatic)'),
                    [
                        'playerId' => $playerId,
                        'player_name' => $this->loadPlayersBasicInfos()[$playerId]['player_name'],
                        'passed' => true,
                    ]
                );
                $this->gamestate->jumpToState(STATE_ROUND_NEXT_PLAYER_ID);
                break;
            default:
                throw new \BgaSystemException("BUG! Zombie mode not supported for this game state: " . $statename);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    function upgradeTableDb($from_version)
    {
    }
}
