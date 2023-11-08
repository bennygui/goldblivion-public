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
        return declare("gb.NotificationTrait", null, {
            UPDATE_COMPONENT_DELAY: 50,

            constructor() {
                // Format: ['notif', delay]
                if (this.notificationsToRegister === undefined) {
                    this.notificationsToRegister = [];
                }
                this.notificationsToRegister.push(['NTF_UPDATE_COMPONENTS', -1]);
                this.notificationsToRegister.push(['NTF_SHUFFLE_BLUE_DECK', -1]);
                this.notificationsToRegister.push(['NTF_SHUFFLE_RED_DECK', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_COUNTS', 1]);
                this.notificationsToRegister.push(['NTF_UPDATE_NUGGET', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_MATERIAL', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_PASS', null]);
                this.notificationsToRegister.push(['NTF_LAST_ROUND', null]);
                this.notificationsToRegister.push(['NTF_UPDATE_FIRST_PLAYER', -1]);
                this.notificationsToRegister.push(['NTF_FLIP_VILLAGE', -1]);
                this.notificationsToRegister.push(['NTF_USE_COMPONENTS', -1]);
                this.notificationsToRegister.push(['NTF_ROLL_DICE', -1]);
                this.notificationsToRegister.push(['NTF_DESTROY_DICE', -1]);
                this.notificationsToRegister.push(['NTF_FLIP_ENEMY', -1]);
                this.notificationsToRegister.push(['NTF_SELECT_ENEMY', -1]);
                this.notificationsToRegister.push(['NTF_ATTACK_ENEMY', -1]);
                this.notificationsToRegister.push(['NTF_COMBAT_STATUS', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_PLAYER_DEVELOPMENT_TYPE_ID', null]);
                this.notificationsToRegister.push(['NTF_UPDATE_HAND_ORDER', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_ROUND', null]);
                this.notificationsToRegister.push(['NTF_UPDATE_SOLO_ACTION_LIST', -1]);
            },

            notif_UpdateComponents(args) {
                debug('notif_UpdateComponents');
                debug(args);
                const movements = [];
                let delay = this.UPDATE_COMPONENT_DELAY;
                const slideDuration = this.getDefaultSlideDuration()
                if (this.isTrue(args.args.fast)) {
                    this.setDefaultSlideDuration(slideDuration / 2);
                    delay = delay / 5;
                }
                for (const c of args.args.components) {
                    const sortFct = () => {
                        this.handMgr.updateHandEmptyElem();
                        if (c.playerId) {
                            this.playerBoardMgr.sortAndResizeBluePlayerPlayArea(c.playerId);
                        }
                    };
                    const fct = () => this.updateComponent(c, args.args.from).then(sortFct);
                    const move = (movements.length == 0)
                        ? fct
                        : () => movements[movements.length - 1].then(() => this.wait(delay).then(fct));
                    movements.push(move());
                }
                Promise.all(movements).then(() => {
                    if (this.isTrue(args.args.fast)) {
                        this.setDefaultSlideDuration(slideDuration);
                    }
                    this.notifqueue.setSynchronousDuration(0);
                });
            },
            updateComponent(c, from) {
                let elem = this.componentMgr.getComponentById(c.componentId);
                if (elem === null && from !== undefined && from.locationId !== undefined) {
                    elem = this.componentMgr.createComponentElem(c.typeId, c.componentId);
                    const elemCreationElem = gameui.getElementCreationElement();
                    elemCreationElem.appendChild(elem);
                    switch (parseInt(from.locationId)) {
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_DECK:
                            if (this.componentMgr.typeIdIsCardBlue(c.typeId)) {
                                this.playerBoardMgr.moveToCardBluePlayerDeck(c.componentId, from.playerId, true);
                            } else {
                                this.playerBoardMgr.moveToCardRedPlayerDeck(c.componentId, from.playerId, true);
                            }
                            break;
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT:
                            this.playerBoardMgr.moveToPlayerDevelopment(c.componentId, from.locationPrimaryOrder, from.playerId, true);
                            break;
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_SUPPLY:
                            if (this.componentMgr.typeIdIsCardBlue(c.typeId)) {
                                this.mainBoardMgr.moveComponentToCardBlueSupply(c.componentId, true);
                            } else if (this.componentMgr.typeIdIsCardRed(c.typeId)) {
                                this.mainBoardMgr.moveComponentToCardRedMarket(c.componentId, c.locationPrimaryOrder, true);
                            } else {
                                this.mainBoardMgr.moveComponentToMagicSupply(c.componentId, true);
                            }
                            break;
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_HAND:
                            this.playerBoardMgr.moveToOtherPlayerHand(c.componentId, from.playerId, true);
                            break;
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_BOARD:
                            this.playerBoardMgr.moveToPlayerBoardMagic(c.componentId, from.playerId, c.locationPrimaryOrder, true);
                            break;
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA:
                            this.playerBoardMgr.moveToCardRedPlayerPlayArea(c.componentId, from.playerId, from.locationPrimaryOrder, 0, true);
                            break;
                    }
                }
                if (elem !== null) {
                    switch (parseInt(c.locationId)) {
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_DECK:
                            if (this.componentMgr.typeIdIsCardBlue(c.typeId)) {
                                return this.playerBoardMgr.moveToCardBluePlayerDeckAndDestroy(c.componentId, c.playerId);
                            } else {
                                return this.playerBoardMgr.moveToCardRedPlayerDeckAndDestroy(c.componentId, c.playerId);
                            }
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_HAND:
                            if (c.playerId == this.player_id) {
                                return this.handMgr.moveToCardPlayerHand(c.componentId, c.locationPrimaryOrder);
                            } else {
                                return this.playerBoardMgr.moveToOtherPlayerHandAndDestroy(c.componentId, from.playerId);
                            }
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT:
                            return this.playerBoardMgr.moveToPlayerDevelopmentAndDestroy(c.componentId, c.locationPrimaryOrder, c.playerId);
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_MARKET:
                            if (this.componentMgr.typeIdIsCardBlue(c.typeId)) {
                                return this.mainBoardMgr.moveComponentToCardBlueMarket(c.componentId, c.locationPrimaryOrder);
                            } else {
                                return this.mainBoardMgr.moveComponentToCardRedMarket(c.componentId, c.locationPrimaryOrder);
                            }
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_DISCARD:
                            return this.componentMgr.discardComponent(c.componentId);
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA:
                            if (this.componentMgr.typeIdIsCardBlue(c.typeId)) {
                                return this.playerBoardMgr.moveToCardBluePlayerPlayArea(c.componentId, c.playerId, c.locationPrimaryOrder);
                            } else {
                                return this.playerBoardMgr.moveToCardRedPlayerPlayArea(c.componentId, c.playerId, c.locationPrimaryOrder, c.locationSecondaryOrder);
                            }
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING:
                            return this.playerBoardMgr.moveToCardBuildingPlayerPlayArea(c.componentId, c.playerId, c.locationPrimaryOrder);
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_SUPPLY_VISIBLE:
                            return this.mainBoardMgr.moveComponentToMagicSupply(c.componentId);
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_PLAYER_BOARD:
                            if (gameui.componentMgr.typeIdIsEnemy(c.typeId)) {
                                return this.playerBoardMgr.moveToPlayerBoardEnemy(c.componentId, c.playerId, c.locationPrimaryOrder);
                            } else {
                                return this.playerBoardMgr.moveToPlayerBoardMagic(c.componentId, c.playerId, c.locationPrimaryOrder);
                            }
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_SOLO_BOARD:
                            return this.soloMgr.moveToCardToSoloBoard(c.componentId, c.locationPrimaryOrder, c.locationSecondaryOrder);
                        case gameui.componentMgr.COMPONENT_LOCATION_ID_SUPPLY: {
                            return this.componentMgr.removeComponent(c.componentId);
                        }
                    }
                }
                return Promise.resolve();
            },

            notif_ShuffleBlueDeck(args) {
                debug('notif_ShuffleBlueDeck');
                debug(args);
                const movements = [];
                for (const playerId of args.args.playerIds) {
                    movements.push(this.playerBoardMgr.shuffleBlueDeck(playerId));
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_ShuffleRedDeck(args) {
                debug('notif_ShuffleRedDeck');
                debug(args);
                const movements = [];
                for (const playerId of args.args.playerIds) {
                    movements.push(this.playerBoardMgr.shuffleRedDeck(playerId));
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdateCounts(args) {
                debug('notif_UpdateCounts');
                debug(args);
                this.mainBoardMgr.updateMagicCount(args.args.componentCounts.magicCount);
                this.mainBoardMgr.updateCardBlueCount(args.args.componentCounts.cardBlueCount);
                this.mainBoardMgr.updateCardRedCounts(args.args.componentCounts.cardRedCounts);
                this.mainBoardMgr.updateEnemyCounts(args.args.componentCounts.enemyCounts);
                this.playerBoardMgr.updateComponentCounts(args.args.componentCounts);
                this.playerPanelMgr.updateHandCounts(args.args.componentCounts.handCounts);
                this.playerPanelMgr.updateMagicCount(args.args.componentCounts.magicPlayerBoardCounts);
                this.soloMgr.updateIconCounts(args.args.componentCounts.soloIconCounts);
                this.soloMgr.updateCounterCounts(args.args.componentCounts);
                this.mainBoardMgr.movePlayerScoreShield(null, args.args.componentCounts.soloGoldCount);
            },

            notif_UpdateNugget(args) {
                debug('notif_UpdateNugget');
                debug(args);
                this.playerBoardMgr.updateNuggetCount(args.args.playerId, args.args.nuggetCount, args.args.from).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdateMaterial(args) {
                debug('notif_UpdateMaterial');
                debug(args);
                this.playerBoardMgr.updateMaterialCount(args.args.playerId, args.args.materialCount, args.args.from).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdatePass(args) {
                debug('notif_UpdatePass');
                debug(args);
                this.playerPanelMgr.updatePlayerPassed(args.args.playerId, args.args.passed);
            },

            notif_LastRound(args) {
                debug('notif_LastRound');
                debug(args);
                this.displayLastRound(args.args.isLastRound);
            },

            notif_UpdatePlayerScore(args) {
                debug('notif_UpdatePlayerScore');
                debug(args);
                args.args.setNotificationDuration = false;
                this.inherited(arguments);
                this.mainBoardMgr.movePlayerScoreShield(args.args.playerId, args.args.playerScore).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdateFirstPlayer(args) {
                debug('notif_UpdateFirstPlayer');
                debug(args);
                this.playerPanelMgr.moveFirstPlayerToken(args.args.roundFirstPlayerId).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_FlipVillage(args) {
                debug('notif_FlipVillage');
                debug(args);
                this.mainBoardMgr.flipVillage(args.args.fromId, args.args.toId, args.args.toTypeId).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UseComponents(args) {
                debug('notif_UseComponents');
                debug(args);
                const movements = [];
                for (const id of args.args.componentIds) {
                    movements.push(this.componentMgr.useComponent(id, args.args.isUsed));
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_RollDice(args) {
                debug('notif_RollDice');
                debug(args);
                const parentElem = this.getFromElement(args.args.from);
                if (parentElem.classList.contains('gb-component')) {
                    parentElem.style.zIndex = 5000;
                }
                gameui.autoScroll(parentElem, args.args.playerId, false);
                this.createAndAnimateDiceFromSide(
                    'gb-roll-dice-' + args.args.diceId,
                    parentElem,
                    args.args.diceFace,
                    this.createDiceFace
                ).then(() => this.wait(300)).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_DestroyDice(args) {
                debug('notif_DestroyDice');
                debug(args);
                this.wait(300).then(() => {
                    const elem = document.getElementById('gb-roll-dice-' + args.args.diceId);
                    const parentElem = elem.parentElement;
                    this.discardElement(elem).then(() => {
                        if (parentElem !== null && parentElem.querySelector('.bx-dice') === null) {
                            parentElem.style.zIndex = null;
                        }
                        this.notifqueue.setSynchronousDuration(0);
                    });
                });
            },

            notif_FlipEnemy(args) {
                debug('notif_FlipEnemy');
                debug(args);
                this.mainBoardMgr.flipEnemy(args.args.locationId, args.args.component).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_SelectEnemy(args) {
                debug('notif_SelectEnemy');
                debug(args);
                this.mainBoardMgr.selectEnemy(args.args.selectEnemy).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_AttackEnemy(args) {
                debug('notif_AttackEnemy');
                debug(args);
                const from = this.componentMgr.getComponentById(args.args.fromComponentId);
                if (this.isFastMode() || args.args.combatPower == 0 || from === null) {
                    this.notifqueue.setSynchronousDuration(0);
                    return;
                }
                const attack = document.createElement('div');
                attack.classList.add('gb-sword');
                from.appendChild(attack);

                const to = this.componentMgr.getComponentById(args.args.toComponentId);
                return gameui.slide(attack, to, {
                    phantom: true,
                }).then(() => {
                    attack.remove();
                    if (to.dataset.typeId == gameui.PERMANENT_ENEMY_TYPE_ID) {
                        return Promise.resolve();
                    }
                    const anim = Math.round(Math.random());
                    to.classList.add('gb-attack-animation-' + anim);
                    return this.wait(300).then(() => to.classList.remove('gb-attack-animation-' + anim));
                }).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_CombatStatus(args) {
                debug('notif_CombatStatus');
                debug(args);
                if (this.isFastMode() || this.isReadOnly()) {
                    this.notifqueue.setSynchronousDuration(0);
                    return;
                }
                (new Promise((resolve, reject) => {
                    const dialog = new bx.ModalDialog('gb-combat-status-dialog', {
                        title:
                            this.format_string_recursive(
                                this.isTrue(args.args.win)
                                    ? _('${playerName} ${space} Wins!')
                                    : _('${playerName} ${space} Loses!'),
                                    {
                                        space: '&nbsp;',
                                        playerName: this.createPlayerColorNameElement(args.args.playerId).outerHTML,
                                    }),
                        contents:
                            '<div>'
                            + this.format_string_recursive(
                                _('${playerPower} vs ${enemyPower}'),
                                {
                                    playerPower: args.args.playerPower,
                                    enemyPower: args.args.enemyPower,
                                }
                            )
                            + '</div>'
                            + '<div>'
                            + this.format_string_recursive(
                                '${componentImage}',
                                {
                                    componentImage: args.args.typeId,
                                }
                            )
                            + '</div>'
                            + '<div class="gb-combat-status-dialog-enemy-name">'
                            + _(this.gamedatas.componentDefs[args.args.typeId].name)
                            + '</div>',
                        closeAction: 'hide',
                        closeWhenClickAnywhere: true,
                        followScroll: true,
                        onShow: () => {
                            if (this.isTrue(args.args.win)) {
                                playSound('goldblivion_victory');
                            } else {
                                playSound('goldblivion_defeat');
                            }
                            this.wait(10000).then(() => {
                                dialog.hide();
                            });
                        },
                        onHide: () => {
                            dialog.kill();
                            resolve()
                        },
                    });
                    dialog.show();
                })).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                })
            },

            notif_UpdatePlayerDevelopmentTypeId(args) {
                debug('notif_UpdatePlayerDevelopmentTypeId');
                debug(args);
                this.playerBoardMgr.updatePlayerDevelopmentTypeId(args.args.playerDevelopmentTypeId);
            },

            notif_UpdateHandOrder(args) {
                debug('notif_UpdateHandOrder');
                debug(args);
                this.handMgr.updateHandOrder(args.args.handOrder).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdateRound(args) {
                debug('notif_UpdateRound');
                debug(args);
                this.counters.round.toValue(args.args.round);
            },

            notif_UpdateSoloActionList(args) {
                debug('notif_UpdateSoloActionList');
                debug(args);
                this.soloMgr.updateSoloActionList(args.args.soloActionList).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },
        });
    })