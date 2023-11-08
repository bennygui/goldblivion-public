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

namespace GB\State\Common;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/AbilityActivation.php');

trait GameStatesTrait
{
    private function callAbilityActivation(int $componentId, \BX\Action\ActionCommandCreatorInterface $creator, bool $mustCommit = false, ?int $side = null)
    {
        $creator->add(new \BX\StateFunction\StateFunctionCall(
            $creator->getPlayerId(),
            new \GB\Actions\AbilityActivation\StateFunction($componentId, $mustCommit, $side)
        ));
    }
}
