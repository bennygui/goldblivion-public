
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- goldblivion implementation : © Guillaume Benny bennygui@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- Components
CREATE TABLE IF NOT EXISTS `component` (
  -- unique id: Each component in the game will have a unique id
  `component_id` int(10) unsigned NOT NULL,
  -- The id to get the static definition of this component
  `type_id` int(10) unsigned NOT NULL,
  -- Player that has this component, null if no player has it
  `player_id` int(10) unsigned NULL,
  -- The location of the component (supply, market, ...)
  `location_id` int(10) unsigned NOT NULL,
  -- The primary order of the component
  `location_primary_order` int(10) unsigned NOT NULL,
  -- The secondary order of the component, for some locations
  `location_secondary_order` int(10) unsigned NOT NULL,
  -- If the component is in a 'used' state, for buildings and combat cards
  `is_used` boolean NOT NULL,
  PRIMARY KEY (`component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State for each players
CREATE TABLE IF NOT EXISTS `player_state` (
  -- The state is for this player
  `player_id` int(10) unsigned NOT NULL,
  -- Number of nuggets for this player
  `nugget_count` smallint(5) NOT NULL,
  -- Number of material for this player
  `material_count` smallint(5) NOT NULL,
  -- Number of main actions that the active player has
  `action_count` smallint(5) NOT NULL,
  -- Did the player pass in this round?
  `passed` boolean NOT NULL,
  -- Player power in current combat
  `combat_power` smallint(5) NOT NULL,
  -- Number of cards still to draw in current combat
  `combat_draw` smallint(5) NOT NULL,
  -- Component id of the selected enemy in current combat
  `combat_enemy_component_id` int(10) unsigned NULL,
  -- Component id of the current interactive combat card
  `combat_interactive_component_id` int(10) unsigned NULL,
  -- Component to activate is from a copy abiltiy
  `combat_interactive_reactivate_component_id` int(10) unsigned NULL,
  -- Stats: Gained nugget
  `stat_gained_nugget` smallint(5) NOT NULL,
  -- Stats: Gained material
  `stat_gained_material` smallint(5) NOT NULL,
  -- Stats: Gained gold from nuggets
  `stat_gained_gold_from_nugget` smallint(5) NOT NULL,
  -- Stats: Gain blue cards
  `stat_gained_blue_card` smallint(5) NOT NULL,
  -- Stats: Gain red cards
  `stat_gained_red_card` smallint(5) NOT NULL,
  -- Stats: Gain magic tokens
  `stat_gained_magic_token` smallint(5) NOT NULL,
  -- Stats: Won combats
  `stat_combat_won` smallint(5) NOT NULL,
  -- Stats: Lost combats
  `stat_combat_lost` smallint(5) NOT NULL,
  -- Stats: Won combats against the Cow-Dragon
  `stat_combat_won_cow` smallint(5) NOT NULL,
  -- Stats: Destroyed blue market cards
  `stat_destroyed_blue_market` smallint(5) NOT NULL,
  -- Stats: Destroyed red market cards
  `stat_destroyed_red_market` smallint(5) NOT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Hand card order for each player
CREATE TABLE IF NOT EXISTS `player_hand_order` (
  -- The player for the card
  `player_id` int(10) unsigned NOT NULL,
  -- Component id of the card
  `component_id` int(10) unsigned NOT NULL,
  -- The order in hand
  `component_order` smallint(5) NOT NULL,
  PRIMARY KEY (`player_id`, `component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State for the whole game
CREATE TABLE IF NOT EXISTS `game_state` (
  -- Always 0, there is only one game state
  `game_state_id` smallint(5) NOT NULL,
  -- The player that is the first player for this round
  `round_first_player_id` int(10) unsigned NOT NULL,
  -- Is the player loosing because the market cannot be filled?
  `solo_lost_unfilled_market` boolean NOT NULL,
  -- Number of nuggets for the solo noble
  `solo_nugget_count` smallint(5) NOT NULL,
  -- Number of material for the solo noble
  `solo_material_count` smallint(5) NOT NULL,
  -- Number of gold for the solo noble
  `solo_gold_count` smallint(5) NOT NULL,
  -- Component id of the card the dice selected in the market
  `solo_market_component_id` int(10) unsigned NULL,
  -- List of solo actions that must be activated
  `solo_action_list` varchar(65535) NOT NULL,
  PRIMARY KEY (`game_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- === BX Tables === --

-- Lock table
CREATE TABLE IF NOT EXISTS `bx_lock` (
  -- The only value to lock, has no meaning
  `lock_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State function calls for each players
CREATE TABLE IF NOT EXISTS `bx_state_function` (
  -- The player functions
  `player_id` int(10) unsigned NOT NULL,
  -- Function call array
  `function_calls` varchar(65535) NOT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Actions that are still private to a player and that can be undone
CREATE TABLE IF NOT EXISTS `bx_action_command` (
  -- unique id with no meaning 
  `action_command_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- json version of the class
  `action_json` varchar(65535) NOT NULL,
  PRIMARY KEY (`action_command_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;