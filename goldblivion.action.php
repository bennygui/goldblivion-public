<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * goldblivion.action.php
 *
 * goldblivion main action entry point
 *
 */

require_once("modules/GB/php/Globals.php");

class action_goldblivion extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
    } else {
      $this->view = "goldblivion_goldblivion";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function undoLast()
  {
    self::setAjaxMode();
    $this->game->undoLast();
    self::ajaxResponse();
  }

  public function undoAll()
  {
    self::setAjaxMode();
    $this->game->undoAll();
    self::ajaxResponse();
  }

  public function playerSetupChooseNoble()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->playerSetupChooseNoble($componentId);

    self::ajaxResponse();
  }

  public function playerRoundChooseCardDevelop()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $side = $this->getArg("side", AT_posint, true);
    $this->game->playerRoundChooseCardDevelop($componentId, $side);

    self::ajaxResponse();
  }

  public function playerRoundChooseCardDevelopUndo()
  {
    self::setAjaxMode();

    $this->game->playerRoundChooseCardDevelopUndo();

    self::ajaxResponse();
  }

  public function playerActionPass()
  {
    self::setAjaxMode();

    $this->game->playerActionPass();

    self::ajaxResponse();
  }

  public function playerActionEndTurn()
  {
    self::setAjaxMode();

    $this->game->playerActionEndTurn();

    self::ajaxResponse();
  }

  public function playerRoundChooseCardToDestroy()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->playerRoundChooseCardToDestroy($componentId);

    self::ajaxResponse();
  }

  public function playerActionBonusConvertNuggetToGold()
  {
    self::setAjaxMode();

    $this->game->playerActionBonusConvertNuggetToGold();

    self::ajaxResponse();
  }

  public function playerActionBonusActivateBuilding()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $side = $this->getArg("side", AT_posint, true);
    $this->game->playerActionBonusActivateBuilding($componentId, $side);

    self::ajaxResponse();
  }

  public function playerActionBonusPlayMagic()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->playerActionBonusPlayMagic($componentId);

    self::ajaxResponse();
  }

  public function playerActionMainPlayCard()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $side = $this->getArg("side", AT_posint, true);
    $this->game->playerActionMainPlayCard($componentId, $side);

    self::ajaxResponse();
  }

  public function playerActionMainPlayVillage()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->playerActionMainPlayVillage($componentId);

    self::ajaxResponse();
  }

  public function playerActionMainBuyCard()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->playerActionMainBuyCard($componentId);

    self::ajaxResponse();
  }

  public function abilityActivationInteractiveDestroy()
  {
    self::setAjaxMode();

    $componentIds = $this->getArg("componentIds", AT_numberlist, false);
    if ($componentIds === null) {
      $componentIds = '';
    }
    $componentIds = trim($componentIds);
    if (strlen($componentIds) == 0) {
      $componentIds = [];
    } else {
      $componentIds = explode(',', $componentIds);
    }
    $soloNugget = $this->getArg("soloNugget", AT_posint, true);
    $this->game->abilityActivationInteractiveDestroy($componentIds, $soloNugget);

    self::ajaxResponse();
  }

  public function abilityActivationInteractiveGainRed()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, false);
    $redDeckPlayerId = $this->getArg("redDeckPlayerId", AT_posint, false);
    $this->game->abilityActivationInteractiveGainRed($componentId, $redDeckPlayerId);

    self::ajaxResponse();
  }

  public function abilityActivationInteractiveGainBlue()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->abilityActivationInteractiveGainBlue($componentId);

    self::ajaxResponse();
  }

  public function abilityActivationInteractiveReactivateHumanoid()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $side = $this->getArg("side", AT_posint, true);
    $this->game->abilityActivationInteractiveReactivateHumanoid($componentId, $side);

    self::ajaxResponse();
  }

  public function abilityActivationInteractiveReactivateBuilding()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->abilityActivationInteractiveReactivateBuilding($componentId);

    self::ajaxResponse();
  }

  public function combatSelectEnemy()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, false);
    $locationId = $this->getArg("locationId", AT_posint, false);
    $this->game->combatSelectEnemy($componentId, $locationId);

    self::ajaxResponse();
  }

  public function combatLoseDestroyRedCard()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->combatLoseDestroyRedCard($componentId);

    self::ajaxResponse();
  }

  public function combatInteractive()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $side = $this->getArg("side", AT_posint, true);
    $this->game->combatInteractive($componentId, $side);

    self::ajaxResponse();
  }

  public function combatInteractiveEndCombat()
  {
    self::setAjaxMode();

    $this->game->combatInteractiveEndCombat();

    self::ajaxResponse();
  }

  public function combatInteractiveReactivateRedCard()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $side = $this->getArg("side", AT_posint, true);
    $this->game->combatInteractiveReactivateRedCard($componentId, $side);

    self::ajaxResponse();
  }

  public function handOrderToStart()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->handOrderToStart($componentId);

    self::ajaxResponse();
  }

  public function handOrderToLeft()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->handOrderToLeft($componentId);

    self::ajaxResponse();
  }

  public function handOrderToRight()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->handOrderToRight($componentId);

    self::ajaxResponse();
  }

  public function handOrderToEnd()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->handOrderToEnd($componentId);

    self::ajaxResponse();
  }

  public function soloChooseMarketActivation()
  {
    self::setAjaxMode();

    $iconId = $this->getArg("iconId", AT_posint, true);
    $this->game->soloChooseMarketActivation($iconId);

    self::ajaxResponse();
  }

  public function soloRevealEnemy()
  {
    self::setAjaxMode();

    $locationId = $this->getArg("locationId", AT_posint, true);
    $this->game->soloRevealEnemy($locationId);

    self::ajaxResponse();
  }

  public function soloDestroyEnemy()
  {
    self::setAjaxMode();

    $componentId = $this->getArg("componentId", AT_posint, true);
    $this->game->soloDestroyEnemy($componentId);

    self::ajaxResponse();
  }
}
