/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define([
    "dojo",
    "dojo/_base/declare",
],
    function (dojo, declare) {
        return declare("gb.RoundTrait", null, {
            onButtonsStateRoundChooseCardDevelop(args) {
                debug('onButtonsStateRoundChooseCardDevelop');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    this.addTopButtonSecondary(
                        'button-change-selection',
                        _('Change development choice'),
                        () => this.serverAction('playerRoundChooseCardDevelopUndo')
                    );
                    return;
                }
                args = args._private;
                const clickableElements = [];
                let choosenSide = null;
                let choosenId = null;

                const removeSelectedSides = () => {
                    for (let i = 0; i <= 1; ++i) {
                        const e = this.playerBoardMgr.getPlayerBoardDevelop(this.player_id, i);
                        this.removeSelected(e);
                    }
                };
                for (let i = 0; i <= 1; ++i) {
                    const e = this.playerBoardMgr.getPlayerBoardDevelop(this.player_id, i);
                    const onClick = () => {
                        removeSelectedSides();
                        if (choosenSide == i) {
                            choosenSide = null;
                            this.setTopButtonValid(BUTTON_CHOOSE_CARD_ID, false);
                        } else {
                            this.addSelected(e);
                            choosenSide = i;
                            this.setTopButtonValid(BUTTON_CHOOSE_CARD_ID, choosenId !== null);
                        }
                    };
                    this.addClickable(e, onClick);
                    clickableElements.push({e: e, onClick: onClick});
                }

                const BUTTON_CHOOSE_CARD_ID = 'button-choose-card';
                const removeSelectedCards = () => {
                    for (const id of args.componentIds) {
                        const e = this.componentMgr.getComponentById(id);
                        this.removeSelected(e);
                    }
                };
                for (const id of args.componentIds) {
                    const e = this.componentMgr.getComponentById(id);
                    const onClick = () => {
                        removeSelectedCards();
                        if (choosenId === id) {
                            choosenId = null;
                            this.setTopButtonValid(BUTTON_CHOOSE_CARD_ID, false);
                        } else {
                            this.addSelected(e);
                            choosenId = id;
                            this.setTopButtonValid(BUTTON_CHOOSE_CARD_ID, choosenSide !== null);
                        }
                    };
                    this.addClickable(e, onClick);
                    clickableElements.push({e: e, onClick: onClick});
                }
                this.addTopButtonPrimaryWithValid(
                    BUTTON_CHOOSE_CARD_ID,
                    _('Choose Card and Side'),
                    _('You must choose a Card and a Side'),
                    () => {
                        this.showConfirmDialogIfConfirm(
                            _('Are you sure you want to send this card for development? It will not be available for the rest of the game.')
                        ).then(() => {
                            this.serverAction('playerRoundChooseCardDevelop', { componentId: choosenId, side: choosenSide });
                        });
                    }
                );
                this.setTopButtonValid(BUTTON_CHOOSE_CARD_ID, false);
                for (const info of clickableElements) {
                    if (this.elementWasSelectedBeforeRemoveAll(info.e)) {
                        info.onClick();
                    }
                }
                this.clearSelectedBeforeRemoveAll();
            },

            onButtonsStateRoundChooseCardToDestroy(args) {
                debug('onButtonsStateRoundChooseCardToDestroy');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Destroy Card'),
                    _('You must choose a card to destroy from the GOLDblivion market'),
                    {
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getComponentById(id),
                        onClick: (id) => {
                            this.showConfirmDialogIfConfirm(
                                _('Are you sure you want to destroy this card? This cannot be undone.')
                            ).then(() => {
                                this.serverAction('playerRoundChooseCardToDestroy', { componentId: id })
                            });
                        },
                    },
                );
            },
        });
    });
