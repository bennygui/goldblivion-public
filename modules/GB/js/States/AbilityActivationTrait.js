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
        return declare("gb.AbilityActivationTrait", null, {
            onButtonsStateAbilityActivationInteractiveDestroy(args) {
                debug('onButtonsStateAbilityActivationInteractiveDestroy');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }

                if (args.destroySoloNuggetCount > 0) {
                    const title = this.format_string_recursive(
                        _('Destroy 0 Cards and ${count} Solo Noble Nugget'),
                        {
                            count: args.destroySoloNuggetCount,
                        }
                    );
                    const resetSelection = this.addTopButtonSelection(
                        title,
                        _('You must choose less cards'),
                        {
                            title: (ids) => {
                                if (!(ids instanceof Array)) {
                                    if (ids === null) {
                                        ids = [];
                                    } else {
                                        ids = [ids];
                                    }
                                }
                                if (args.cardCount == 1 && args.destroySoloNuggetCount == 1) {
                                    switch (ids.length) {
                                        default:
                                        case 0:
                                            return title;
                                        case 1:
                                            return _('Destroy 1 Card and 0 Solo Noble Nugget');
                                        case 2:
                                            return _('Destroy 2 Cards and 0 Solo Noble Nugget');
                                    }
                                }
                                if (args.cardCount == 2 && args.destroySoloNuggetCount == 1) {
                                    switch (ids.length) {
                                        default:
                                        case 0:
                                            return title;
                                        case 1:
                                            return _('Destroy 1 Card and 1 Solo Noble Nugget');
                                        case 2:
                                            return _('Destroy 2 Cards and 0 Solo Noble Nugget');
                                    }
                                }
                                if (args.cardCount == 2 && args.destroySoloNuggetCount == 2) {
                                    switch (ids.length) {
                                        default:
                                        case 0:
                                            return title;
                                        case 1:
                                            return _('Destroy 1 Card and 1 Solo Noble Nugget');
                                        case 2:
                                            return _('Destroy 2 Cards and 0 Solo Noble Nugget');
                                    }
                                }
                            },
                            ids: args.componentIds,
                            onElement: (id) => this.componentMgr.getComponentById(id),
                            onClick: (ids) => {
                                if (!(ids instanceof Array)) {
                                    if (ids === null) {
                                        ids = [];
                                    } else {
                                        ids = [ids];
                                    }
                                }
                                const soloNugget = Math.min(args.cardCount - ids.length, args.destroySoloNuggetCount);
                                ids = ids.join(',');
                                this.showConfirmDialogIfConfirm(null, ids.length > 0).then(() => {
                                    this.serverAction('abilityActivationInteractiveDestroy', { componentIds: ids, soloNugget: soloNugget });
                                });
                            },
                        },
                        args.cardCount,
                        (nb) => (nb <= args.cardCount)
                    );
                    if (args.cardCount > 1) {
                        this.addTopButtonSecondary('button-reset', _('Reset'), () => resetSelection());
                    }
                } else {
                    const resetSelection = this.addTopButtonSelection(
                        args.cardCount > 1
                            ? _('Destroy Cards')
                            : _('Destroy Card'),
                        this.format_string_recursive(
                            _('You must choose ${cardCount} card(s) to destroy from any of the markets'),
                            {
                                cardCount: args.cardCount,
                            }
                        ),
                        {
                            ids: args.componentIds,
                            onElement: (id) => this.componentMgr.getComponentById(id),
                            onClick: (ids) => {
                                if (ids instanceof Array) {
                                    ids = ids.join(',');
                                }
                                this.showConfirmDialogIfConfirm().then(() => {
                                    this.serverAction('abilityActivationInteractiveDestroy', { componentIds: ids, soloNugget: 0 });
                                });
                            },
                        },
                        args.cardCount
                    );
                    if (args.cardCount > 1) {
                        this.addTopButtonSecondary('button-reset', _('Reset'), () => resetSelection());
                    }
                }
            },

            onButtonsStateAbilityActivationInteractiveGainRed(args) {
                debug('onButtonsStateAbilityActivationInteractiveGainRed');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Gain Card'),
                    _('You must choose a Combat card to gain'),
                    [
                        {
                            ids: args.componentIds,
                            onElement: (id) => this.componentMgr.getComponentById(id),
                            onClick: (id) => {
                                this.showConfirmDialogIfConfirm().then(() => {
                                    this.serverAction('abilityActivationInteractiveGainRed', { componentId: id, redDeckPlayerId: null });
                                });
                            },
                        },
                        {
                            title: _('Draw and Gain Card'),
                            ids: args.redDeckPlayerIds,
                            onElement: (id) => this.playerBoardMgr.getTopRedDeckForPlayerId(id),
                            onClick: (id) => {
                                this.showConfirmDialogIfConfirm().then(() => {
                                    this.serverAction('abilityActivationInteractiveGainRed', { componentId: null, redDeckPlayerId: id });
                                });
                            },
                        },
                    ]
                );
                for (const side of args.emptyRedDecks) {
                    const e = this.mainBoardMgr.getCardRedSupplyContainerElem(side);
                    this.addClickable(e, () => {
                        this.showInformationDialog(
                            _('Empty Combat card deck'),
                            [
                                _('When one or both of the Combat card deck is empty, you can take the top card from the Combat card deck of one of your opponents.'),
                                _('You can scroll down to click on one of your oppenents\' deck to take a card.'),
                            ]
                        );
                    });
                }
            },

            onButtonsStateAbilityActivationInteractiveGainBlue(args) {
                debug('onButtonsStateAbilityActivationInteractiveGainBlue');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Gain Card'),
                    _('You must choose a card from the GOLDblivion market'),
                    {
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getComponentById(id),
                        onClick: (id) => {
                            this.showConfirmDialogIfConfirm().then(() => {
                                this.serverAction('abilityActivationInteractiveGainBlue', { componentId: id });
                            });
                        },
                    },
                );
            },

            onButtonsStateAbilityActivationInteractiveReactivateHumanoid(args) {
                debug('onButtonsStateAbilityActivationInteractiveReactivateHumanoid');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Reactivate Card'),
                    _('You must choose a card from your GOLDblivion play area'),
                    {
                        title: (id, side) => {
                            if (gameui.componentHasSide(id)) {
                                return side === 0
                                    ? _('Reactivate Card: Left Side')
                                    : _('Reactivate Card: Right Side');
                            }
                            return _('Reactivate Card');
                        },
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getSidesComponentById(id),
                        onSelect: (id, side, option) => {
                            if (gameui.isSingleClick(id, side)) {
                                option.onClick(id, side);
                            }
                        },
                        onClick: (id, side) => {
                            this.showConfirmDialogDrawBlue(id, side, args.hasBlueCards).then(() => {
                                this.serverAction('abilityActivationInteractiveReactivateHumanoid', { componentId: id, side });
                            });
                        },
                    },
                );
            },

            onButtonsStateAbilityActivationInteractiveReactivateBuilding(args) {
                debug('onButtonsStateAbilityActivationInteractiveReactivateBuilding');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.addTopButtonSelection(
                    _('Reactivate Building'),
                    _('You must choose an activated building card from your play area'),
                    {
                        ids: args.componentIds,
                        onElement: (id) => this.componentMgr.getComponentById(id),
                        onSelect: (id, side, option) => {
                            if (gameui.isPrefSingleClick()) {
                                option.onClick(id, side);
                            }
                        },
                        onClick: (id) => this.serverAction('abilityActivationInteractiveReactivateBuilding', { componentId: id }),
                    },
                );
            },
        });
    });