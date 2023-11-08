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
    g_gamethemeurl + "modules/BX/js/Numbers.js",
],
    function (dojo, declare) {
        return declare("gb.MainBoardMgr", null, {
            MAX_DECK_SIZE_BLUE: 10,
            MAX_DECK_SIZE_RED: 5,

            constructor() {
                this.cardBlueCounter = new bx.Numbers();
                this.cardRedCounters = [new bx.Numbers(), new bx.Numbers()];
                this.scoreCounters = {};
            },

            setup(gamedatas) {
                this.cardBlueCounter.addTarget(this.getCardBlueSupplyCountElem());
                for (let i = 0; i <= 1; ++i) {
                    this.cardRedCounters[i].addTarget(this.getCardRedSupplyCountElem(i));
                }

                for (const playerId in gamedatas.players) {
                    this.createScoreShield(playerId);
                    this.movePlayerScoreShield(playerId, gamedatas.players[playerId].score, true);
                }
                if (gameui.isGameSolo()) {
                    this.createScoreShield(null);
                    this.movePlayerScoreShield(null, gameui.counters.solo.gold.getValues(), true);
                }

                for (const c of Object.values(gamedatas.components)) {
                    if (gameui.componentMgr.typeIdIsCardBlue(c.typeId)
                        && c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_MARKET) {
                        this.moveComponentToCardBlueMarket(c.componentId, c.locationPrimaryOrder, true);
                    } else if (gameui.componentMgr.typeIdIsCardRed(c.typeId)
                        && c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_MARKET) {
                        this.moveComponentToCardRedMarket(c.componentId, c.locationPrimaryOrder, true);
                    } else if (gameui.componentMgr.typeIdIsVillage(c.typeId)
                        && c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_MARKET) {
                        this.moveComponentToVillageMarket(c.componentId, c.locationPrimaryOrder, true);
                    } else if (gameui.componentMgr.typeIdIsEnemy(c.typeId)
                        && c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_MARKET) {
                        this.moveComponentToEnemyMarket(c.componentId, c.locationPrimaryOrder, true);
                    } else if (c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_SUPPLY_VISIBLE) {
                        this.moveComponentToMagicSupply(c.componentId, true);
                    }
                }
                for (const ps of Object.values(gamedatas.playerStates)) {
                    if (ps.combatEnemyComponentId !== null) {
                        this.selectEnemy(ps.combatEnemyComponentId, true);
                        break;
                    }
                }
                this.updateMagicCount(gamedatas.componentCounts.magicCount, true);
                this.updateCardBlueCount(gamedatas.componentCounts.cardBlueCount, true);
                this.updateCardRedCounts(gamedatas.componentCounts.cardRedCounts, true);
                this.updateEnemyCounts(gamedatas.componentCounts.enemyCounts, true);
            },

            createScoreShield(playerId) {
                let shield = null
                if (playerId === null) {
                    shield = gameui.createShieldElementFromColorName(gameui.gamedatas.soloNobleColorName);
                    shield.id = 'gb-score-shield-solo';
                } else {
                    shield = gameui.createShieldElement(playerId);
                    shield.id = 'gb-score-shield-' + playerId;
                    shield.style.order = gameui.gamedatas.players[playerId].player_no;
                }

                const counter = document.createElement('div');
                counter.classList.add('gb-counter');
                counter.classList.add('bx-hidden');

                shield.appendChild(counter);

                const elemCreationElem = gameui.getElementCreationElement();
                elemCreationElem.appendChild(shield);

                if (playerId === null) {
                    gameui.counters.solo.gold.addTarget(counter);
                } else {
                    this.scoreCounters[playerId] = new bx.Numbers();
                    this.scoreCounters[playerId].addTarget(counter);
                }
            },

            getScoreShieldContainerElem(score) {
                score = parseInt(score);
                if (score > 10) {
                    score = (score % 10);
                    if (score == 0) {
                        score = 10;
                    }
                }
                return document.getElementById('gb-score-container-' + score);
            },

            movePlayerScoreShield(playerId, score, isInstantaneous = false) {
                const componentElem =
                    playerId === null
                        ? document.getElementById('gb-score-shield-solo')
                        : document.getElementById('gb-score-shield-' + playerId);
                if (componentElem === null) {
                    return Promise.resolve();
                }
                const counterElem = componentElem.querySelector('.gb-counter');
                if (score < 0) {
                    score = 0;
                }
                if (score > 10) {
                    counterElem.classList.remove('bx-hidden');
                } else {
                    counterElem.classList.add('bx-hidden');
                }
                if (playerId !== null) {
                    this.scoreCounters[playerId].toValue(score, isInstantaneous);
                }
                const targetElem = this.getScoreShieldContainerElem(score);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getMagicSupplyContainerElem() {
                return document.getElementById('gb-magic-supply-container');
            },

            updateMagicCount(magicCount, isInstantaneous = false) {
                gameui.componentMgr.updateFaceDownSupply(
                    magicCount,
                    this.getMagicSupplyContainerElem(),
                    gameui.componentMgr.COMPONENT_TYPE_ID_BACK_MAGIC
                );
            },

            moveComponentToMagicSupply(componentId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getMagicSupplyContainerElem();
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getCardBlueSupplyCountElem() {
                return document.getElementById('gb-card-blue-supply-count');
            },

            getCardBlueSupplyContainerElem() {
                return document.getElementById('gb-card-blue-supply-container');
            },

            updateCardBlueCount(cardBlueCount, isInstantaneous = false) {
                this.cardBlueCounter.toValue(cardBlueCount, isInstantaneous);
                gameui.componentMgr.updateFaceDownSupply(
                    cardBlueCount,
                    this.getCardBlueSupplyContainerElem(),
                    gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_BLUE,
                    1,
                    this.MAX_DECK_SIZE_BLUE
                );
            },

            getCardRedSupplyCountElem(side) {
                return document.getElementById('gb-card-red-supply-count-' + side);
            },

            getCardRedSupplyContainerElem(side) {
                return document.getElementById('gb-card-red-supply-container-' + side);
            },

            updateCardRedCounts(cardRedCounts, isInstantaneous = false) {
                for (let i = 0; i <= 1; ++i) {
                    this.cardRedCounters[i].toValue(cardRedCounts[i], isInstantaneous);
                    gameui.componentMgr.updateFaceDownSupply(
                        parseInt(cardRedCounts[i]) - 1,
                        this.getCardRedSupplyContainerElem(i),
                        gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_RED_DECK,
                        0,
                        this.MAX_DECK_SIZE_RED
                    );
                }
            },

            getEnemySupplyContainerElem(id) {
                return document.getElementById('gb-enemy-supply-container-' + id);
            },

            updateEnemyCounts(enemyCounts, isInstantaneous = false) {
                for (const id in enemyCounts) {
                    gameui.componentMgr.updateFaceDownSupply(
                        enemyCounts[id],
                        this.getEnemySupplyContainerElem(id),
                        gameui.gamedatas.enemyMapDefs[id].isForest
                            ? gameui.componentMgr.COMPONENT_TYPE_ID_BACK_ENEMY_FOREST
                            : gameui.componentMgr.COMPONENT_TYPE_ID_BACK_ENEMY_MOUNTAIN
                    );
                }
            },

            moveComponentToEnemyMarket(componentId, locationId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getEnemySupplyContainerElem(locationId);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            flipEnemy(locationId, component) {
                const fromElem = this.getEnemySupplyContainerElem(locationId).querySelector('.gb-component:last-child');
                const toElem = gameui.componentMgr.createComponentElem(component.typeId, component.componentId);
                if (fromElem === null) {
                    const elemCreationElem = gameui.getElementCreationElement();
                    elemCreationElem.appendChild(toElem);
                    return this.moveComponentToEnemyMarket(component.componentId, component.locationPrimaryOrder, true);
                }
                return gameui.flipAndReplace(fromElem, toElem);
            },

            getCardBlueMarketContainerElem() {
                return document.getElementById('gb-card-blue-market-container');
            },

            moveComponentToCardBlueMarket(componentId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getCardBlueMarketContainerElem();
                componentElem.dataset.cardBlueMarketOrder = order;
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getCardRedMarketContainerElem(side) {
                return document.getElementById('gb-card-red-supply-container-' + side);
            },

            moveComponentToCardRedMarket(componentId, side, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getCardRedMarketContainerElem(side);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getVillageContainerElem(villageSide) {
                return document.getElementById('gb-village-container-' + villageSide);
            },

            moveComponentToVillageMarket(componentId, villageSide, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getVillageContainerElem(villageSide);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            flipVillage(fromId, toId, toTypeId) {
                const fromElem = gameui.componentMgr.getComponentById(fromId);
                if (fromElem === null) {
                    return Promise.resolve();
                }
                const toElem = gameui.componentMgr.createComponentElem(toTypeId, toId);
                return gameui.flipAndReplace(fromElem, toElem, false);
            },

            moveComponentToCardBlueSupply(componentId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getCardBlueSupplyContainerElem();
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getEnemyByLocationId(locationId) {
                return this.getEnemySupplyContainerElem(locationId).querySelector('.gb-component:last-child');
            },

            selectEnemy(combatEnemyComponentId, isInstantaneous = false) {
                return gameui.wait(1, isInstantaneous).then(() => {
                    for (const e of document.querySelectorAll('.gb-selected-enemy')) {
                        e.classList.remove('gb-selected-enemy');
                    }
                    if (combatEnemyComponentId !== null) {
                        const componentElem = gameui.componentMgr.getComponentById(combatEnemyComponentId);
                        if (componentElem.dataset.typeId != gameui.PERMANENT_ENEMY_TYPE_ID) {
                            componentElem.classList.add('gb-selected-enemy');
                        }
                    }
                });
            },
        });
    });