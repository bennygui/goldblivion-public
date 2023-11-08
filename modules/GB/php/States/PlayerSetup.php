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

namespace GB\State\PlayerSetup;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/Ability.php');
require_once(__DIR__ . '/../Component.php');

trait GameStatesTrait
{
    public function argPlayerSetupChooseNoble()
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $hasChoice = (count($componentMgr->getNoblesInDraftMarket()) > 1);
        return [
            'componentIds' => array_keys($componentMgr->getNoblesInDraftMarket()),
            'chooseNoble' => $hasChoice
                ? clienttranslate('choose a Noble card')
                : clienttranslate('accept the Noble card'),
            'i18n' => ['chooseNoble'],
        ];
    }

    public function playerSetupChooseNoble(int $componentId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("playerSetupChooseNoble");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \GB\Actions\Ability\GainBlueCardToPlayerDeck($playerId, $componentId, \GB\COMPONENT_LOCATION_ID_DRAFT_MARKET));
        $creator->commit();

        $this->giveExtraTime($playerId);

        $this->gamestate->nextState();
    }

    public function stPlayerSetupNext()
    {
        $componentMgr = \BX\Action\ActionRowMgrRegister::getMgr('component');
        $nobles = $componentMgr->getNoblesInDraftMarket();
        if (isGameSolo()) {
            $playerId = $this->getActivePlayerId();
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            foreach ($nobles as $card) {
                $creator->add(new \GB\Actions\Ability\DestroyCard($playerId, $card->componentId, \GB\COMPONENT_CATEGORY_ID_CARD_BLUE, true, true));
            }
            $creator->commit();
            $this->gamestate->nextState('enterRound');
        } else if (count($nobles) == 1) {
            $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
            $playerId = $playerMgr->getFirstPlayerId();
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \GB\Actions\Ability\GainBlueCardToPlayerDeck($playerId, array_keys($nobles)[0], \GB\COMPONENT_LOCATION_ID_DRAFT_MARKET));
            $creator->commit();
            $this->gamestate->nextState('enterRound');
        } else {
            $this->activePrevPlayer();
            $this->gamestate->nextState('nextPlayerSetup');
        }
    }
}
