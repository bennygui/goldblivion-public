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
        return declare("gb.PlayerActionTrait", null, {
            onButtonsStatePlayerActionChooseAction(args) {
                debug('onButtonsStatePlayerActionChooseAction');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                let undoLevel = 0;
                if (args && args.undoLevel !== undefined && args.undoLevel !== null) {
                    undoLevel = args.undoLevel;
                } else if (args && args._private && args._private.undoLevel !== undefined && args._private.undoLevel !== null) {
                    undoLevel = args._private.undoLevel;
                }

                const originalArgs = args;
                args = args._private;

                let lastHandSelection = null;
                let hasActions = false;
                if (
                    args.cardIdsInPlayerHand.length > 0 ||
                    args.villageIds.length > 0 ||
                    args.cardIdsInMarkets.length > 0 ||
                    args.buildingCardIds.length > 0 ||
                    args.playerMagicIds.length > 0
                ) {
                    hasActions = true;
                    this.addTopButtonSelection(
                        _('Play selection'),
                        _('You must select a card, a village or a magic token to play'),
                        [
                            // Main actions
                            {
                                title: (id, side) => {
                                    if (gameui.componentHasSide(id)) {
                                        return side === 0
                                            ? _('Play Card: Left Side')
                                            : _('Play Card: Right Side');
                                    }
                                    return _('Play Card');
                                },
                                ids: args.cardIdsInPlayerHand,
                                onElement: (id) => this.componentMgr.getSidesComponentById(id),
                                onSelect: (id, side, option) => {
                                    if (gameui.isSingleClick(id, side)) {
                                        option.onClick(id, side);
                                    } else {
                                        lastHandSelection = {
                                            id: id,
                                            side: side,
                                        };
                                    }
                                },
                                onClick: (id, side) => {
                                    this.showConfirmDialogDrawBlue(id, side, args.hasBlueCards).then(() => {
                                        this.serverAction('playerActionMainPlayCard', { componentId: id, side: side });
                                    });
                                },
                            },
                            {
                                title: _('Activate Village'),
                                ids: args.villageIds,
                                onElement: (id) => this.componentMgr.getComponentById(id),
                                onSelect: (id, side, option) => {
                                    if (gameui.isSingleClick(id, side)) {
                                        option.onClick(id, side);
                                    }
                                },
                                onClick: (id) => {
                                    this.showConfirmDialogIfConfirm(null, gameui.mustCommit(id)).then(() => {
                                        this.serverAction('playerActionMainPlayVillage', { componentId: id });
                                    });
                                },
                            },
                            {
                                title: (id) => {
                                    const cost = gameui.getComponentCost(id)
                                    if (cost && cost.resourceTypeId == gameui.RESOURCE_TYPE_ID_NUGGET) {
                                        return this.format_string_recursive(
                                            _('Buy Card for ${cost}x${nuggetImage}'),
                                            {
                                                cost: cost.count,
                                                nuggetImage: _('nugget(s)'),
                                            }
                                        );
                                    }
                                    if (cost && cost.resourceTypeId == gameui.RESOURCE_TYPE_ID_MATERIAL) {
                                        return this.format_string_recursive(
                                            _('Buy Card for ${cost}x${materialImage}'),
                                            {
                                                cost: cost.count,
                                                materialImage: _('material'),
                                            }
                                        );
                                    }
                                    return _('Buy Card');
                                },
                                ids: args.cardIdsInMarkets,
                                onElement: (id) => this.componentMgr.getComponentById(id),
                                onClick: (id) => {
                                    let confirm = () => this.showConfirmDialogIfConfirm();
                                    if (lastHandSelection !== null) {
                                        if (
                                            (gameui.isComponentCardBlue(id)
                                                && gameui.hasFreeBlueCardAbility(lastHandSelection.id, lastHandSelection.side))
                                            ||
                                            (gameui.isComponentCardRed(id)
                                                && gameui.hasFreeRedCardAbility(lastHandSelection.id, lastHandSelection.side))
                                        ) {
                                            confirm = () => this.showConfirmDialog(
                                                this.format_string_recursive(
                                                    _('You are ${startb}buying${endb} a card and not using the ability of card ${cardName}. This cannot be undone. Are you sure?'),
                                                    {
                                                        cardName: gameui.getComponentName(lastHandSelection.id),
                                                        startb: '<b>',
                                                        endb: '</b>',
                                                    }
                                                )
                                            );
                                        }
                                    }
                                    confirm().then(() => {
                                        this.serverAction('playerActionMainBuyCard', { componentId: id });
                                    });
                                },
                            },
                            // Bonus actions
                            {
                                title: (id, side) => {
                                    if (gameui.componentHasSide(id)) {
                                        return side === 0
                                            ? _('Activate Building: Left Side')
                                            : _('Activate Building: Right Side');
                                    }
                                    return _('Activate Building');
                                },
                                ids: args.buildingCardIds,
                                onElement: (id) => this.componentMgr.getSidesComponentById(id),
                                onSelect: (id, side, option) => {
                                    if (gameui.isSingleClick(id, side)) {
                                        option.onClick(id, side);
                                    }
                                },
                                onClick: (id, side) => {
                                    this.showConfirmDialogDrawBlue(id, side, args.hasBlueCards).then(() => {
                                        this.serverAction('playerActionBonusActivateBuilding', { componentId: id, side: side });
                                    });
                                },
                            },
                            {
                                title: _('Play Magic Token'),
                                ids: args.playerMagicIds,
                                onElement: (id) => this.componentMgr.getComponentById(id),
                                onSelect: (id, side, option) => {
                                    if (gameui.isSingleClick(id, side)) {
                                        option.onClick(id, side);
                                    }
                                },
                                onClick: (id) => {
                                    this.showConfirmDialogDrawBlue(id, 0, args.hasBlueCards).then(() => {
                                        this.serverAction('playerActionBonusPlayMagic', { componentId: id });
                                    });
                                },
                            },
                        ]
                    );
                }

                // Bonus action: Convert
                if (this.isTrue(args.canConvertNuggetToGold)) {
                    hasActions = true;
                    this.addTopButtonSecondary(
                        'button-convert',
                        this.format_string_recursive(
                            '7x${nuggetImage}${convertImage}${goldImage}',
                            {
                                nuggetImage: _('nuggets'),
                                goldImage: _('gold'),
                                convertImage: _('convert'),
                            }
                        ),
                        () => this.serverAction('playerActionBonusConvertNuggetToGold')
                    );
                }

                // Pass and End turn
                if (this.isTrue(args.canPass)) {
                    let msg = _('Are you sure you want to pass? You will not do any other actions in this round.');
                    if (args.cardIdsInPlayerHand.length > 0) {
                        msg += '<p><b>' + _('You have cards in your hand.') + '</b></p>';
                    }
                    if (args.buildingCardIds.length > 0) {
                        msg += '<p><b>' + _('You have unused buildings.') + '</b></p>';
                    }
                    if (args.playerMagicIds.length > 0) {
                        msg += '<p><b>' + _('You have magic tokens.') + '</b></p>';
                    }
                    if (args.villageIds.length > 0) {
                        msg += '<p>' + _('You could pay for village tokens.') + '</p>';
                    }
                    if (args.cardIdsInMarkets.length > 0) {
                        msg += '<p>' + _('You could buy cards from the markets.') + '</p>';
                    }
                    if (this.isTrue(args.canConvertNuggetToGold)) {
                        msg += '<p>' + _('Your nuggets will be converted to gold.') + '</p>';
                    }
                    this.addTopButtonImportant(
                        'button-pass',
                        _('Pass'),
                        () => {
                            this.showConfirmDialogCondition(
                                msg,
                                this.mustConfirmActions()
                                || hasActions
                                || undoLevel >= 1
                            ).then(() => {
                                this.serverAction('playerActionPass');
                            });
                        }
                    );
                }

                if (this.isTrue(args.canEndTurn)) {
                    this.addTopButtonImportantWithTimerPreference(
                        'button-end-turn',
                        _('End Turn'),
                        this.TIMER_DURATION,
                        !hasActions && this.seenMoreThanOneStateList(),
                        originalArgs,
                        () => this.getLocalPreference(this.GB_PREF_CONFIRM_TIMER_ID),
                        (checked) => this.setLocalPreference(this.GB_PREF_CONFIRM_TIMER_ID, checked),
                        () => {
                            this.showConfirmDialogIfConfirm(
                                _('Are you sure you want to end your turn? This cannot be undone.')
                            ).then(() => {
                                this.serverAction('playerActionEndTurn');
                            });
                        }
                    );
                }
            },
        });
    });