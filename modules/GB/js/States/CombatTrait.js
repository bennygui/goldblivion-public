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
        return declare("gb.CombatTrait", null, {
            onButtonsStateCombatSelectEnemy(args) {
                debug('onButtonsStateCombatSelectEnemy');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (this.seenMoreThanOneStateList()) {
                    gameui.autoScroll(document.getElementById('gb-main-board'), this.getActivePlayerId(), false);
                }
                const playerCombatDraw = parseInt(args.playerCombatDraw);
                const formatNbCards = (locationDraw) => {
                    if (playerCombatDraw == 0) {
                        return locationDraw;
                    } else {
                        return locationDraw + '+' + playerCombatDraw;
                    }
                };

                for (const id of args.locationIds) {
                    const enemy = this.mainBoardMgr.getEnemyByLocationId(id);
                    const draw = enemy.querySelector('.gb-enemy-draw');
                    draw.classList.remove('bx-hidden');
                    const drawCount = enemy.querySelector('.gb-enemy-draw-count');
                    drawCount.innerText = args.locationIdCombatDraw[id];
                }
                for (const id of args.componentIds) {
                    const enemy = this.componentMgr.getComponentById(id);
                    const draw = enemy.querySelector('.gb-enemy-draw');
                    draw.classList.remove('bx-hidden');
                    const drawCount = enemy.querySelector('.gb-enemy-draw-count');
                    drawCount.innerText = args.componentIdCombatDraw[id];
                }

                let confirmMsg = _('Are you sure? This cannot be undone.');
                if (!this.isTrue(args.hasCombatCard)) {
                    confirmMsg += '<p><b>' + _('You have no Combat cards.') + '</b></p>';
                }
                this.addTopButtonSelection(
                    _('Choose Enemy'),
                    _('You must choose a revealed or hidden reachable enemy'),
                    [
                        {
                            title: (id) => gameui.format_string_recursive(
                                _('Fight Enemy (Draw up to ${nbCards} cards)'),
                                { nbCards: formatNbCards(args.componentIdCombatDraw[id]) }
                            ),
                            ids: args.componentIds,
                            onElement: (id) => this.componentMgr.getComponentById(id),
                            onClick: (id) => {
                                this.showConfirmDialogCondition(confirmMsg, !this.isTrue(args.hasCombatCard) || this.mustConfirmActions()).then(() => {
                                    this.serverAction('combatSelectEnemy', { componentId: id, locationId: null });
                                });
                            },
                            clickableOption: { childEventSelector: '.gb-enemy-click-target' },
                        },
                        {
                            title: (id) => gameui.format_string_recursive(
                                _('Flip and Fight Enemy (Draw up to ${nbCards} cards)'),
                                { nbCards: formatNbCards(args.locationIdCombatDraw[id]) }
                            ),
                            ids: args.locationIds,
                            onElement: (id) => this.mainBoardMgr.getEnemyByLocationId(id),
                            onClick: (id) => {
                                this.showConfirmDialogCondition(confirmMsg, !this.isTrue(args.hasCombatCard) || this.mustConfirmActions()).then(() => {
                                    this.serverAction('combatSelectEnemy', { componentId: null, locationId: id });
                                });
                            },
                            clickableOption: { childEventSelector: '.gb-enemy-click-target' },
                        },
                    ]
                );
            },

            onButtonsStateCombatLoseDestroyRedCard(args) {
                debug('onButtonsStateCombatLoseDestroyRedCard');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Destroy Card'),
                    _('You must choose a card to destroy from your played combat card'),
                    {
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getComponentById(id),
                        onSelect: (id, side, option) => {
                            if (gameui.isPrefSingleClick()) {
                                option.onClick(id, side);
                            }
                        },
                        onClick: (id) => this.serverAction('combatLoseDestroyRedCard', { componentId: id }),
                    },
                );
            },

            onButtonsStateCombatInteractive(args) {
                debug('onButtonsStateCombatInteractive');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (args.componentIds.length > 0) {
                    this.addTopButtonSelection(
                        _('Activate Card'),
                        _('You must choose a combat card to activate'),
                        {
                            title: (id, side) => {
                                if (gameui.componentHasSide(id)) {
                                    return side === 0
                                        ? _('Activate Card: Left Side')
                                        : _('Activate Card: Right Side');
                                }
                                return _('Activate Card');
                            },
                            ids: args.componentIds,
                            onElement: (id) => this.componentMgr.getSidesComponentById(id),
                            onSelect: (id, side, option) => {
                                if (gameui.isSingleClick(id, side)) {
                                    option.onClick(id, side);
                                }
                            },
                            onClick: (id, side) => {
                                this.showConfirmDialogIfConfirm(null, gameui.mustCommit(id, side)).then(() => {
                                    this.serverAction('combatInteractive', { componentId: id, side: side });
                                });
                            },
                        },
                    );
                }
                this.addTopButtonImportantWithTimerPreference(
                    'button-end-combat',
                    _('End Combat'),
                    this.TIMER_DURATION,
                    args.componentIds.length == 0 && this.seenMoreThanOneStateList(),
                    args,
                    () => this.getLocalPreference(this.GB_PREF_CONFIRM_TIMER_ID),
                    (checked) => this.setLocalPreference(this.GB_PREF_CONFIRM_TIMER_ID, checked),
                    () => {
                        this.showConfirmDialogIfConfirm(
                            _('Are you sure you want to end the Combat? This cannot be undone.')
                        ).then(() => {
                            this.serverAction('combatInteractiveEndCombat');
                        });
                    }
                );
            },

            onButtonsStateCombatInteractiveReactivateRedCard(args) {
                debug('onButtonsStateCombatInteractiveReactivateRedCard');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Copy Combat Card'),
                    _('You must choose a played combat card to copy'),
                    {
                        title: (id, side) => {
                            if (gameui.componentHasSide(id)) {
                                return side === 0
                                    ? _('Copy Combat Card: Left Side')
                                    : _('Copy Combat Card: Right Side');
                            }
                            return _('Copy Combat Card');
                        },
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getSidesComponentById(id),
                        onSelect: (id, side, option) => {
                            if (gameui.isSingleClick(id, side)) {
                                option.onClick(id, side);
                            }
                        },
                        onClick: (id, side) => {
                            this.showConfirmDialogIfConfirm(null, gameui.mustCommit(id, side)).then(() => {
                                this.serverAction('combatInteractiveReactivateRedCard', { componentId: id, side: side });
                            });
                        },
                    },
                );
            },
        });
    });