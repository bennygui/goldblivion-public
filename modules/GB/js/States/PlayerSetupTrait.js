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
        return declare("gb.PlayerSetupTrait", null, {
            onButtonsStatePlayerSetupChooseNoble(args) {
                debug('onButtonsStatePlayerSetupChooseNoble');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Choose Noble'),
                    _('You must choose a Noble card'),
                    {
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getComponentById(id),
                        onClick: (id) => {
                            this.showConfirmDialogIfConfirm().then(() => {
                                this.serverAction('playerSetupChooseNoble', { componentId: id })
                            });
                        },
                    },
                );
            },
        });
    });