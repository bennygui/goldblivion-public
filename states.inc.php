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
 * states.inc.php
 *
 * goldblivion game states description
 *
 */

require_once("modules/BX/php/Globals.php");
require_once("modules/GB/php/Globals.php");

$machinestates = [

    // The initial state. Please do not modify.
    STATE_GAME_START_ID => [
        "name" => STATE_GAME_START,
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => [
            "" => STATE_PLAYER_SETUP_CHOOSE_NOBLE_ID,
        ],
    ],

    // Player setup
    STATE_PLAYER_SETUP_CHOOSE_NOBLE_ID => [
        "name" => STATE_PLAYER_SETUP_CHOOSE_NOBLE,
        "description" => clienttranslate('${actplayer} must: ${chooseNoble}'),
        "descriptionmyturn" => clienttranslate('${you} must: ${chooseNoble}'),
        "type" => "activeplayer",
        "args" => "argPlayerSetupChooseNoble",
        "possibleactions" => [
            'playerSetupChooseNoble',
        ],
        "transitions" => [
            '' => STATE_PLAYER_SETUP_NEXT_ID,
        ],
    ],
    STATE_PLAYER_SETUP_NEXT_ID => [
        "name" => STATE_PLAYER_SETUP_NEXT,
        "type" => "game",
        "action" => 'stPlayerSetupNext',
        "transitions" => [
            'nextPlayerSetup' => STATE_PLAYER_SETUP_CHOOSE_NOBLE_ID,
            'enterRound' => STATE_ROUND_START_ID,
        ],
    ],

    // Round
    STATE_ROUND_START_ID => [
        "name" => STATE_ROUND_START,
        "type" => "game",
        "action" => 'stRoundStart',
        "updateGameProgression" => true,
        "transitions" => [
            'production' => STATE_ROUND_PRODUCTION_ID,
            'chooseCardDevelop' => STATE_ROUND_CHOOSE_CARD_DEVELOP_ID,
        ],
    ],
    STATE_ROUND_CHOOSE_CARD_DEVELOP_ID => [
        "name" => STATE_ROUND_CHOOSE_CARD_DEVELOP,
        "description" => clienttranslate('Other players must choose a card and a side for development'),
        "descriptionmyturn" => clienttranslate('${you} must choose a card and a side for development'),
        "type" => "multipleactiveplayer",
        "args" => "argsRoundChooseCardDevelop",
        "action" => 'stRoundChooseCardDevelop',
        "possibleactions" => [
            'playerRoundChooseCardDevelop',
            'playerRoundChooseCardDevelopUndo',
        ],
        "transitions" => [
            '' => STATE_ROUND_PRODUCTION_ID,
        ],
    ],
    STATE_ROUND_PRODUCTION_ID => [
        "name" => STATE_ROUND_PRODUCTION,
        "type" => "game",
        "action" => 'stRoundProduction',
        "transitions" => [
            '' => STATE_PLAYER_ACTION_START_ID,
        ],
    ],
    STATE_ROUND_NEXT_PLAYER_ID => [
        "name" => STATE_ROUND_NEXT_PLAYER,
        "type" => "game",
        "action" => 'stRoundNextPlayer',
        "updateGameProgression" => true,
        "transitions" => [
            'multi' => STATE_ROUND_NEXT_PLAYER_POST_ID,
            'solo' => STATE_SOLO_ENTER_ID,
        ],
    ],
    STATE_ROUND_NEXT_PLAYER_POST_ID => [
        "name" => STATE_ROUND_NEXT_PLAYER_POST,
        "type" => "game",
        "action" => 'stRoundNextPlayerPost',
        "updateGameProgression" => true,
        "transitions" => [
            'endGame' => STATE_PRE_GAME_END_ID,
            'startNewRound' => STATE_ROUND_START_ID,
            'chooseCardToDestroy' => STATE_ROUND_CHOOSE_CARD_TO_DESTROY_ID,
            'nextPlayerRound' => STATE_PLAYER_ACTION_START_ID,
            'nextPlayerRoundAutoPass' => STATE_ROUND_NEXT_PLAYER_POST_ID,
        ],
    ],
    STATE_ROUND_CHOOSE_CARD_TO_DESTROY_ID => [
        "name" => STATE_ROUND_CHOOSE_CARD_TO_DESTROY,
        "description" => clienttranslate('A new round starts, ${actplayer} must choose a card to destroy'),
        "descriptionmyturn" => clienttranslate('A new round starts, ${you} must choose a card to destroy'),
        "type" => "activeplayer",
        "args" => "argRoundChooseCardToDestroy",
        "possibleactions" => [
            'playerRoundChooseCardToDestroy',
        ],
        "transitions" => [
            '' => STATE_ROUND_START_ID,
        ],
    ],

    // Player Action
    STATE_PLAYER_ACTION_START_ID => [
        "name" => STATE_PLAYER_ACTION_START,
        "type" => "game",
        "updateGameProgression" => true,
        "action" => 'stPlayerActionStart',
        "transitions" => [
            '' => STATE_PLAYER_ACTION_LOOP_ID,
        ],
    ],
    STATE_PLAYER_ACTION_LOOP_ID => [
        "name" => STATE_PLAYER_ACTION_LOOP,
        "type" => "game",
        "updateGameProgression" => true,
        "action" => 'stPlayerActionLoop',
        "transitions" => [
            'chooseAction' => STATE_PLAYER_ACTION_CHOOSE_ACTION_ID,
            'nextPlayerInRound' => STATE_ROUND_NEXT_PLAYER_ID,
        ],
    ],
    STATE_PLAYER_ACTION_CHOOSE_ACTION_ID => [
        "name" => STATE_PLAYER_ACTION_CHOOSE_ACTION,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose: ${actions}'),
        "type" => "activeplayer",
        "args" => "argPlayerActionChooseAction",
        "possibleactions" => [
            // Pass
            'playerActionPass',
            'playerActionEndTurn',
            // Bonus
            'playerActionBonusConvertNuggetToGold',
            'playerActionBonusActivateBuilding',
            'playerActionBonusPlayMagic',
            // Main
            'playerActionMainPlayCard',
            'playerActionMainPlayVillage',
            'playerActionMainBuyCard',
        ],
        "transitions" => [
            'chooseActionLoop' => STATE_PLAYER_ACTION_LOOP_ID,
            'nextPlayerInChooseAction' => STATE_ROUND_NEXT_PLAYER_ID,
        ],
    ],

    // Ability Activation
    STATE_ABILITY_ACTIVATION_ENTER_ID => [
        "name" => STATE_ABILITY_ACTIVATION_ENTER,
        "type" => "game",
        "action" => 'stAbilityActivationEnter',
        "transitions" => [
            'activateInstant' => STATE_ABILITY_ACTIVATION_INSTANT_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_INSTANT_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INSTANT,
        "type" => "game",
        "action" => 'stAbilityActivationInstant',
        "transitions" => [
            'enterInteractiveAbility' => STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP_ID,
            'enterCombatAbility' => STATE_COMBAT_SELECT_ENEMY_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP,
        "type" => "game",
        "action" => 'stAbilityActivationInteractiveLoop',
        "transitions" => [
            'activationExit' => STATE_ABILITY_ACTIVATION_EXIT_ID,
            'destroy' => STATE_ABILITY_ACTIVATION_INTERACTIVE_DESTROY_ID,
            'gainRed' => STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_RED_ID,
            'gainBlue' => STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_BLUE_ID,
            'reactivateHumanoid' => STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_HUMANOID_ID,
            'reactivateBuilding' => STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_BUILDING_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_INTERACTIVE_DESTROY_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INTERACTIVE_DESTROY,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose what to destroy'),
        "type" => "activeplayer",
        "args" => "argAbilityActivationInteractiveDestroy",
        "possibleactions" => [
            'abilityActivationInteractiveDestroy'
        ],
        "transitions" => [
            '' => STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_RED_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_RED,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Combat card: ${gainFrom} ${interactiveCount}'),
        "type" => "activeplayer",
        "args" => "argAbilityActivationInteractiveGainRed",
        "possibleactions" => [
            'abilityActivationInteractiveGainRed'
        ],
        "transitions" => [
            '' => STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_BLUE_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INTERACTIVE_GAIN_BLUE,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose a GOLDblivion card to gain ${interactiveCount}'),
        "type" => "activeplayer",
        "args" => "argAbilityActivationInteractiveGainBlue",
        "possibleactions" => [
            'abilityActivationInteractiveGainBlue'
        ],
        "transitions" => [
            '' => STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_HUMANOID_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_HUMANOID,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose a played GOLDblivion card to reactivate'),
        "type" => "activeplayer",
        "args" => "argAbilityActivationInteractiveReactivateHumanoid",
        "possibleactions" => [
            'abilityActivationInteractiveReactivateHumanoid'
        ],
        "transitions" => [],
    ],
    STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_BUILDING_ID => [
        "name" => STATE_ABILITY_ACTIVATION_INTERACTIVE_REACTIVATE_BUILDING,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose an activated building to reactivate'),
        "type" => "activeplayer",
        "args" => "argAbilityActivationInteractiveReactivateBuilding",
        "possibleactions" => [
            'abilityActivationInteractiveReactivateBuilding'
        ],
        "transitions" => [
            '' => STATE_ABILITY_ACTIVATION_INTERACTIVE_LOOP_ID,
        ],
    ],
    STATE_ABILITY_ACTIVATION_EXIT_ID => [
        "name" => STATE_ABILITY_ACTIVATION_EXIT,
        "type" => "game",
        "action" => 'stAbilityActivationExit',
        "transitions" => [
            '' => STATE_PLAYER_ACTION_LOOP_ID,
        ],
    ],

    // Combat
    STATE_COMBAT_SELECT_ENEMY_ID => [
        "name" => STATE_COMBAT_SELECT_ENEMY,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose an enemy'),
        "type" => "activeplayer",
        "args" => "argCombatSelectEnemy",
        "possibleactions" => [
            'combatSelectEnemy',
        ],
        "transitions" => [
            'interactive' => STATE_COMBAT_INTERACTIVE_ID,
            'endCombat' => STATE_COMBAT_WIN_OR_LOSE_ID,
        ],
    ],
    STATE_COMBAT_INTERACTIVE_ID => [
        "name" => STATE_COMBAT_INTERACTIVE,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must: ${actions}'),
        "type" => "activeplayer",
        "args" => "argCombatInteractive",
        "possibleactions" => [
            'combatInteractive',
            'combatInteractiveEndCombat',
        ],
        "transitions" => [
            'interactive' => STATE_COMBAT_INTERACTIVE_ID,
            'reactivate' => STATE_COMBAT_INTERACTIVE_REACTIVATE_RED_CARD_ID,
            'endCombat' => STATE_COMBAT_WIN_OR_LOSE_ID,
        ],
    ],
    STATE_COMBAT_INTERACTIVE_REACTIVATE_RED_CARD_ID => [
        "name" => STATE_COMBAT_INTERACTIVE_REACTIVATE_RED_CARD,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose a played Combat card to copy'),
        "type" => "activeplayer",
        "args" => "argCombatInteractiveReactivateRedCard",
        "possibleactions" => [
            'combatInteractiveReactivateRedCard',
        ],
        "transitions" => [
            'interactive' => STATE_COMBAT_INTERACTIVE_ID,
        ],
    ],
    STATE_COMBAT_WIN_OR_LOSE_ID  => [
        "name" => STATE_COMBAT_WIN_OR_LOSE_ID,
        "type" => "game",
        "action" => 'stCombatWinOrLose',
        "transitions" => [
            'loseDestroyCard' => STATE_COMBAT_LOSE_DESTROY_RED_CARD_ID,
            'activationExit' => STATE_ABILITY_ACTIVATION_EXIT_ID,
        ],
    ],
    STATE_COMBAT_LOSE_DESTROY_RED_CARD_ID => [
        "name" => STATE_COMBAT_LOSE_DESTROY_RED_CARD,
        "description" => clienttranslate('${actplayer} must destroy a combat card used in the fight'),
        "descriptionmyturn" => clienttranslate('${you} must destroy a combat card used in the fight'),
        "type" => "activeplayer",
        "args" => "argCombatLoseDestroyRedCard",
        "possibleactions" => [
            'combatLoseDestroyRedCard'
        ],
        "transitions" => [
            '' => STATE_ABILITY_ACTIVATION_EXIT_ID,
        ],
    ],

    // Solo states
    STATE_SOLO_LOST_UNFILLED_MARKET_ID => [
        "name" => STATE_SOLO_LOST_UNFILLED_MARKET,
        "type" => "game",
        "action" => 'stSoloLostUnfilledMarket',
        "transitions" => [
            '' => STATE_GAME_END_ID,
        ],
    ],
    STATE_SOLO_ENTER_ID => [
        "name" => STATE_SOLO_ENTER,
        "type" => "game",
        "action" => 'stSoloEnter',
        "transitions" => [
            'choose' => STATE_SOLO_CHOOSE_MARKET_ACTIVATION_ID,
            'activate' => STATE_SOLO_MARKET_ACTIVATION_ID,
        ],
    ],
    STATE_SOLO_CHOOSE_MARKET_ACTIVATION_ID => [
        "name" => STATE_SOLO_CHOOSE_MARKET_ACTIVATION,
        "description" => clienttranslate('${actplayer} must choose the Solo Board activation side'),
        "descriptionmyturn" => clienttranslate('${you} must choose the Solo Board activation side'),
        "type" => "activeplayer",
        "args" => "argSoloChooseMarketActivation",
        "possibleactions" => [
            'soloChooseMarketActivation'
        ],
        "transitions" => [
            '' => STATE_SOLO_MARKET_ACTIVATION_ID,
        ],
    ],
    STATE_SOLO_MARKET_ACTIVATION_ID => [
        "name" => STATE_SOLO_MARKET_ACTIVATION,
        "description" => clienttranslate('Activating Solo Noble Abilities...'),
        "type" => "game",
        "action" => 'stSoloMarketActivation',
        "transitions" => [
            'revealEnemy' => STATE_SOLO_REVEAL_ENEMY_ID,
            'destroyEnemy' => STATE_SOLO_DESTROY_ENEMY_ID,
            'endSolo' => STATE_ROUND_NEXT_PLAYER_POST_ID,
        ],
    ],
    STATE_SOLO_REVEAL_ENEMY_ID => [
        "name" => STATE_SOLO_REVEAL_ENEMY,
        "description" => clienttranslate('${actplayer} must choose an enemy to reveal'),
        "descriptionmyturn" => clienttranslate('${you} must choose an enemy to reveal'),
        "type" => "activeplayer",
        "args" => "argSoloRevealEnemy",
        "possibleactions" => [
            'soloRevealEnemy'
        ],
        "transitions" => [
            '' => STATE_SOLO_MARKET_ACTIVATION_ID,
        ],
    ],
    STATE_SOLO_DESTROY_ENEMY_ID => [
        "name" => STATE_SOLO_DESTROY_ENEMY,
        "description" => clienttranslate('${actplayer} must choose an enemy to destroy'),
        "descriptionmyturn" => clienttranslate('${you} must choose an enemy to destroy'),
        "type" => "activeplayer",
        "args" => "argSoloDestroyEnemy",
        "possibleactions" => [
            'soloDestroyEnemy'
        ],
        "transitions" => [
            '' => STATE_SOLO_MARKET_ACTIVATION_ID,
        ],
    ],

    // End game state
    STATE_PRE_GAME_END_ID => [
        "name" => STATE_PRE_GAME_END,
        "type" => "game",
        "action" => 'stPreGameEnd',
        "transitions" => [
            '' => STATE_GAME_END_ID,
        ],
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_GAME_END_ID => [
        "name" => STATE_GAME_END,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],
];
