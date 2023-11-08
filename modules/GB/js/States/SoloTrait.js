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
        return declare("gb.SoloTrait", null, {
            onButtonsStateSoloChooseMarketActivation(args) {
                debug('onButtonsStateSoloChooseMarketActivation');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (this.seenMoreThanOneStateList()) {
                    gameui.autoScroll(document.querySelector('.gb-area-solo-container'), this.getActivePlayerId(), false);
                }
                this.addTopButtonSelection(
                    _('Choose Side'),
                    _('You must choose a side where to place the card'),
                    {
                        ids: args.iconIds,
                        onElement: (id) => this.soloMgr.getIconCardElem(id),
                        onClick: (id) => this.serverAction('soloChooseMarketActivation', { iconId: id }),
                    },
                );
            },

            onButtonsStateSoloRevealEnemy(args) {
                debug('onButtonsStateSoloRevealEnemy');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (this.seenMoreThanOneStateList()) {
                    gameui.autoScroll(document.getElementById('gb-main-board'), this.getActivePlayerId(), false);
                }
                this.addTopButtonSelection(
                    _('Reveal Enemy'),
                    _('You must choose a hidden, reachable enemy'),
                    {
                        ids: args.locationIds,
                        onElement: (id) => this.mainBoardMgr.getEnemyByLocationId(id),
                        onClick: (id) => {
                            this.showConfirmDialogIfConfirm().then(() => {
                                this.serverAction('soloRevealEnemy', { locationId: id });
                            });
                        },
                        clickableOption: { childEventSelector: '.gb-enemy-click-target' },
                    },
                );
            },

            onButtonsStateSoloDestroyEnemy(args) {
                debug('onButtonsStateSoloDestroyEnemy');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (this.seenMoreThanOneStateList()) {
                    gameui.autoScroll(document.getElementById('gb-main-board'), this.getActivePlayerId(), false);
                }
                this.addTopButtonSelection(
                    _('Destroy Enemy'),
                    _('You must choose a revealed enemy'),
                    {
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getComponentById(id),
                        onClick: (id) => {
                            this.showConfirmDialogIfConfirm().then(() => {
                                this.serverAction('soloDestroyEnemy', { componentId: id });
                            });
                        },
                        clickableOption: { childEventSelector: '.gb-enemy-click-target' },
                    },
                );
            },
        });
    });