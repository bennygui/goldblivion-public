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
        return declare("gb.DraftMgr", null, {
            setup(gamedatas) {
                for (const c of Object.values(gamedatas.components)) {
                    if (c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_DRAFT_MARKET) {
                        this.moveComponentToDraft(c.componentId, c.locationPrimaryOrder, true);
                    }
                }
            },

            getDraftContainerElem() {
                return document.getElementById('gb-area-card-draft-container');
            },

            moveComponentToDraft(componentId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getDraftContainerElem();
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => {
                    componentElem.style.setProperty('--gb-draft-market-order', parseInt(order));
                });
            },
        });
    });