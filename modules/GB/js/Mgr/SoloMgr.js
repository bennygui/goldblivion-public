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
        return declare("gb.SoloMgr", null, {
            SOLO_ABILITY_DICE: 1,
            SOLO_ABILITY_DICE_PER_HUMAN: 2,
            SOLO_ABILITY_NUGGET_PER_ELF_1: 3,
            SOLO_ABILITY_NUGGET_PER_ELF_2: 4,
            SOLO_ABILITY_NUGGET_PER_HUMAN_2: 5,
            SOLO_ABILITY_REVEAL_ENEMY: 6,
            SOLO_ABILITY_DESTROY_ENEMY: 7,
            SOLO_ABILITY_NUGGET_1: 8,
            SOLO_ABILITY_NUGGET_2: 9,
            SOLO_ABILITY_NUGGET_3: 10,
            SOLO_ABILITY_NUGGET_4: 11,
            SOLO_ABILITY_NUGGET_5: 12,
            SOLO_ABILITY_NUGGET_10: 13,
            SOLO_ABILITY_MATERIAL_1: 14,
            SOLO_ABILITY_GOLD_1: 15,
            SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD: 16,
            SOLO_ABILITY_DESTROY_PLAYER_NUGGET_1: 17,
            SOLO_ABILITY_DESTROY_PLAYER_NUGGET_2: 18,
            SOLO_ABILITY_DESTROY_PLAYER_NUGGET_3: 19,

            setup(gamedatas) {
                if (gameui.isGameSolo()) {
                    const container = document.querySelector('.gb-area-solo-container');
                    container.classList.remove('bx-hidden');

                    const board = document.querySelector('.gb-solo-board');
                    board.classList.add('gb-solo-noble-' + gamedatas.soloNoble);
                }

                const help = document.querySelector('.gb-solo-board-card-help .gb-component-help');
                help.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const typeIds = [];
                    for (const iconId of gameui.iconList) {
                        const elem = this.getIconCardElem(iconId);
                        if (elem === null) {
                            continue;
                        }
                        for (const e of elem.querySelectorAll('.gb-component[data-type-id]')) {
                            typeIds.push(e.dataset.typeId);
                        }
                    }
                    gameui.componentMgr.showComponentDetailDialog(
                        typeIds,
                        _('Cards under Solo Noble board'),
                        false,
                        true
                    );
                });

                // Icons
                for (const iconId of gameui.iconList) {
                    const elem = this.getIconCounterElem(iconId);
                    if (elem === null) {
                        continue;
                    }
                    gameui.counters.solo.icon[gameui.iconIdToName[iconId]].addTarget(elem);
                }

                // Counters
                gameui.counters.solo.nugget.addTarget(document.querySelector('.bp-solo-board-nugget-count .bx-pill-counter'));
                gameui.counters.solo.material.addTarget(document.querySelector('.bp-solo-board-material-count .bx-pill-counter'));
                gameui.counters.solo.gold.addTarget(document.querySelector('.bp-solo-board-gold-count .bx-pill-counter'));

                for (const c of Object.values(gamedatas.components)) {
                    if (c.locationId == gameui.componentMgr.COMPONENT_LOCATION_ID_SOLO_BOARD) {
                        this.moveToCardToSoloBoard(c.componentId, c.locationPrimaryOrder, c.locationSecondaryOrder, true);
                    }
                }
                this.updateIconCounts(gamedatas.componentCounts.soloIconCounts, true);
                this.updateCounterCounts(gamedatas.componentCounts, true);
                this.updateSoloActionList(gamedatas.soloActionList, true);
                gameui.mainBoardMgr.movePlayerScoreShield(null, gamedatas.componentCounts.soloGoldCount);
            },

            getIconCardElem(icon) {
                return document.querySelector('.gb-solo-board-card-icon-' + icon);
            },

            getIconCounterElem(icon) {
                return document.querySelector('.gb-solo-board-card-icon-' + icon + '-counter');
            },

            updateIconCounts(soloIconCounts, isInstantaneous = false) {
                for (const iconId in soloIconCounts) {
                    const count = soloIconCounts[iconId];
                    gameui.counters.solo.icon[gameui.iconIdToName[iconId]].toValue(count, isInstantaneous);
                }
            },
            
            updateCounterCounts(componentCounts, isInstantaneous = false) {
                gameui.counters.solo.nugget.toValue(componentCounts.soloNuggetCount, isInstantaneous);
                gameui.counters.solo.material.toValue(componentCounts.soloMaterialCount, isInstantaneous);
                gameui.counters.solo.gold.toValue(componentCounts.soloGoldCount, isInstantaneous);
            },

            updateSoloActionList(soloActionList, isInstantaneous = false) {
                const container = document.querySelector('.gb-solo-icons');
                gameui.autoScroll(container, gameui.player_id, isInstantaneous);
                container.innerHTML = '';
                const elemText = (txt) => {
                    const e = document.createElement('div');
                    e.innerText = txt;
                    return e;
                };
                const elemClass = (cl) => {
                    const e = document.createElement('div');
                    e.classList.add(cl);
                    return e;
                };
                for (const id of soloActionList) {
                    switch (id) {
                        case this.SOLO_ABILITY_DICE:
                            container.appendChild(gameui.createSoloDiceElement());
                            break;
                        case this.SOLO_ABILITY_DICE_PER_HUMAN:
                            container.appendChild(gameui.createSoloDiceElement());
                            container.appendChild(elemText('x'));
                            container.appendChild(elemClass('gb-blue-icon-human'));
                            break;
                        case this.SOLO_ABILITY_NUGGET_PER_ELF_1:
                            container.appendChild(gameui.createNuggetElement());
                            container.appendChild(elemText('x'));
                            container.appendChild(elemClass('gb-blue-icon-elf'));
                            break;
                        case this.SOLO_ABILITY_NUGGET_PER_ELF_2:
                            container.appendChild(elemText('2x'));
                            container.appendChild(gameui.createNuggetElement());
                            container.appendChild(elemText('x'));
                            container.appendChild(elemClass('gb-blue-icon-elf'));
                            break;
                        case this.SOLO_ABILITY_NUGGET_PER_HUMAN_2:
                            container.appendChild(elemText('2x'));
                            container.appendChild(gameui.createNuggetElement());
                            container.appendChild(elemText('x'));
                            container.appendChild(elemClass('gb-blue-icon-human'));
                            break;
                        case this.SOLO_ABILITY_NUGGET_1:
                            container.appendChild(gameui.createNuggetElement());
                            break;
                        case this.SOLO_ABILITY_NUGGET_2:
                            container.appendChild(elemText('2x'));
                            container.appendChild(gameui.createNuggetElement());
                            break;
                        case this.SOLO_ABILITY_NUGGET_3:
                            container.appendChild(elemText('3x'));
                            container.appendChild(gameui.createNuggetElement());
                            break;
                        case this.SOLO_ABILITY_NUGGET_4:
                            container.appendChild(elemText('4x'));
                            container.appendChild(gameui.createNuggetElement());
                            break;
                        case this.SOLO_ABILITY_NUGGET_5:
                            container.appendChild(elemText('5x'));
                            container.appendChild(gameui.createNuggetElement());
                            break;
                        case this.SOLO_ABILITY_NUGGET_10:
                            container.appendChild(elemText('10x'));
                            container.appendChild(gameui.createNuggetElement());
                            break;
                        case this.SOLO_ABILITY_MATERIAL_1:
                            container.appendChild(gameui.createMaterialElement());
                            break;
                        case this.SOLO_ABILITY_GOLD_1:
                            container.appendChild(gameui.createGoldElement());
                            break;
                        case this.SOLO_ABILITY_DESTROY_RIGHT_MARKET_CARD:
                            container.appendChild(gameui.createSoloDestroyCardElement());
                            break;
                        case this.SOLO_ABILITY_DESTROY_PLAYER_NUGGET_1:
                            container.appendChild(gameui.createSoloDestroyNuggetElement());
                            break;
                        case this.SOLO_ABILITY_DESTROY_PLAYER_NUGGET_2:
                            container.appendChild(elemText('2x'));
                            container.appendChild(gameui.createSoloDestroyNuggetElement());
                            break;
                        case this.SOLO_ABILITY_DESTROY_PLAYER_NUGGET_3:
                            container.appendChild(elemText('3x'));
                            container.appendChild(gameui.createSoloDestroyNuggetElement());
                            break;
                        case this.SOLO_ABILITY_REVEAL_ENEMY:
                            container.appendChild(gameui.createSoloRevealEnemyElement());
                            break;
                        case this.SOLO_ABILITY_DESTROY_ENEMY:
                            container.appendChild(gameui.createSoloDestroyEnemyElement());
                            break;
                    }
                    container.appendChild(elemClass('gb-solo-icons-separator'));
                }
                return gameui.wait(1000, isInstantaneous);
            },

            moveToCardToSoloBoard(componentId, icon, order, isInstantaneous = false) {
                const componentElem = gameui.componentMgr.getComponentById(componentId);
                const targetElem = this.getIconCardElem(icon);
                componentElem.dataset.soloBoardOrder = order;
                return gameui.slide(componentElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },
        });
    });