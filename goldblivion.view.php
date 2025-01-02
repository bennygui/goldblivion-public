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
 * goldblivion.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in goldblivion_goldblivion.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_goldblivion_goldblivion extends game_view
{
  public function getGameName()
  {
    return "goldblivion";
  }

  private function insertPlayerAreaBlock($playerId, $playerInfo)
  {
    $this->page->insert_block(
      "player-area",
      [
        "PLAYER_ID" => $playerId,
        "PLAYER_NAME" => $playerInfo['player_name'],
        "PLAYER_COLOR" => $playerInfo['player_color'],
        "RED_FIGHTER" => self::_('Fighters'),
        "RED_EXHAUSTED" => self::_('Exausted Fighters'),
        "ENEMY" => self::_('Enemy'),
        "COMPACT" => self::_('Compact'),
      ]
    );
  }

  public function build_page($viewArgs)
  {
    $this->tpl['DISPLAY_LAST_ROUND'] = self::_('This is the last round!');
    $this->tpl['NO_CARDS_IN_HAND'] = self::_('You have no cards in hand');
    $this->tpl['DISCARDED_CARDS'] = self::_('Destroyed cards');
    $this->tpl['LIST_INITIAL_BLUE_CARDS'] = self::_('List of initial GOLDblivion player deck');
    $this->tpl['LIST_INITIAL_RED_CARDS'] = self::_('List of initial Combat player deck');
    $this->tpl['LIST_DECK_BLUE_CARDS'] = self::_('List of GOLDblivion cards in the deck at the start of the game');
    $this->tpl['LIST_DECK_RED_CARDS'] = self::_('List of Combat cards in the decks at the start of the game');
    $this->tpl['LIST_MAGIC'] = self::_('List of all Magic tokens');
    $this->tpl['LIST_ENEMY'] = self::_('List of all Enemy tiles');
    $this->tpl['LIST_VILLAGE'] = self::_('Two sides of the Village tiles');
    $this->tpl['LIST_DICE_FACE'] = self::_('List of dice faces');

    $currentPlayerId = $this->game->currentPlayerId();
    $playersInfos = $this->game->loadPlayersBasicInfos();
    $this->page->begin_block("goldblivion_goldblivion", "player-area");
    if (array_key_exists($currentPlayerId, $playersInfos)) {
      $this->insertPlayerAreaBlock($currentPlayerId, $playersInfos[$currentPlayerId]);
    }

    $playerIdArray = array_keys($playersInfos);
    usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
      return ($playersInfos[$p1]['player_no'] <> $playersInfos[$p2]['player_no']);
    });

    $currentPlayerIndex = array_search($currentPlayerId, $playerIdArray);
    if ($currentPlayerIndex === false) {
      $currentPlayerIndex = -1;
    }

    // Insert players that are after the current player
    foreach ($playerIdArray as $i => $playerId) {
      if ($i > $currentPlayerIndex) {
        $this->insertPlayerAreaBlock($playerId, $playersInfos[$playerId]);
      }
    }

    // Insert players that are before the current player
    foreach ($playerIdArray as $i => $playerId) {
      if ($i < $currentPlayerIndex) {
        $this->insertPlayerAreaBlock($playerId, $playersInfos[$playerId]);
      }
    }
    /*********** Do not change anything below this line  ************/
  }
}
