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
        return declare("gb.PlayerPanelMgr", null, {
            setup(gamedatas) {
                for (const playerId in gamedatas.playerStates) {
                    const ps = gamedatas.playerStates[playerId];
                    this.updatePlayerPassed(playerId, ps.passed);
                    this.createPlayerPanel(playerId);
                }

                const first = document.createElement('div');
                first.classList.add('gb-first-player');
                const firstContainer = this.getFirstPlayerContainerElem(gamedatas.roundFirstPlayerId)
                firstContainer.appendChild(first);

                this.setupSoloNoblePanel();
                this.setupOptionPanel();
                this.setupScrollShortcuts();

                this.updateHandCounts(gamedatas.componentCounts.handCounts, true);
                this.updateMagicCount(gamedatas.componentCounts.magicPlayerBoardCounts, true);
                gameui.counters.round.setValue(gamedatas.round);
            },

            setupSoloNoblePanel() {
                if (!gameui.isGameSolo()) {
                    return;
                }
                const soloPanel = gameui.addPlayerPanel();

                let row = gameui.appendPlayerPanelRowToBoardElem(soloPanel);
                const nameElem = document.createElement('div');
                nameElem.classList.add('player-name');
                nameElem.innerText = gameui.getSoloNobleName();
                row.appendChild(nameElem);

                const shieldRow = gameui.appendPlayerPanelRowToBoardElem(soloPanel);
                const shield = gameui.createShieldElementFromColorName(gameui.gamedatas.soloNobleColorName);
                shield.style.setProperty('--gb-zoom', 0.3);
                shieldRow.appendChild(shield);

                // Counter rows
                row = null;
                for (const info of this.getSoloCounters()) {
                    if (info === null) {
                        row = null;
                        continue;
                    }
                    if (row === null) {
                        row = gameui.appendPlayerPanelRowToBoardElem(soloPanel);
                        row.style.setProperty('--gb-zoom', 0.25);
                    }
                    const elem = gameui.createPillElem(
                        info[1],
                        info[2],
                        (e) => info[0].addTarget(e)
                    );
                    row.appendChild(elem);
                    gameui.addBasicTooltipToElement(elem, info[3]);
                }

                // Icon row
                row = null;
                for (const info of gameui.getBlueIcons()) {
                    if (row === null) {
                        row = gameui.appendPlayerPanelRowToBoardElem(soloPanel);
                        row.style.setProperty('--gb-zoom', 0.35);
                    }
                    const elem = gameui.createPillElem(
                        'gb-blue-icon-' + info[0],
                        null,
                        (e) => gameui.counters.solo.icon[info[0]].addTarget(e)
                    );
                    row.appendChild(elem);
                    gameui.addBasicTooltipToElement(elem, info[2]);
                }
            },

            createPlayerPanel(playerId) {
                // Replace star with gold
                const starElem = document.getElementById('icon_point_' + playerId);
                if (starElem != null) {
                    starElem.classList.add('bx-invisible');

                    const goldElem = gameui.createGoldElement();
                    goldElem.style.setProperty('--gb-zoom', 0.25);

                    // Sparkle
                    const delay = Math.floor(Math.random() * 100) + 's';
                    const sparkle1Elem = document.createElement('div');
                    sparkle1Elem.classList.add('gb-sparkle');
                    sparkle1Elem.classList.add('gb-first');
                    sparkle1Elem.style.setProperty('--gb-zoom', 0.7);
                    sparkle1Elem.style.setProperty('--gb-sparkle-animation-delay', delay);
                    const sparkle2Elem = document.createElement('div');
                    sparkle2Elem.classList.add('gb-sparkle');
                    sparkle2Elem.classList.add('gb-second');
                    sparkle2Elem.style.setProperty('--gb-zoom', 0.7);
                    sparkle2Elem.style.setProperty('--gb-sparkle-animation-delay', delay);
                    goldElem.appendChild(sparkle1Elem);
                    goldElem.appendChild(sparkle2Elem);

                    starElem.parentElement.insertBefore(goldElem, starElem);
                }

                const shieldRow = gameui.appendPlayerPanelRow(playerId);
                const shield = gameui.createShieldElement(playerId);
                shield.style.setProperty('--gb-zoom', 0.3);
                shieldRow.appendChild(shield);

                // Counter rows
                let row = null;

                for (const info of this.getCounters(playerId)) {
                    if (info === null) {
                        row = null;
                        continue;
                    }
                    if (row === null) {
                        row = gameui.appendPlayerPanelRow(playerId);
                        row.style.setProperty('--gb-zoom', 0.25);
                    }
                    const elem = gameui.createPillElem(
                        info[1],
                        info[2],
                        (e) => info[0].addTarget(e)
                    );
                    row.appendChild(elem);
                    gameui.addBasicTooltipToElement(elem, info[3]);
                }

                // Icon row
                row = null;
                for (const info of gameui.getBlueIcons()) {
                    if (row === null) {
                        row = gameui.appendPlayerPanelRow(playerId);
                        row.style.setProperty('--gb-zoom', 0.35);
                    }
                    const elem = gameui.createPillElem(
                        'gb-blue-icon-' + info[0],
                        null,
                        (e) => gameui.counters[playerId].icon[info[0]].addTarget(e)
                    );
                    row.appendChild(elem);
                    gameui.addBasicTooltipToElement(elem, info[1]);
                }

                const firstPlayerRow = gameui.appendPlayerPanelRow(playerId);
                const firstPlayer = document.createElement('div');
                firstPlayer.classList.add('gb-first-player-container');
                firstPlayer.style.setProperty('--gb-zoom', 0.7);
                firstPlayerRow.appendChild(firstPlayer);
                if (gameui.isGameSolo()) {
                    firstPlayerRow.classList.add('bx-hidden');
                }
            },

            getCounters(playerId) {
                return [
                    [gameui.counters[playerId].hand, 'gb-hand', null, _('Number of cards in hand')],
                    [gameui.counters[playerId].nugget, 'gb-nugget', null, _('Number of nuggets')],
                    [gameui.counters[playerId].material, 'gb-material', null, _('Number of material')],
                    [gameui.counters[playerId].magic, ['gb-component', 'gb-magic'], (e) => {
                        e.dataset.typeId = gameui.componentMgr.COMPONENT_TYPE_ID_BACK_MAGIC;
                        e.style.setProperty('--gb-zoom', 0.1);
                    }, _('Number of magic tokens')],
                    null,
                    [gameui.counters[playerId].deckBlue, ['gb-component', 'gb-card-blue'], (e) => {
                        e.dataset.typeId = gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_BLUE;
                        e.style.setProperty('--gb-zoom', 0.05);
                    }, _('Number of GOLDblivion cards in deck')],
                    [gameui.counters[playerId].deckRed, ['gb-component', 'gb-card-red'], (e) => {
                        e.dataset.typeId = gameui.componentMgr.COMPONENT_TYPE_ID_BACK_CARD_RED;
                        e.style.setProperty('--gb-zoom', 0.055);
                    }, _('Number of combat cards in deck')],
                    [gameui.counters[playerId].development[0], 'gb-nugget-production', null, _('Number of cards in nugget production and Number of nuggets produced each round')],
                    [gameui.counters[playerId].development[1], 'gb-material-production', null, _('Number of cards in material production (Number of material produced each round)')],
                ];
            },

            getSoloCounters() {
                return [
                    [gameui.counters.solo.nugget, 'gb-nugget', null, _('Number of nuggets')],
                    [gameui.counters.solo.material, 'gb-material', null, _('Number of material')],
                    [gameui.counters.solo.gold, 'gb-gold', null, _('Number of gold')],
                ];
            },

            getFirstPlayerContainerElem(playerId) {
                return gameui.getPlayerPanelBoardElem(playerId).querySelector('.gb-first-player-container');
            },

            updatePlayerPassed(playerId, passed) {
                const playerBoardElem = gameui.getOverallPlayerPanelBoardElem(playerId);
                if (gameui.isTrue(passed)) {
                    playerBoardElem.classList.add('gb-player-panel-passed');
                } else {
                    playerBoardElem.classList.remove('gb-player-panel-passed');
                }
            },

            moveFirstPlayerToken(playerId) {
                const first = document.querySelector('.gb-first-player');
                const targetElem = this.getFirstPlayerContainerElem(playerId);
                return gameui.slide(first, targetElem, {
                    phantom: true,
                });
            },

            updateHandCounts(handCounts, isInstantaneous = false) {
                for (const playerId in handCounts) {
                    const count = handCounts[playerId]
                    gameui.counters[playerId].hand.toValue(count, isInstantaneous);
                }
            },

            updateMagicCount(magicPlayerBoardCounts, isInstantaneous = false) {
                for (const playerId in magicPlayerBoardCounts) {
                    const count = magicPlayerBoardCounts[playerId]
                    gameui.counters[playerId].magic.toValue(count, isInstantaneous);
                }
            },

            setupScrollShortcuts() {
                const areaElem = document.getElementById('gb-shortcut-area');
                if (gameui.getLocalPreference(gameui.GB_PREF_SHORTCUTS_ID)) {
                    document.body.classList.remove('gb-shortcuts-hidden');
                } else {
                    document.body.classList.add('gb-shortcuts-hidden');

                }
                areaElem.innerHTML = '';
                areaElem.appendChild(this.createScrollShortcut(
                    _('Top'),
                    document.body
                ));

                let searchPlayerId = null;
                if (gameui.player_id in gameui.gamedatas.players) {
                    searchPlayerId = gameui.player_id;
                    if (gameui.getLocalPreference(gameui.GB_PREF_HAND_POSITION_ID) == gameui.GB_PREF_HAND_POSITION_VALUE_MIDDLE) {
                        areaElem.appendChild(this.createScrollShortcut(
                            _('Hand'),
                            document.getElementById('gb-area-card-hand-container')
                        ));
                    }
                    areaElem.appendChild(this.createScrollShortcut(
                        _('Board'),
                        document.getElementById('gb-area-player-' + searchPlayerId)
                    ));
                }
                for (const playerId in gameui.gamedatas.players) {
                    if (playerId == searchPlayerId) {
                        continue;
                    }
                    areaElem.appendChild(this.createScrollShortcut(
                        gameui.gamedatas.players[playerId].player_name,
                        document.getElementById('gb-area-player-' + playerId),
                        true
                    ));
                }
                if (gameui.isGameSolo()) {
                    areaElem.appendChild(this.createScrollShortcut(
                        _('Solo Noble'),
                        document.querySelector('.gb-area-solo-container')
                    ));
                }

                areaElem.appendChild(this.createScrollShortcut(
                    _('Components'),
                    document.getElementById('gb-discarded-help')
                ));

                if (this.shortcutsScrollListener) {
                    dojo.disconnect(this.shortcutsScrollListener);
                }
                this.shortcutsScrollListener = dojo.connect(window, 'scroll', () => {
                    const shortcutRect = areaElem.getBoundingClientRect();

                    const pageContentElem = document.getElementById('page-content');
                    const pageContentRect = pageContentElem.getBoundingClientRect();
                    if (shortcutRect.bottom < pageContentRect.bottom) {
                        areaElem.classList.remove('bx-invisible');
                    } else {
                        areaElem.classList.add('bx-invisible');
                    }
                });
            },

            createScrollShortcut(title, scrollToElem, isPlayer = false) {
                const elem = document.createElement('div');
                if (isPlayer) {
                    elem.classList.add('gb-shortcut-is-player');
                }
                elem.addEventListener('click', () => {
                    scrollToElem.scrollIntoView();
                    window.scrollBy(0, -1 * document.getElementById('page-title').offsetHeight);
                });
                elem.innerText = title;
                return elem;
            },

            setupOptionPanel() {
                const newPanel = gameui.addPlayerPanel();
                newPanel.classList.add('gb-option-panel');
                let rowElem = null;

                // Round
                rowElem = this.createPlayerPanelRow(newPanel);
                rowElem.classList.add('gb-panel-round');
                const roundText = document.createElement('div');
                roundText.innerHTML = _('Round') + ':&nbsp;';
                rowElem.appendChild(roundText);
                const roundCount = document.createElement('div');
                roundCount.innerText = '0';
                rowElem.appendChild(roundCount);
                gameui.counters.round.addTarget(roundCount);

                rowElem = this.createPlayerPanelRow(newPanel);
                rowElem.classList.add('gb-panel-option-row');
                const optionText = document.createElement('div');
                optionText.innerHTML = _('Options:');
                rowElem.appendChild(optionText);
                const gear = gameui.createFAIcon('gear');
                rowElem.appendChild(gear);
                gear.addEventListener('click', () => {
                    document.getElementById('gb-option-panel-toggle').classList.toggle('bx-hidden');
                    gameui.adaptPlayersPanels();
                });

                const optionPanel = document.createElement('div');
                optionPanel.id = 'gb-option-panel-toggle';
                optionPanel.classList.add('bx-hidden');
                newPanel.appendChild(optionPanel);

                // Welcome message
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-welcome-message-checkbox', _('Welcome message')));
                const welcomeCheckbox = document.getElementById('gb-welcome-message-checkbox');
                welcomeCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_WELCOME_MESSAGE_ID, welcomeCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Shows the welcome message when the game loads'));

                // Component help
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-component-help-checkbox', _('Component help')));
                const componentHelpCheckbox = document.getElementById('gb-component-help-checkbox');
                componentHelpCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_COMPONENT_HELP_ID, componentHelpCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display Help button under game components'));

                // Hand sort
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-hand-sort-checkbox', _('Hand sorting')));
                const handSortCheckbox = document.getElementById('gb-hand-sort-checkbox');
                handSortCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_HAND_SORT_ID, handSortCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display Sorting buttons under cards in hand'));

                // Hand position
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.classList.add('gb-card-position-container');
                const handPositionText = document.createElement('div');
                handPositionText.innerHTML = _('Hand position');
                rowElem.appendChild(handPositionText);
                const posMiddle = document.createElement('div');
                posMiddle.classList.add('gb-card-position');
                posMiddle.classList.add('gb-card-position-middle');
                rowElem.appendChild(posMiddle);
                posMiddle.addEventListener('click', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_HAND_POSITION_ID, gameui.GB_PREF_HAND_POSITION_VALUE_MIDDLE);
                });
                gameui.addBasicTooltipToElement(posMiddle, _('Display hand of card above the player board'));
                const posBottom = document.createElement('div');
                posBottom.classList.add('gb-card-position');
                posBottom.classList.add('gb-card-position-bottom');
                rowElem.appendChild(posBottom);
                posBottom.addEventListener('click', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_HAND_POSITION_ID, gameui.GB_PREF_HAND_POSITION_VALUE_BOTTOM);
                });
                gameui.addBasicTooltipToElement(posBottom, _('Display hand of card at the bottom left of the screen'));

                // Auto scroll self
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-auto-scroll-self-checkbox', _('Auto scroll')));
                const autoScrollSelfCheckbox = document.getElementById('gb-auto-scroll-self-checkbox');
                autoScrollSelfCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_AUTO_SCROLL_SELF_ID, autoScrollSelfCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Automatically scroll for some actions'));

                // Shortcuts
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-shortcuts-checkbox', _('Shortcuts')));
                const shortcutsCheckbox = document.getElementById('gb-shortcuts-checkbox');
                shortcutsCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_SHORTCUTS_ID, shortcutsCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display a planel of shortcuts to navigate the page'));

                // Dark background
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-dark-background-checkbox', _('Dark background')));
                const darkBackgroundCheckbox = document.getElementById('gb-dark-background-checkbox');
                darkBackgroundCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_DARK_BACKGROUND_ID, darkBackgroundCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display a dark background or the classic background'));

                // Single click
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-single-click-checkbox', _('1-click undoable actions')));
                const singleClickCheckbox = document.getElementById('gb-single-click-checkbox');
                singleClickCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_SINGLE_CLICK_ID, singleClickCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Use only one click for actions that can be undone'));

                // Confirm all actions
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('gb-always-confirm-checkbox', _('Always Confirm')));
                const alwaysConfirmCheckbox = document.getElementById('gb-always-confirm-checkbox');
                alwaysConfirmCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_ALWAYS_CONFIRM_ID, alwaysConfirmCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Always ask confirmation for all actions that cannot be undone'));

                // Zoom
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.innerText = _('Component Zoom');
                rowElem = this.createPlayerPanelRow(optionPanel);
                const zoomSliderElem = this.createZoomSlider('gb-zoom-slider');
                zoomSliderElem.addEventListener('input', () => {
                    gameui.setLocalPreference(gameui.GB_PREF_ZOOM_FACTOR_ID, parseInt(zoomSliderElem.value));
                });
                rowElem.appendChild(zoomSliderElem)
                gameui.addBasicTooltipToElement(rowElem, _('Change the size the components'));
            },

            createPlayerPanelRow(parentElem) {
                const rowElem = document.createElement('div');
                rowElem.classList.add('gb-player-panel-row');
                parentElem.appendChild(rowElem);
                return rowElem;
            },

            createZoomSlider(id) {
                const sliderElem = document.createElement('input');
                sliderElem.id = id;
                sliderElem.type = 'range';
                sliderElem.min = 20;
                sliderElem.max = 100;
                sliderElem.step = 5;
                sliderElem.value = 40;
                return sliderElem;
            },
        });
    });
