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
    g_gamethemeurl + "modules/BX/js/DragScroller.js",
],
    function (dojo, declare) {
        return declare("gb.HandMgr", null, {
            constructor() {
                this.handOrder = {};
            },

            setup(gamedatas) {
                if (gameui.isSpectator) {
                    this.getHandContainerElem().classList.add('bx-hidden');
                }
                this.dragScroller = new bx.DragScroller(this.getHandContainerElem());
                this.handOrder = gamedatas.playerHandOrder;
                for (const c of Object.values(gamedatas.components)) {
                    if (c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_HAND) {
                        this.moveToCardPlayerHand(c.componentId, c.locationPrimaryOrder, true);
                    }
                }
            },

            getHandContainerElem() {
                return document.getElementById('gb-area-card-hand');
            },

            updateHandEmptyElem() {
                const elem = document.getElementById('gb-area-card-hand-empty');
                if (this.getHandContainerElem().querySelector('.gb-component') === null) {
                    elem.classList.remove('bx-hidden');
                } else {
                    elem.classList.add('bx-hidden');
                }
            },

            moveToCardPlayerHand(componentId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getHandContainerElem();
                const realOrder = (componentId in this.handOrder)
                    ? parseInt(this.handOrder[componentId])
                    : 1000 + parseInt(order);
                componentElem.style.setProperty('--gb-hand-order', realOrder);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => {
                    this.updateHandEmptyElem();
                });
            },

            updateHandOrder(handOrder) {
                this.handOrder = handOrder;
                for (const componentId in this.handOrder) {
                    const componentElem = gameui.componentMgr.getComponentById(componentId);
                    if (componentElem === null) {
                        continue;
                    }
                    componentElem.style.setProperty('--gb-hand-order', parseInt(this.handOrder[componentId]));
                }
                return Promise.resolve();
            },
        });
    });