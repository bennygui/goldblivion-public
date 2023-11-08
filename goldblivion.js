/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * goldblivion.js
 *
 * goldblivion user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/counter",
    "ebg/core/gamegui",
    g_gamethemeurl + "modules/BX/js/GameBase.js",
    g_gamethemeurl + "modules/BX/js/Numbers.js",
    g_gamethemeurl + "modules/BX/js/PlayerScoreTrait.js",
    g_gamethemeurl + "modules/BX/js/DiceTrait.js",
    g_gamethemeurl + "modules/GB/js/Mgr/ComponentMgr.js",
    g_gamethemeurl + "modules/GB/js/Mgr/MainBoardMgr.js",
    g_gamethemeurl + "modules/GB/js/Mgr/PlayerBoardMgr.js",
    g_gamethemeurl + "modules/GB/js/Mgr/DraftMgr.js",
    g_gamethemeurl + "modules/GB/js/Mgr/HandMgr.js",
    g_gamethemeurl + "modules/GB/js/Mgr/PlayerPanelMgr.js",
    g_gamethemeurl + "modules/GB/js/Mgr/SoloMgr.js",
    g_gamethemeurl + "modules/GB/js/States/PlayerSetupTrait.js",
    g_gamethemeurl + "modules/GB/js/States/RoundTrait.js",
    g_gamethemeurl + "modules/GB/js/States/PlayerActionTrait.js",
    g_gamethemeurl + "modules/GB/js/States/AbilityActivationTrait.js",
    g_gamethemeurl + "modules/GB/js/States/CombatTrait.js",
    g_gamethemeurl + "modules/GB/js/States/SoloTrait.js",
    g_gamethemeurl + "modules/GB/js/NotificationTrait.js",
],
    function (dojo, declare) {
        return declare("bgagame.goldblivion", [
            bx.GameBase,
            bx.PlayerScoreTrait,
            bx.DiceTrait,
            gb.PlayerSetupTrait,
            gb.RoundTrait,
            gb.PlayerActionTrait,
            gb.AbilityActivationTrait,
            gb.CombatTrait,
            gb.SoloTrait,
            gb.NotificationTrait,
        ], {
            PLAYER_YELLOW_BACK_COLOR: 'bbbbbb',

            CARD_BLUE_HEIGHT: 559,
            CARD_RED_HEIGHT: 473,

            COMPONENT_ICON_ID_BUILDING: 1,
            COMPONENT_ICON_ID_HUMAN: 2,
            COMPONENT_ICON_ID_ELF: 3,
            COMPONENT_ICON_ID_DWARF: 4,
            COMPONENT_ICON_ID_ENEMY: 5,

            PERMANENT_ENEMY_TYPE_ID: 5300,

            GAIN_ID_DESTROY_CARD_FROM_ANY_MARKET: 1,
            GAIN_ID_GAIN_NUGGET: 2,
            GAIN_ID_GAIN_MATERIAL: 3,
            GAIN_ID_GAIN_GOLD: 4,
            GAIN_ID_GAIN_FREE_RED_CARD: 5,
            GAIN_ID_DRAW_BLUE_CARD: 6,
            GAIN_ID_ROLL_DICE: 7,
            GAIN_ID_REACTIVATE_ICON: 8,
            GAIN_ID_GAIN_COMBAT_POWER: 9,
            GAIN_ID_DRAW_RED_CARD: 10,
            GAIN_ID_COPY_OTHER_RED_CARD: 11,
            GAIN_ID_DESTROY_SELF: 12,
            GAIN_ID_START_COMBAT: 13,
            GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD: 14,
            GAIN_ID_GAIN_MAGIC: 15,

            RESOURCE_TYPE_ID_NUGGET: 1,
            RESOURCE_TYPE_ID_MATERIAL: 2,
            RESOURCE_TYPE_ID_GOLD: 3,

            TIMER_DURATION: 10,

            GB_PREF_WELCOME_MESSAGE_ID: 'GB_PREF_WELCOME_MESSAGE_ID',
            GB_PREF_WELCOME_MESSAGE_DEFAULT_VALUE: true,

            GB_PREF_ALWAYS_CONFIRM_ID: 'GB_PREF_ALWAYS_CONFIRM_ID',
            GB_PREF_ALWAYS_CONFIRM_DEFAULT_VALUE: false,

            GB_PREF_SINGLE_CLICK_ID: 'GB_PREF_SINGLE_CLICK_ID',
            GB_PREF_SINGLE_CLICK_DEFAULT_VALUE: false,

            GB_PREF_CONFIRM_TIMER_ID: 'GB_PREF_CONFIRM_TIMER_ID',
            GB_PREF_CONFIRM_TIMER_DEFAULT_VALUE: true,

            GB_PREF_SHORTCUTS_ID: 'GB_PREF_SHORTCUTS_ID',
            GB_PREF_SHORTCUTS_DEFAULT_VALUE: true,

            GB_PREF_COMPONENT_HELP_ID: 'GB_PREF_COMPONENT_HELP_ID',
            GB_PREF_COMPONENT_HELP_DEFAULT_VALUE: true,

            GB_PREF_HAND_SORT_ID: 'GB_PREF_HAND_SORT_ID',
            GB_PREF_HAND_SORT_DEFAULT_VALUE: false,

            GB_PREF_HAND_POSITION_ID: 'GB_PREF_HAND_POSITION_ID',
            GB_PREF_HAND_POSITION_DEFAULT_VALUE: 'middle',
            GB_PREF_HAND_POSITION_VALUE_MIDDLE: 'middle',
            GB_PREF_HAND_POSITION_VALUE_BOTTOM: 'bottom',

            GB_PREF_AUTO_SCROLL_SELF_ID: 'GB_PREF_AUTO_SCROLL_SELF_ID',
            GB_PREF_AUTO_SCROLL_SELF_DEFAULT_VALUE: true,

            GB_PREF_DARK_BACKGROUND_ID: 'GB_PREF_DARK_BACKGROUND_ID',
            GB_PREF_DARK_BACKGROUND_DEFAULT_VALUE: true,

            GB_PREF_ZOOM_FACTOR_ID: 'GB_PREF_ZOOM_FACTOR_ID',
            GB_PREF_ZOOM_FACTOR_DEFAULT_VALUE: 40,

            GB_PREF_COMPACT_RED_0_ID: 'GB_PREF_COMPACT_RED_0_ID',
            GB_PREF_COMPACT_RED_0_DEFAULT_VALUE: true,
            GB_PREF_COMPACT_RED_1_ID: 'GB_PREF_COMPACT_RED_1_ID',
            GB_PREF_COMPACT_RED_1_DEFAULT_VALUE: true,
            GB_PREF_COMPACT_BLUE_ID: 'GB_PREF_COMPACT_BLUE_ID',
            GB_PREF_COMPACT_BLUE_DEFAULT_VALUE: true,

            constructor() {
                this.iconIdToName = {};
                this.iconIdToName[this.COMPONENT_ICON_ID_BUILDING] = 'building';
                this.iconIdToName[this.COMPONENT_ICON_ID_HUMAN] = 'human';
                this.iconIdToName[this.COMPONENT_ICON_ID_ELF] = 'elf';
                this.iconIdToName[this.COMPONENT_ICON_ID_DWARF] = 'dwarf';
                this.iconIdToName[this.COMPONENT_ICON_ID_ENEMY] = 'enemy';
                this.iconList = [
                    this.COMPONENT_ICON_ID_BUILDING,
                    this.COMPONENT_ICON_ID_HUMAN,
                    this.COMPONENT_ICON_ID_ELF,
                    this.COMPONENT_ICON_ID_DWARF,
                    this.COMPONENT_ICON_ID_ENEMY,
                ];

                this.counters = {};
                this.setAlwaysFixTopActions();

                this.componentMgr = new gb.ComponentMgr();
                this.mainBoardMgr = new gb.MainBoardMgr();
                this.playerBoardMgr = new gb.PlayerBoardMgr();
                this.draftMgr = new gb.DraftMgr();
                this.handMgr = new gb.HandMgr();
                this.playerPanelMgr = new gb.PlayerPanelMgr();
                this.soloMgr = new gb.SoloMgr();

                this.htmlTextForLogKeys.push('componentImage');
                this.htmlTextForLogKeys.push('nuggetImage');
                this.htmlTextForLogKeys.push('materialImage');
                this.htmlTextForLogKeys.push('goldImage');
                this.htmlTextForLogKeys.push('convertImage');
                this.htmlTextForLogKeys.push('diceImage');

                this.localPreferenceToRegister.push([this.GB_PREF_WELCOME_MESSAGE_ID, this.GB_PREF_WELCOME_MESSAGE_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_ALWAYS_CONFIRM_ID, this.GB_PREF_ALWAYS_CONFIRM_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_SINGLE_CLICK_ID, this.GB_PREF_SINGLE_CLICK_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_CONFIRM_TIMER_ID, this.GB_PREF_CONFIRM_TIMER_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_SHORTCUTS_ID, this.GB_PREF_SHORTCUTS_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_SHORTCUTS_ID, this.GB_PREF_SHORTCUTS_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_COMPONENT_HELP_ID, this.GB_PREF_COMPONENT_HELP_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_HAND_SORT_ID, this.GB_PREF_HAND_SORT_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_HAND_POSITION_ID, this.GB_PREF_HAND_POSITION_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_AUTO_SCROLL_SELF_ID, this.GB_PREF_AUTO_SCROLL_SELF_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_DARK_BACKGROUND_ID, this.GB_PREF_DARK_BACKGROUND_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_ZOOM_FACTOR_ID, this.GB_PREF_ZOOM_FACTOR_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_COMPACT_RED_0_ID, this.GB_PREF_COMPACT_RED_0_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_COMPACT_RED_1_ID, this.GB_PREF_COMPACT_RED_1_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.GB_PREF_COMPACT_BLUE_ID, this.GB_PREF_COMPACT_BLUE_DEFAULT_VALUE, {}]);
            },

            setup(gamedatas) {
                // Counters
                this.counters.round = new bx.Numbers();
                this.counters.solo = {
                    icon: {
                        human: new bx.Numbers(),
                        elf: new bx.Numbers(),
                        dwarf: new bx.Numbers(),
                        building: new bx.Numbers(),
                        enemy: new bx.Numbers(),
                    },
                    nugget: new bx.Numbers(),
                    material: new bx.Numbers(),
                    gold: new bx.Numbers(),
                };
                for (const playerId in gamedatas.players) {
                    this.counters[playerId] = {
                        hand: new bx.Numbers(),
                        magic: new bx.Numbers(),
                        deckBlue: new bx.Numbers(),
                        deckRed: new bx.Numbers(),
                        development: [
                            new bx.Numbers([0, 0]),
                            new bx.Numbers([0, 0]),
                        ],
                        nugget: new bx.Numbers(),
                        material: new bx.Numbers(),
                        icon: {
                            human: new bx.Numbers(),
                            elf: new bx.Numbers(),
                            dwarf: new bx.Numbers(),
                            building: new bx.Numbers(),
                            enemy: new bx.Numbers(),
                        }
                    };
                }

                // Adapt back color for yellow player
                for (const playerId in gamedatas.players) {
                    if (gamedatas.players[playerId].player_color_name == 'yellow') {
                        gamedatas.players[playerId].color_back = this.PLAYER_YELLOW_BACK_COLOR;
                        const playerPanelNameElem = document.querySelector('#player_name_' + playerId + ' a');
                        if (playerPanelNameElem !== null) {
                            playerPanelNameElem.style.backgroundColor = '#' + this.PLAYER_YELLOW_BACK_COLOR;
                        }
                        const playerBoardNameElem = document.querySelector('#gb-area-player-' + playerId + ' .player-name');
                        if (playerBoardNameElem !== null) {
                            playerBoardNameElem.style.backgroundColor = '#' + this.PLAYER_YELLOW_BACK_COLOR;
                        }
                    }
                }

                // Preload images
                const preloadImageArray = [
                    'background/dark.jpg',
                    'board/board_main.jpg',
                    'board/board_player.jpg',
                    'card/blue_building.jpg',
                    'card/blue_humanoid.jpg',
                    'card/blue_noble.jpg',
                    'card/blue_starting.jpg',
                    'card/red_deck.jpg',
                    'card/red_starting.jpg',
                    'icon/combat_numbers.png',
                    'icon/icon.png',
                    'icon/shield.png',
                    'token/boat.png',
                    'token/dice.png',
                    'token/enemy.png',
                    'token/enemy_border.png',
                    'token/enemy_selected.png',
                    'token/enemy_shadow.png',
                    'token/magic.png',
                    'token/sword.png',
                    'token/token.png',
                    'token/village.png',
                ];
                if (this.isGameSolo()) {
                    preloadImageArray.push('board/board_solo.jpg');
                }
                this.ensureSpecificGameImageLoading(preloadImageArray);

                this.componentMgr.setup(gamedatas);
                this.mainBoardMgr.setup(gamedatas);
                this.playerBoardMgr.setup(gamedatas);
                this.draftMgr.setup(gamedatas);
                this.handMgr.setup(gamedatas);
                this.playerPanelMgr.setup(gamedatas);
                this.soloMgr.setup(gamedatas);

                this.displayLastRound(gamedatas.isLastRound);

                this.inherited(arguments);
            },

            onLocalPreferenceChanged(prefId, value) {
                switch (prefId) {
                    case this.GB_PREF_WELCOME_MESSAGE_ID: {
                        const checkbox = document.getElementById('gb-welcome-message-checkbox');
                        checkbox.checked = value;
                        break;
                    }
                    case this.GB_PREF_ALWAYS_CONFIRM_ID: {
                        const checkbox = document.getElementById('gb-always-confirm-checkbox');
                        checkbox.checked = value;
                        break;
                    }
                    case this.GB_PREF_SINGLE_CLICK_ID: {
                        const checkbox = document.getElementById('gb-single-click-checkbox');
                        checkbox.checked = value;
                        break;
                    }
                    case this.GB_PREF_SHORTCUTS_ID: {
                        const checkbox = document.getElementById('gb-shortcuts-checkbox');
                        checkbox.checked = value;
                        this.playerPanelMgr.setupScrollShortcuts();
                        break;
                    }
                    case this.GB_PREF_COMPONENT_HELP_ID: {
                        const checkbox = document.getElementById('gb-component-help-checkbox');
                        checkbox.checked = value;
                        if (checkbox.checked) {
                            document.documentElement.classList.remove('gb-component-help-hidden');
                        } else {
                            document.documentElement.classList.add('gb-component-help-hidden');
                        }
                        break;
                    }
                    case this.GB_PREF_HAND_SORT_ID: {
                        const checkbox = document.getElementById('gb-hand-sort-checkbox');
                        checkbox.checked = value;
                        if (checkbox.checked) {
                            document.documentElement.classList.remove('gb-hand-sort-hidden');
                        } else {
                            document.documentElement.classList.add('gb-hand-sort-hidden');
                        }
                        break;
                    }
                    case this.GB_PREF_HAND_POSITION_ID: {
                        for (const e of document.querySelectorAll('.gb-card-position-middle, .gb-card-position-bottom')) {
                            e.classList.remove('gb-card-position-selected');
                        }
                        const e = document.querySelector('.gb-card-position-' + value);
                        e.classList.add('gb-card-position-selected');

                        const hand = document.getElementById('gb-area-card-hand');
                        if (value == 'bottom') {
                            hand.classList.add('gb-hand-float');
                            if (this.handMgr.dragScroller) { this.handMgr.dragScroller.enable() }
                        } else {
                            hand.classList.remove('gb-hand-float');
                            if (this.handMgr.dragScroller) { this.handMgr.dragScroller.disable() }
                        }
                        this.playerPanelMgr.setupScrollShortcuts();
                        break;
                    }
                    case this.GB_PREF_AUTO_SCROLL_SELF_ID: {
                        const checkbox = document.getElementById('gb-auto-scroll-self-checkbox');
                        checkbox.checked = value;
                        break;
                    }
                    case this.GB_PREF_DARK_BACKGROUND_ID: {
                        const checkbox = document.getElementById('gb-dark-background-checkbox');
                        checkbox.checked = value;
                        if (checkbox.checked) {
                            document.documentElement.classList.add('gb-background-dark');
                        } else {
                            document.documentElement.classList.remove('gb-background-dark');
                        }
                        break;
                    }
                    case this.GB_PREF_ZOOM_FACTOR_ID: {
                        document.body.style.setProperty('--gb-zoom', value / 100);
                        document.getElementById('gb-zoom-slider').value = value;
                        break;
                    }
                    case this.GB_PREF_COMPACT_BLUE_ID: {
                        for (const e of document.querySelectorAll('.gb-switch-compact-blue input')) {
                            e.checked = value;
                        }
                        for (const e of document.querySelectorAll('.gb-blue-building-container, .gb-blue-played-container')) {
                            if (value) {
                                e.classList.remove('gb-card-row');
                            } else {
                                e.classList.add('gb-card-row');
                            }
                        }
                        break;
                    }
                    case this.GB_PREF_COMPACT_RED_0_ID: {
                        for (const e of document.querySelectorAll('.gb-switch-compact-red-0 input')) {
                            e.checked = value;
                        }
                        for (const e of document.querySelectorAll('.gb-red-played-container-0')) {
                            if (value) {
                                e.classList.remove('gb-card-row');
                            } else {
                                e.classList.add('gb-card-row');
                            }
                        }
                        break;
                    }
                    case this.GB_PREF_COMPACT_RED_1_ID: {
                        for (const e of document.querySelectorAll('.gb-switch-compact-red-1 input')) {
                            e.checked = value;
                        }
                        for (const e of document.querySelectorAll('.gb-red-played-container-1')) {
                            if (value) {
                                e.classList.remove('gb-card-row');
                            } else {
                                e.classList.add('gb-card-row');
                            }
                        }
                        break;
                    }
                }
            },

            getHtmlTextForLogArg(key, value) {
                switch (key) {
                    case 'componentImage': {
                        const element = this.componentMgr.createComponentElem(value);
                        return element.outerHTML;
                    }
                    case 'nuggetImage': {
                        const element = gameui.createNuggetElement();
                        return element.outerHTML;
                    }
                    case 'materialImage': {
                        const element = gameui.createMaterialElement();
                        return element.outerHTML;
                    }
                    case 'goldImage': {
                        const element = gameui.createGoldElement();
                        return element.outerHTML;
                    }
                    case 'convertImage': {
                        const element = gameui.createConvertElement();
                        return element.outerHTML;
                    }
                    case 'diceImage': {
                        const element = gameui.createDiceFace(value);
                        return element.outerHTML;
                    }
                }
                return this.inherited(arguments);
            },

            onStateChangedBefore(stateName, args) {
                this.inherited(arguments);
            },

            onStateChangedAfter(stateName, args) {
                this.inherited(arguments);
            },

            onUpdateActionButtonsBefore(stateName, args) {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.hideAllEnemyCombatCardDraw();
            },

            onUpdateActionButtonsdAfter(stateName, args) {
                this.addTopUndoButton(args);
                this.inherited(arguments);
            },

            onUndoBegin() {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.hideAllEnemyCombatCardDraw();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
            },

            onLeavingState(stateName) {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.hideAllEnemyCombatCardDraw();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
            },

            onLoadingComplete() {
                this.inherited(arguments);
                this.showWelcomeMessage();
            },

            showWelcomeMessage() {
                if (this.isReadOnly()) {
                    return;
                }
                if (!this.getLocalPreference(this.GB_PREF_WELCOME_MESSAGE_ID)) {
                    return;
                }
                this.showInformationDialog(_('Welcome to GOLDblivion!'), [
                    _('${startb}If you do not want to see this message again, close it and disable the related option under the gear icon ${gear} beside the player panels.${endb}'),
                    '',
                    _('Options ${gear}'),
                    _('You can control the display of many aspects of the game in the option panel beside the player panels.'),
                    '',
                    _('Blue ? buttons'),
                    _('Blue ${startb}?${endb} buttons show help for the game components but also the cards that each player has and the cards that you have placed for development.'),
                    '',
                    _('Component list'),
                    _('Below all player boards, you can view a list of all the components in the game and how many of each components there are.'),
                    _('Have fun!'),
                ], {
                    gear: this.createFAIcon('gear').outerHTML,
                    startb: '<b>',
                    endb: '</b>',
                });
            },

            getElementCreationElement() {
                return document.getElementById('gb-element-creation');
            },

            displayLastRound(doDisplay = true) {
                if (doDisplay) {
                    document.getElementById('gb-display-last-round').classList.remove('bx-hidden');
                } else {
                    document.getElementById('gb-display-last-round').classList.add('bx-hidden');
                }
            },

            getBlueIcons() {
                return [
                    ['human', _('Number of Human icons in play area'), _('Number of Human cards under the Solo Noble Board')],
                    ['elf', _('Number of Elf icons in play area'), _('Number of Elf cards under the Solo Noble Board')],
                    ['dwarf', _('Number of Dwarf icons in play area'), _('Number of Dwarf cards under the Solo Noble Board')],
                    ['building', _('Number of Building icons in play area'), _('Number of Building cards under the Solo Noble Board')],
                    ['enemy', _('Number of Enemy defeated'), _('Number of Enemy destoyed by the Solo Noble')],
                ];
            },

            createShieldElement(playerId) {
                return this.createShieldElementFromColorName(this.gamedatas.players[playerId].player_color_name);
            },

            createShieldElementFromColorName(colorName) {
                const shield = document.createElement('div');
                shield.classList.add('gb-shield');
                shield.classList.add(colorName);
                return shield;
            },

            createNuggetElement() {
                const e = document.createElement('div');
                e.classList.add('gb-nugget');

                // Sparkle
                const delay = Math.floor(Math.random() * 100) + 's';
                const sparkle1Elem = document.createElement('div');
                sparkle1Elem.classList.add('gb-sparkle');
                sparkle1Elem.classList.add('gb-first');
                sparkle1Elem.style.setProperty('--gb-zoom', 1);
                sparkle1Elem.style.setProperty('--gb-sparkle-animation-delay', delay);
                sparkle1Elem.style.top = '0px';
                sparkle1Elem.style.left = '0px';
                const sparkle2Elem = document.createElement('div');
                sparkle2Elem.classList.add('gb-sparkle');
                sparkle2Elem.classList.add('gb-second');
                sparkle2Elem.style.setProperty('--gb-zoom', 1);
                sparkle2Elem.style.setProperty('--gb-sparkle-animation-delay', delay);
                sparkle2Elem.style.top = '0px';
                sparkle2Elem.style.left = '0px';
                e.appendChild(sparkle1Elem);
                e.appendChild(sparkle2Elem);

                return e;
            },

            createMaterialElement() {
                const e = document.createElement('div');
                e.classList.add('gb-material');
                return e;
            },

            createGoldElement() {
                const e = document.createElement('div');
                e.classList.add('gb-gold');
                return e;
            },

            createConvertElement() {
                const e = document.createElement('div');
                e.classList.add('gb-convert');
                return e;
            },

            createSoloDestroyEnemyElement() {
                const e = document.createElement('div');
                e.classList.add('gb-solo-destroy-enemy');
                return e;
            },

            createSoloDestroyCardElement() {
                const e = document.createElement('div');
                e.classList.add('gb-solo-destroy-card');
                return e;
            },

            createSoloDestroyNuggetElement() {
                const e = document.createElement('div');
                e.classList.add('gb-solo-destroy-nugget');
                return e;
            },

            createSoloDiceElement() {
                const e = document.createElement('div');
                e.classList.add('gb-solo-dice');
                return e;
            },

            createSoloRevealEnemyElement() {
                const e = document.createElement('div');
                e.classList.add('gb-solo-reveal-enemy');
                return e;
            },

            createDiceFace(faceNumber) {
                const face = document.createElement('div');
                switch (parseInt(faceNumber)) {
                    default:
                        debug('BUG! createDiceFace: invalid faceNumber' + faceNumber);
                    case 0:
                    case 1:
                        face.classList.add('gb-dice-face-nugget-1');
                        break;
                    case 2:
                    case 3:
                        face.classList.add('gb-dice-face-nugget-2');
                        break;
                    case 4:
                        face.classList.add('gb-dice-face-magic');
                        break;
                    case 5:
                        face.classList.add('gb-dice-face-material');
                        break;
                }
                return face;
            },

            discardElement(element) {
                // To be able to change the duration, would need to change the css
                const duration = 700;
                element.classList.add('gb-destroy-animation');
                return this.wait(duration).then(() => {
                    element.remove();
                });
            },

            getFromElement(from) {
                if (from.componentId !== undefined) {
                    return this.componentMgr.getComponentById(from.componentId);
                }
                if (from.soloBoard !== undefined) {
                    return document.querySelector('.gb-solo-board');
                }
                switch (parseInt(from.locationId)) {
                    case this.componentMgr.COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT:
                        return this.playerBoardMgr.getPlayerBoardDevelop(from.playerId, from.locationPrimaryOrder);
                    case this.componentMgr.COMPONENT_LOCATION_ID_SCORE:
                        return this.getPlayerPanelScoreElem(from.playerId);
                }
                return null;
            },

            showConfirmDialogIfConfirm(title = null, condition = true) {
                return this.showConfirmDialogCondition(
                    title === null
                        ? _('Are you sure? This cannot be undone.')
                        : title,
                    condition && this.mustConfirmActions()
                );
            },

            showConfirmDialogDrawBlue(id, side, hasBlueCards) {
                const mustCommit = (this.mustCommit(id, side) && this.mustConfirmActions());
                const emptyBlue = (this.willDrawBlueCard(id, side) && !this.isTrue(hasBlueCards));
                let msg = _('Are you sure? This cannot be undone.');
                if (emptyBlue) {
                    msg = _('Are you sure? You do not have any GOLDblivion cards to draw.');
                }
                return this.showConfirmDialogCondition(msg, mustCommit || emptyBlue);
            },

            mustConfirmActions() {
                return this.getLocalPreference(this.GB_PREF_ALWAYS_CONFIRM_ID);
            },

            componentHasSide(componentId) {
                if (!(componentId in gameui.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                return this.typeIdHasSide(gameui.gamedatas.componentIdToTypeId[componentId]);
            },

            typeIdHasSide(typeId) {
                if (!(typeId in gameui.gamedatas.componentDefs)) {
                    debug('BUG! Unknown typeId: ' + typeId);
                    return false;
                }
                const def = gameui.gamedatas.componentDefs[typeId];
                return (def.abilities.length > 1);
            },

            isSingleClick(componentId, side) {
                if (!this.isPrefSingleClick()) {
                    return false;
                }
                if (this.mustCommit(componentId, side)) {
                    return false;
                }
                return true;
            },

            isPrefSingleClick() {
                return this.getLocalPreference(this.GB_PREF_SINGLE_CLICK_ID);
            },

            mustCommit(componentId, side = null) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                if (side === null) {
                    if (def.abilities.length > 1) {
                        return false;
                    }
                    side = 0;
                }
                for (const gain of def.abilities[side].gains) {
                    switch (parseInt(gain.gainTypeId)) {
                        case this.GAIN_ID_DRAW_BLUE_CARD:
                        case this.GAIN_ID_ROLL_DICE:
                        case this.GAIN_ID_GAIN_MAGIC:
                            return true;
                        case this.GAIN_ID_DRAW_RED_CARD:
                            if (def.categoryId == this.componentMgr.COMPONENT_CATEGORY_ID_CARD_RED) {
                                return true;
                            }
                    }
                }
                return false;
            },

            willDrawBlueCard(componentId, side = null) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                if (side === null) {
                    if (def.abilities.length > 1) {
                        return false;
                    }
                    side = 0;
                }
                for (const gain of def.abilities[side].gains) {
                    switch (parseInt(gain.gainTypeId)) {
                        case this.GAIN_ID_DRAW_BLUE_CARD:
                            return true;
                    }
                }
                return false;
            },

            getComponentCost(componentId) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                return def.cost;
            },

            getComponentName(componentId) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                return _(def.name);
            },

            isComponentCardBlue(componentId) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                return (def.categoryId == this.componentMgr.COMPONENT_CATEGORY_ID_CARD_BLUE);
            },

            isComponentCardRed(componentId) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                return (def.categoryId == this.componentMgr.COMPONENT_CATEGORY_ID_CARD_RED);
            },

            hasFreeBlueCardAbility(componentId, side = null) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                if (side === null) {
                    if (def.abilities.length > 1) {
                        return false;
                    }
                    side = 0;
                }
                for (const gain of def.abilities[side].gains) {
                    switch (parseInt(gain.gainTypeId)) {
                        case this.GAIN_ID_GAIN_FREE_BLUE_HUMAN_CARD:
                            return true;
                    }
                }
                return false;
            },

            hasFreeRedCardAbility(componentId, side = null) {
                if (!(componentId in this.gamedatas.componentIdToTypeId)) {
                    debug('BUG! Unknown componentId: ' + componentId);
                    return false;
                }
                const def = this.gamedatas.componentDefs[this.gamedatas.componentIdToTypeId[componentId]];
                if (side === null) {
                    if (def.abilities.length > 1) {
                        return false;
                    }
                    side = 0;
                }
                for (const gain of def.abilities[side].gains) {
                    switch (parseInt(gain.gainTypeId)) {
                        case this.GAIN_ID_GAIN_FREE_RED_CARD:
                            return true;
                    }
                }
                return false;
            },

            getSoloNobleName() {
                return _(this.gamedatas.soloBoardDef[this.gamedatas.soloNoble].name);
            },

            autoScroll(elem, playerId, isInstantaneous) {
                if (isInstantaneous || gameui.isFastMode() || elem === null) {
                    return;
                }
                if (playerId !== null && playerId != this.player_id) {
                    return;
                }
                if (!this.getLocalPreference(this.GB_PREF_AUTO_SCROLL_SELF_ID)) {
                    return;
                }
                elem.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            },

            hideAllEnemyCombatCardDraw() {
                for (const e of document.querySelectorAll('.gb-enemy-draw')) {
                    e.classList.add('bx-hidden');
                }
            },
        });
    });