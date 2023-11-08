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
        return declare("gb.PlayerBoardMgr", null, {
            MAX_DECK_SIZE: 5,
            MAX_NUGGET_COUNT: 20,
            MAX_MATERIAL_COUNT: 4,
            NUGGET_SCALE: 0.7,
            ANIMATION_DELAY: 50,

            constructor() {
                this.redPowerCounters = {};
                this.redEnemyPowerCounters = {};
                this.componentCounts = null;
                this.playerDevelopmentTypeId = null;
            },

            setup(gamedatas) {
                const combatPowerFormatFct = (currentValue, targetValue) => {
                    const str = currentValue.toString();
                    const container = document.createElement('span');
                    for (let i = 0; i < str.length; ++i) {
                        const number = document.createElement('span');
                        number.classList.add('gb-combat-number');
                        number.dataset.number = str[i];
                        container.appendChild(number);
                    }
                    return container.outerHTML;
                };

                this.componentCounts = gamedatas.componentCounts;
                this.playerDevelopmentTypeId = gamedatas.playerDevelopmentTypeId;
                for (const playerId in gamedatas.players) {
                    gameui.counters[playerId].deckBlue.addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-player-deck-blue-count')
                    );
                    gameui.counters[playerId].deckRed.addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-player-deck-red-count')
                    );

                    // Combat: player
                    this.redPowerCounters[playerId] = new bx.Numbers();
                    this.redPowerCounters[playerId].addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-red-power-counter')
                    );
                    this.redPowerCounters[playerId].setFormatOneFunction(combatPowerFormatFct);
                    gameui.addBasicTooltipToElement(this.getPlayerAreaElem(playerId).querySelector('.gb-red-power-counter'), _('Player Combat Power'));

                    // Combat: enemy
                    this.redEnemyPowerCounters[playerId] = new bx.Numbers();
                    this.redEnemyPowerCounters[playerId].addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-red-enemy-power-counter')
                    );
                    this.redEnemyPowerCounters[playerId].setFormatOneFunction(combatPowerFormatFct);
                    gameui.addBasicTooltipToElement(this.getPlayerAreaElem(playerId).querySelector('.gb-red-enemy-power-counter'), _('Enemy Combat Power'));

                    gameui.counters[playerId].development[0].addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-development-side-0-counter')
                    );
                    gameui.counters[playerId].development[0].setFormatMultipleFunction((formattedValues, currentValues) => {
                        return formattedValues[0];
                    });
                    gameui.counters[playerId].development[1].addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-development-side-1-counter')
                    );
                    gameui.counters[playerId].development[1].setFormatMultipleFunction((formattedValues, currentValues) => {
                        if (currentValues[0] == 0) {
                            return formattedValues[0];
                        } else {
                            return formattedValues[0] + ' (' + formattedValues[1] + ')';
                        }
                    });
                    gameui.counters[playerId].nugget.addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-nugget-counter')
                    );
                    gameui.counters[playerId].material.addTarget(
                        this.getPlayerAreaElem(playerId).querySelector('.gb-material-counter')
                    );

                    // Icons
                    for (const info of gameui.getBlueIcons()) {
                        gameui.counters[playerId].icon[info[0]].addTarget(
                            this.getPlayerIconCounterElem(playerId, info[0])
                        );
                        gameui.addBasicTooltipToElement(this.getPlayerIconCounterElem(playerId, info[0]).parentElement, info[1]);
                    }

                    // Deck help
                    this.getPlayerAreaElem(playerId).querySelector('.gb-player-deck-red-help').addEventListener('click', (e) => {
                        e.stopPropagation();
                        const typeList = this.getPlayerCardRedTypeList(playerId);
                        gameui.componentMgr.showComponentDetailDialog(
                            typeList[1],
                            playerId == gameui.player_id
                                ? _('Combat cards in your deck')
                                : _('Combat cards in deck for player:') + ' ' + gameui.createPlayerColorNameElement(playerId).outerHTML,
                            false,
                            true,
                            playerId == gameui.player_id
                                ? _('Your Combat cards in play')
                                : _('Combat cards in play for player:') + ' ' + gameui.createPlayerColorNameElement(playerId).outerHTML,
                            typeList[0]
                        );
                    });
                    this.getPlayerAreaElem(playerId).querySelector('.gb-player-deck-blue-help').addEventListener('click', (e) => {
                        e.stopPropagation();
                        const typeList = this.getPlayerCardBlueTypeList(playerId);
                        gameui.componentMgr.showComponentDetailDialog(
                            typeList[1],
                            playerId == gameui.player_id
                                ? _('GOLDblivion cards in your deck')
                                : _('GOLDblivion cards in deck/hand/development for player:') + ' ' + gameui.createPlayerColorNameElement(playerId).outerHTML,
                            false,
                            true,
                            playerId == gameui.player_id
                                ? _('Your GOLDblivion cards in hand/in play')
                                : _('GOLDblivion cards in play for player:') + ' ' + gameui.createPlayerColorNameElement(playerId).outerHTML,
                            typeList[0]
                        );
                    });

                    // Development help (for current player only)
                    if (playerId == gameui.player_id) {
                        const devHelp = this.getPlayerAreaElem(playerId).querySelector('.gb-development-help');
                        const help = document.createElement('div');
                        help.classList.add('gb-component-help')
                        devHelp.appendChild(help);
                        help.addEventListener('click', (e) => {
                            e.stopPropagation();
                            gameui.componentMgr.showComponentDetailDialog(
                                this.playerDevelopmentTypeId,
                                _('Your cards in Development'),
                                false,
                                true
                            );
                        });
                    }
                }

                // Discarded list
                document.getElementById('gb-discarded-help').addEventListener('click', (e) => {
                    e.stopPropagation();
                    gameui.componentMgr.showComponentDetailDialog(
                        this.componentCounts.discardedTypeList,
                        _('Destroyed cards'),
                        false,
                        true
                    );
                });

                // Compact switch
                for (const e of document.querySelectorAll('.gb-switch-compact-blue input')) {
                    e.addEventListener('change', () => {
                        gameui.setLocalPreference(gameui.GB_PREF_COMPACT_BLUE_ID, e.checked);
                    });
                }
                for (const e of document.querySelectorAll('.gb-switch-compact-red-0 input')) {
                    e.addEventListener('change', () => {
                        gameui.setLocalPreference(gameui.GB_PREF_COMPACT_RED_0_ID, e.checked);
                    });
                }
                for (const e of document.querySelectorAll('.gb-switch-compact-red-1 input')) {
                    e.addEventListener('change', () => {
                        gameui.setLocalPreference(gameui.GB_PREF_COMPACT_RED_1_ID, e.checked);
                    });
                }

                this.setupShield(gamedatas);
                this.updateComponentCounts(gamedatas.componentCounts, true);
                for (const playerId in gamedatas.playerStates) {
                    const ps = gamedatas.playerStates[playerId];
                    this.updateNuggetCount(ps.playerId, ps.nuggetCount, null, true);
                    this.updateMaterialCount(ps.playerId, ps.materialCount, null, true);
                }

                for (const c of Object.values(gamedatas.components)) {
                    if (gameui.componentMgr.typeIdIsCardBlue(c.typeId)
                        && c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA) {
                        this.moveToCardBluePlayerPlayArea(c.componentId, c.playerId, c.locationPrimaryOrder, true);
                    } else if (gameui.componentMgr.typeIdIsCardRed(c.typeId)
                        && c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA) {
                        this.moveToCardRedPlayerPlayArea(c.componentId, c.playerId, c.locationPrimaryOrder, c.locationSecondaryOrder, true);
                    } else if (c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING) {
                        this.moveToCardBuildingPlayerPlayArea(c.componentId, c.playerId, c.locationPrimaryOrder, true);
                    } else if (c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_BOARD) {
                        if (gameui.componentMgr.typeIdIsEnemy(c.typeId)) {
                            this.moveToPlayerBoardEnemy(c.componentId, c.playerId, c.locationPrimaryOrder, true);
                        } else {
                            this.moveToPlayerBoardMagic(c.componentId, c.playerId, c.locationPrimaryOrder, true);
                        }
                    }
                }
            },

            setupShield(gamedatas) {
                for (const playerId in gamedatas.players) {
                    const shieldElem = this.getPlayerAreaElem(playerId).querySelector('.gb-player-board-shield .gb-shield');
                    shieldElem.classList.add(gamedatas.players[playerId].player_color_name);
                }
            },

            updateComponentCounts(componentCounts, isInstantaneous = false) {
                this.componentCounts = componentCounts;
                this.updateCardBluePlayerDeckCounts(componentCounts.cardBluePlayerDeckCounts, isInstantaneous);
                this.updateCardRedPlayerDeckCounts(componentCounts.cardRedPlayerDeckCounts, isInstantaneous);
                this.updateDevelopmentCounts(componentCounts.developmentCounts, isInstantaneous);
                this.updateMagicCounts(componentCounts.magicPlayerBoardCounts, isInstantaneous);
                this.updateIconCounts(componentCounts.iconCounts, isInstantaneous);
                this.updateCombatPowers(componentCounts.combatPowers, componentCounts.enemyCombatPowers, isInstantaneous);
            },

            updatePlayerDevelopmentTypeId(playerDevelopmentTypeId) {
                this.playerDevelopmentTypeId = playerDevelopmentTypeId;
            },

            getPlayerCardBlueTypeList(playerId) {
                let typeList = null;
                let visibleElement = Array.from(document.querySelectorAll('#gb-area-player-' + playerId + ' .gb-component.gb-card-blue'));
                if (playerId == gameui.player_id) {
                    typeList = Array.from(this.componentCounts.cardBlueTypeList[playerId]);
                    for (const devId of this.playerDevelopmentTypeId) {
                        const idx = typeList.indexOf(devId);
                        if (idx >= 0) {
                            typeList.splice(idx, 1);
                        }
                    }
                    visibleElement = visibleElement.concat(Array.from(document.querySelectorAll('#gb-area-card-hand .gb-component.gb-card-blue')));
                } else {
                    typeList = Array.from(this.componentCounts.cardBlueTypeList[playerId]);
                }
                const visibleList = [];
                for (const e of visibleElement) {
                    if (!e.dataset.typeId) {
                        continue;
                    }
                    const idx = typeList.indexOf(e.dataset.typeId);
                    if (idx >= 0) {
                        typeList.splice(idx, 1);
                        visibleList.push(e.dataset.typeId)
                    }
                }
                return [visibleList, typeList];
            },

            getPlayerCardRedTypeList(playerId) {
                const typeList = Array.from(this.componentCounts.cardRedTypeList[playerId]);
                const visibleElement = Array.from(document.querySelectorAll('#gb-area-player-' + playerId + ' .gb-component.gb-card-red'));
                const visibleList = [];
                for (const e of visibleElement) {
                    if (!e.dataset.typeId) {
                        continue;
                    }
                    const idx = typeList.indexOf(e.dataset.typeId);
                    if (idx >= 0) {
                        typeList.splice(idx, 1);
                        visibleList.push(e.dataset.typeId)
                    }
                }
                return [visibleList, typeList];
            },

            getPlayerAreaElem(playerId) {
                return document.getElementById('gb-area-player-' + playerId);
            },

            getPlayerIconCounterElem(playerId, icon) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-blue-icon-' + icon).parentElement.querySelector('.bx-pill-counter');
            },

            getCardBluePlayerDeckContainerElem(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-player-deck-blue-container');
            },

            updateCardBluePlayerDeckCounts(cardBluePlayerDeckCounts, isInstantaneous = false) {
                for (const playerId in cardBluePlayerDeckCounts) {
                    const count = cardBluePlayerDeckCounts[playerId];
                    gameui.counters[playerId].deckBlue.toValue(count, isInstantaneous);
                    gameui.componentMgr.updateFaceDownSupply(
                        count,
                        this.getCardBluePlayerDeckContainerElem(playerId),
                        gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_BLUE,
                        1,
                        this.MAX_DECK_SIZE
                    );
                }
            },

            getCardRedPlayerDeckContainerElem(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-player-deck-red-container');
            },

            getTopRedDeckForPlayerId(playerId) {
                return this.getCardRedPlayerDeckContainerElem(playerId).querySelector('.gb-component:last-child');
            },

            updateCardRedPlayerDeckCounts(cardRedPlayerDeckCounts, isInstantaneous = false) {
                for (const playerId in cardRedPlayerDeckCounts) {
                    const count = cardRedPlayerDeckCounts[playerId];
                    gameui.counters[playerId].deckRed.toValue(count, isInstantaneous);
                    gameui.componentMgr.updateFaceDownSupply(
                        count,
                        this.getCardRedPlayerDeckContainerElem(playerId),
                        gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_RED_DECK,
                        1,
                        this.MAX_DECK_SIZE,
                        gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_RED
                    );
                }
            },

            moveToCardBluePlayerDeckAndDestroy(componentId, playerId) {
                return this.moveToCardBluePlayerDeck(componentId, playerId).then(() => {
                    const componentElem = gameui.componentMgr.getComponentById(componentId);
                    componentElem.remove();
                });
            },

            moveToCardBluePlayerDeck(componentId, playerId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getCardBluePlayerDeckContainerElem(playerId);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            shuffleBlueDeck(playerId) {
                const targetElem = this.getCardBluePlayerDeckContainerElem(playerId);
                return gameui.componentMgr.suffleDeck(targetElem, gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_BLUE);
            },

            moveToCardRedPlayerDeckAndDestroy(componentId, playerId) {
                return this.moveToCardRedPlayerDeck(componentId, playerId).then(() => {
                    const componentElem = gameui.componentMgr.getComponentById(componentId);
                    componentElem.remove();
                }).then(() => this.sortAndResizeRedPlayerPlayArea(playerId));
            },

            moveToCardRedPlayerDeck(componentId, playerId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getCardRedPlayerDeckContainerElem(playerId);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            shuffleRedDeck(playerId) {
                const targetElem = this.getCardRedPlayerDeckContainerElem(playerId);
                return gameui.componentMgr.suffleDeck(targetElem, gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_RED);
            },

            getPlayerBoardDevelop(playerId, side) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-development-side-' + side);
            },

            moveToPlayerDevelopment(componentId, side, playerId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerBoardDevelop(playerId, side);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveToPlayerDevelopmentAndDestroy(componentId, side, playerId) {
                return this.moveToPlayerDevelopment(componentId, side, playerId).then(() => {
                    const componentElem = gameui.componentMgr.getComponentById(componentId);
                    componentElem.remove();
                });
            },

            updateDevelopmentCounts(developmentCounts, isInstantaneous = false) {
                for (const side in developmentCounts) {
                    for (const playerId in developmentCounts[side]) {
                        const count = developmentCounts[side][playerId]
                        gameui.counters[playerId].development[side].toValue([count, count / 2], isInstantaneous);
                        gameui.componentMgr.updateFaceDownSupply(
                            count,
                            this.getPlayerBoardDevelop(playerId, side),
                            gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_BLUE,
                            0,
                            this.MAX_DECK_SIZE
                        );
                    }
                }
            },

            getPlayerNuggetBox(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-nugget-box');
            },

            updateNuggetCount(playerId, nuggetCount, from, isInstantaneous = false) {
                const prevNuggetCount = gameui.counters[playerId].nugget.getValues();
                if (nuggetCount == prevNuggetCount) {
                    return Promise.resolve();
                }
                const nuggetBoxElem = this.getPlayerNuggetBox(playerId);
                const movements = this.moveTokens(
                    nuggetCount - prevNuggetCount,
                    nuggetBoxElem,
                    from,
                    this.MAX_NUGGET_COUNT,
                    () => gameui.createNuggetElement()
                );
                return Promise.all(movements).then(() => {
                    gameui.counters[playerId].nugget.toValue(nuggetCount, isInstantaneous);
                    for (const e of nuggetBoxElem.querySelectorAll('.gb-nugget')) {
                        e.remove();
                    }
                    for (let i = 0; i < Math.min(nuggetCount, this.MAX_NUGGET_COUNT); ++i) {
                        const nuggetElem = gameui.createNuggetElement();
                        nuggetBoxElem.appendChild(nuggetElem);
                        nuggetElem.style.position = 'absolute';
                        nuggetElem.style.transform = 'scale(' + this.NUGGET_SCALE + ') rotate(' + (Math.random() * 360) + 'deg)';
                        nuggetElem.style.top = Math.floor(Math.random() * (nuggetBoxElem.offsetHeight - nuggetElem.offsetHeight * this.NUGGET_SCALE)) + 'px';
                        nuggetElem.style.left = Math.floor(Math.random() * (nuggetBoxElem.offsetWidth - nuggetElem.offsetWidth * this.NUGGET_SCALE)) + 'px';
                    }
                });
            },

            getPlayerMaterialBox(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-material-box');
            },

            updateMaterialCount(playerId, materialCount, from, isInstantaneous = false) {
                const materialBoxElem = this.getPlayerMaterialBox(playerId);
                const prevMaterialCount = gameui.counters[playerId].material.getValues();
                const movements = this.moveTokens(
                    materialCount - prevMaterialCount,
                    materialBoxElem,
                    from,
                    this.MAX_MATERIAL_COUNT,
                    () => gameui.createMaterialElement()
                );
                return Promise.all(movements).then(() => {
                    gameui.counters[playerId].material.toValue(materialCount, isInstantaneous);
                    for (const e of materialBoxElem.querySelectorAll('.gb-material')) {
                        e.remove();
                    }
                    for (let i = 0; i < Math.min(materialCount, this.MAX_MATERIAL_COUNT); ++i) {
                        const materialElem = gameui.createMaterialElement();
                        materialBoxElem.appendChild(materialElem);
                    }
                });
            },

            moveTokens(nbToken, boxElem, from, max, createTokenFct) {
                const movements = []
                if (nbToken == 0 || !from) {
                    return movements;
                }
                const fromElement = nbToken < 0
                    ? boxElem
                    : gameui.getFromElement(from);
                const toElement = nbToken < 0
                    ? gameui.getFromElement(from)
                    : boxElem;
                if (fromElement === null || toElement === null) {
                    return movements;
                }
                for (let i = 0; i < Math.min(Math.abs(nbToken), max); ++i) {
                    const e = createTokenFct();
                    e.style.position = 'relative';
                    fromElement.appendChild(e);
                    gameui.placeOnObject(e, fromElement);
                    movements.push(
                        gameui.slide(e, toElement, {
                            delay: i * this.ANIMATION_DELAY,
                        }).then(
                            () => e.remove()
                        )
                    );
                }
                return movements;
            },

            updateIconCounts(iconCounts, isInstantaneous = false) {
                for (const playerId in iconCounts) {
                    for (const iconId in iconCounts[playerId]) {
                        const count = iconCounts[playerId][iconId];
                        gameui.counters[playerId].icon[gameui.iconIdToName[iconId]].toValue(count, isInstantaneous);
                    }
                }
            },

            getPlayerBoardOtherHand(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-player-board');
            },

            moveToOtherPlayerHand(componentId, playerId, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerBoardOtherHand(playerId);
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveToOtherPlayerHandAndDestroy(componentId, playerId) {
                return this.moveToOtherPlayerHand(componentId, playerId).then(() => {
                    const componentElem = gameui.componentMgr.getComponentById(componentId);
                    componentElem.remove();
                });
            },

            getPlayerBluePlayArea(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-blue-played-container');
            },

            getPlayerBuildingPlayArea(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-blue-building-container');
            },

            moveToCardBluePlayerPlayArea(componentId, playerId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerBluePlayArea(playerId);
                componentElem.dataset.cardBluePlayAreaOrder = order;
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => this.sortAndResizeBluePlayerPlayArea(playerId));
            },

            moveToCardBuildingPlayerPlayArea(componentId, playerId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerBuildingPlayArea(playerId)
                componentElem.dataset.cardBluePlayAreaOrder = order;
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => this.sortAndResizeBuildingPlayerPlayArea(playerId));
            },

            sortAndResizeBluePlayerPlayArea(playerId) {
                return gameui.componentMgr.sortAndResizeComponentsWithOverlap(
                    this.getPlayerBluePlayArea(playerId),
                    gameui.CARD_BLUE_HEIGHT,
                    (c1, c2) => {
                        return (c1.dataset.cardBluePlayAreaOrder - c2.dataset.cardBluePlayAreaOrder);
                    }
                );
            },

            sortAndResizeBuildingPlayerPlayArea(playerId) {
                return gameui.componentMgr.sortAndResizeComponentsWithOverlap(
                    this.getPlayerBuildingPlayArea(playerId),
                    gameui.CARD_BLUE_HEIGHT,
                    (c1, c2) => {
                        return (c1.dataset.cardBluePlayAreaOrder - c2.dataset.cardBluePlayAreaOrder);
                    }
                );
            },

            getPlayerRedPlayArea(playerId, containerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-red-played-container-' + containerId);
            },

            moveToCardRedPlayerPlayArea(componentId, playerId, containerId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerRedPlayArea(playerId, containerId);
                componentElem.dataset.cardRedPlayAreaOrder = order;
                if (containerId == 0) {
                    gameui.autoScroll(targetElem, playerId, isInstantaneous);
                }
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => this.sortAndResizeRedPlayerPlayArea(playerId));
            },

            sortAndResizeRedPlayerPlayArea(playerId) {
                const mouvements = [];
                for (let i = 0; i < 2; ++i) {
                    mouvements.push(gameui.componentMgr.sortAndResizeComponentsWithOverlap(
                        this.getPlayerRedPlayArea(playerId, i),
                        gameui.CARD_RED_HEIGHT,
                        (c1, c2) => {
                            return (c1.dataset.cardRedPlayAreaOrder - c2.dataset.cardRedPlayAreaOrder);
                        }
                    ));
                }
                return Promise.all(mouvements);
            },

            getPlayerBoardMagicArea(playerId) {
                return this.getPlayerAreaElem(playerId).querySelector('.gb-player-magic-container');
            },

            moveToPlayerBoardMagic(componentId, playerId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerBoardMagicArea(playerId)
                componentElem.dataset.magicPlayAreaOrder = order;
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getPlayerBoardEnemyArea(playerId) {
                if (playerId === null) {
                    return document.querySelector('.gb-area-solo-container .gb-player-enemy-container');
                }
                return this.getPlayerAreaElem(playerId).querySelector('.gb-player-enemy-container');
            },

            moveToPlayerBoardEnemy(componentId, playerId, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getPlayerBoardEnemyArea(playerId)
                componentElem.dataset.enemyPlayAreaOrder = order;
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            updateMagicCounts(magicPlayerBoardCounts, isInstantaneous = false) {
                for (const playerId in magicPlayerBoardCounts) {
                    if (playerId == gameui.player_id) {
                        continue;
                    }
                    const count = magicPlayerBoardCounts[playerId];
                    const containerElem = this.getPlayerBoardMagicArea(playerId);
                    gameui.componentMgr.updateFaceDownSupply(
                        count,
                        containerElem,
                        gameui.componentMgr.COMPONENT_TYPE_ID_BACK_MAGIC
                    );
                    let i = 0;
                    for (const e of containerElem.querySelectorAll('.gb-component')) {
                        e.dataset.magicPlayAreaOrder = i;
                        ++i;
                    }
                }
            },

            updateCombatPowers(combatPowers, enemyCombatPowers, isInstantaneous = false) {
                for (const playerId in combatPowers) {
                    this.redPowerCounters[playerId].toValue(combatPowers[playerId], isInstantaneous);
                    this.redEnemyPowerCounters[playerId].toValue(enemyCombatPowers[playerId], isInstantaneous);
                }
            },
        });
    });
