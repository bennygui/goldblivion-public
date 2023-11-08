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
        return declare("gb.ComponentMgr", null, {
            COMPONENT_CATEGORY_ID_CARD_BLUE: 1,
            COMPONENT_CATEGORY_ID_CARD_RED: 2,
            COMPONENT_CATEGORY_ID_VILLAGE: 3,
            COMPONENT_CATEGORY_ID_MAGIC: 4,
            COMPONENT_CATEGORY_ID_ENEMY: 5,

            COMPONENT_SUB_CATEGORY_ID_NOBLE: 1,
            COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE: 2,
            COMPONENT_SUB_CATEGORY_ID_PLAYER_RED: 3,
            COMPONENT_SUB_CATEGORY_ID_PLAYER_GREEN: 4,
            COMPONENT_SUB_CATEGORY_ID_PLAYER_YELLOW: 5,
            COMPONENT_SUB_CATEGORY_ID_DECK: 6,
            COMPONENT_SUB_CATEGORY_ID_LEFT: 7,
            COMPONENT_SUB_CATEGORY_ID_RIGHT: 8,
            COMPONENT_SUB_CATEGORY_ID_FOREST: 9,
            COMPONENT_SUB_CATEGORY_ID_MOUNTAIN: 10,
            COMPONENT_SUB_CATEGORY_ID_PERMANENT: 11,

            COMPONENT_LOCATION_ID_SUPPLY: 1,
            COMPONENT_LOCATION_ID_MARKET: 2,
            COMPONENT_LOCATION_ID_PLAYER_DECK: 3,
            COMPONENT_LOCATION_ID_PLAYER_HAND: 4,
            COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA: 5,
            COMPONENT_LOCATION_ID_PLAYER_DEVELOPMENT: 6,
            COMPONENT_LOCATION_ID_DRAFT_MARKET: 7,
            COMPONENT_LOCATION_ID_DISCARD: 8,
            COMPONENT_LOCATION_ID_PLAYER_BOARD: 9,
            COMPONENT_LOCATION_ID_SUPPLY_VISIBLE: 10,
            COMPONENT_LOCATION_ID_SCORE: 11,
            COMPONENT_LOCATION_ID_PLAYER_PLAY_AREA_BUILDING: 12,
            COMPONENT_LOCATION_ID_SOLO_BOARD: 13,

            COMPONENT_TYPE_ID_BACK_CARD_BLUE: 9999,
            COMPONENT_TYPE_ID_BACK_CARD_RED: 9998,
            COMPONENT_TYPE_ID_BACK_CARD_RED_DECK: 9997,
            COMPONENT_TYPE_ID_BACK_MAGIC: 9996,
            COMPONENT_TYPE_ID_BACK_ENEMY_FOREST: 9995,
            COMPONENT_TYPE_ID_BACK_ENEMY_MOUNTAIN: 9994,

            setup(gamedatas) {
                this.componentDefs = gamedatas.componentDefs;

                const elemCreationElem = gameui.getElementCreationElement();
                for (const c of Object.values(gamedatas.components)) {
                    const elem = this.createComponentElem(c.typeId, c.componentId);
                    if (elem !== null) {
                        elemCreationElem.appendChild(elem);
                        this.useComponent(c.componentId, c.isUsed, true);
                    }
                }

                this.createComponentList(
                    document.getElementById('gb-detail-list-initial-blue'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_CARD_BLUE && c.subCategoryId == this.COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE
                );
                this.createComponentList(
                    document.getElementById('gb-detail-list-initial-red'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_CARD_RED && c.subCategoryId == this.COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE
                );
                this.createComponentList(
                    document.getElementById('gb-detail-list-deck-blue'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_CARD_BLUE && c.subCategoryId == this.COMPONENT_SUB_CATEGORY_ID_DECK
                );
                this.createComponentList(
                    document.getElementById('gb-detail-list-deck-red'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_CARD_RED && c.subCategoryId == this.COMPONENT_SUB_CATEGORY_ID_DECK
                );
                this.createComponentList(
                    document.getElementById('gb-detail-list-magic'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_MAGIC,
                    false
                );
                this.createComponentList(
                    document.getElementById('gb-detail-list-enemy'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_ENEMY && c.subCategoryId != this.COMPONENT_SUB_CATEGORY_ID_PERMANENT
                );
                this.createComponentList(
                    document.getElementById('gb-detail-list-village'),
                    Object.values(gamedatas.componentDefs),
                    (c) => c.categoryId == this.COMPONENT_CATEGORY_ID_VILLAGE,
                    false
                );
                const diceFaceContainer = document.getElementById('gb-detail-list-dice-face');
                for (let face = 0; face < 6; ++face) {
                    const faceElem = gameui.createDiceFace(face);
                    const containerElem = document.createElement('div');
                    containerElem.classList.add('gb-detail-list-container');
                    containerElem.appendChild(faceElem);
                    diceFaceContainer.appendChild(containerElem);
                }
            },

            getComponentById(componentId) {
                return document.getElementById('gb-component-id-' + componentId);
            },

            getComponentAbilitySide(componentId, side) {
                return this.getComponentById(componentId).querySelector('.gb-ability-side-' + side);
            },

            getSidesComponentById(componentId) {
                if (gameui.componentHasSide(componentId)) {
                    return [
                        this.getComponentAbilitySide(componentId, 0),
                        this.getComponentAbilitySide(componentId, 1),
                    ];
                } else {
                    return this.getComponentAbilitySide(componentId, 'all');
                }
            },

            getCategoryIdFromTypeId(typeId) {
                switch (parseInt(typeId)) {
                    case this.COMPONENT_TYPE_ID_BACK_CARD_BLUE:
                        return this.COMPONENT_CATEGORY_ID_CARD_BLUE;
                    case this.COMPONENT_TYPE_ID_BACK_CARD_RED:
                    case this.COMPONENT_TYPE_ID_BACK_CARD_RED_DECK:
                        return this.COMPONENT_CATEGORY_ID_CARD_RED;
                    case this.COMPONENT_TYPE_ID_BACK_VILLAGE:
                        return this.COMPONENT_CATEGORY_ID_VILLAGE;
                    case this.COMPONENT_TYPE_ID_BACK_MAGIC:
                        return this.COMPONENT_CATEGORY_ID_MAGIC;
                    case this.COMPONENT_TYPE_ID_BACK_ENEMY_FOREST:
                        return this.COMPONENT_CATEGORY_ID_ENEMY;
                    case this.COMPONENT_TYPE_ID_BACK_ENEMY_MOUNTAIN:
                        return this.COMPONENT_CATEGORY_ID_ENEMY;
                }
                return this.componentDefs[typeId].categoryId;
            },

            typeIdIsCardBlue(typeId) {
                return (this.getCategoryIdFromTypeId(typeId) == this.COMPONENT_CATEGORY_ID_CARD_BLUE);
            },

            typeIdIsCardRed(typeId) {
                return (this.getCategoryIdFromTypeId(typeId) == this.COMPONENT_CATEGORY_ID_CARD_RED);
            },

            typeIdIsVillage(typeId) {
                return (this.getCategoryIdFromTypeId(typeId) == this.COMPONENT_CATEGORY_ID_VILLAGE);
            },

            typeIdIsMagic(typeId) {
                return (this.getCategoryIdFromTypeId(typeId) == this.COMPONENT_CATEGORY_ID_MAGIC);
            },

            typeIdIsEnemy(typeId) {
                return (this.getCategoryIdFromTypeId(typeId) == this.COMPONENT_CATEGORY_ID_ENEMY);
            },

            createComponentElem(typeId, componentId = null) {
                const elem = document.createElement('div');
                elem.classList.add('gb-component');
                elem.dataset.typeId = typeId;
                if (componentId !== null) {
                    elem.id = 'gb-component-id-' + componentId;
                }
                switch (this.getCategoryIdFromTypeId(typeId)) {
                    case this.COMPONENT_CATEGORY_ID_CARD_BLUE:
                        elem.classList.add('gb-card-blue');

                        if (gameui.typeIdHasSide(typeId)) {
                            for (let i = 0; i < 2; ++i) {
                                const abilitySide = document.createElement('div');
                                abilitySide.classList.add('gb-ability-side-' + i);
                                elem.appendChild(abilitySide);
                            }
                        } else {
                            const abilitySideAll = document.createElement('div');
                            abilitySideAll.classList.add('gb-ability-side-all');
                            elem.appendChild(abilitySideAll);
                        }

                        if (componentId !== null) {
                            const handSort = document.createElement('div');
                            handSort.classList.add('gb-hand-sort-container');
                            elem.appendChild(handSort);

                            const toStart = document.createElement('div');
                            handSort.appendChild(toStart);
                            toStart.addEventListener('click', (e) => {
                                e.stopPropagation();
                                gameui.serverAction('handOrderToStart', { componentId: componentId })
                            });

                            const toLeft = document.createElement('div');
                            handSort.appendChild(toLeft);
                            toLeft.addEventListener('click', (e) => {
                                e.stopPropagation();
                                gameui.serverAction('handOrderToLeft', { componentId: componentId })
                            });

                            const toRight = document.createElement('div');
                            handSort.appendChild(toRight);
                            toRight.addEventListener('click', (e) => {
                                e.stopPropagation();
                                gameui.serverAction('handOrderToRight', { componentId: componentId })
                            });

                            const toEnd = document.createElement('div');
                            handSort.appendChild(toEnd);
                            toEnd.addEventListener('click', (e) => {
                                e.stopPropagation();
                                gameui.serverAction('handOrderToEnd', { componentId: componentId })
                            });
                        }
                        break;
                    case this.COMPONENT_CATEGORY_ID_CARD_RED:
                        elem.classList.add('gb-card-red');

                        if (gameui.typeIdHasSide(typeId)) {
                            for (let i = 0; i < 2; ++i) {
                                const abilitySide = document.createElement('div');
                                abilitySide.classList.add('gb-ability-side-' + i);
                                elem.appendChild(abilitySide);
                            }
                        } else {
                            const abilitySideAll = document.createElement('div');
                            abilitySideAll.classList.add('gb-ability-side-all');
                            elem.appendChild(abilitySideAll);
                        }
                        break;
                    case this.COMPONENT_CATEGORY_ID_VILLAGE:
                        elem.classList.add('gb-village');
                        break;
                    case this.COMPONENT_CATEGORY_ID_MAGIC:
                        elem.classList.add('gb-magic');
                        break;
                    case this.COMPONENT_CATEGORY_ID_ENEMY: {
                        elem.classList.add('gb-enemy-container');

                        const shadow = document.createElement('div');
                        shadow.classList.add('gb-enemy-shadow');
                        elem.appendChild(shadow);

                        const content = document.createElement('div');
                        content.classList.add('gb-component');
                        content.dataset.typeId = typeId;
                        content.classList.add('gb-enemy');
                        elem.appendChild(content);

                        const border = document.createElement('div');
                        border.classList.add('gb-enemy-border');
                        elem.appendChild(border);

                        const draw = document.createElement('div');
                        draw.classList.add('gb-enemy-draw');
                        draw.classList.add('bx-hidden');
                        elem.appendChild(draw);

                        const drawCount = document.createElement('div');
                        drawCount.classList.add('gb-enemy-draw-count');
                        draw.appendChild(drawCount);

                        const clickTarget = document.createElement('div');
                        clickTarget.classList.add('gb-enemy-click-target');
                        elem.appendChild(clickTarget);
                        break;
                    }
                    default:
                        debug('createComponentElem does not know the category of typeId ' + typeId);
                }
                const help = document.createElement('div');
                help.classList.add('gb-component-help');
                elem.appendChild(help);
                help.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.showComponentDetailDialog(typeId)
                });
                return elem;
            },

            updateFaceDownSupply(count, supplyElem, backTypeId, minimumCount = null, maximumCount = null, topTypeId = null) {
                const frontElems = [];
                for (const e of supplyElem.querySelectorAll('.gb-component')) {
                    if (e.parentElement != supplyElem) {
                        continue;
                    }
                    if (e.classList.contains('bx-moving')) {
                        continue;
                    }
                    e.remove();
                    if (e.dataset.typeId != backTypeId && (topTypeId === null || e.dataset.typeId != topTypeId)) {
                        frontElems.push(e);
                    }
                }
                let upTo = count;
                if (maximumCount !== null) {
                    upTo = Math.min(upTo, maximumCount)
                }
                if (minimumCount !== null) {
                    upTo = Math.max(upTo, minimumCount)
                }
                for (let i = 0; i < upTo; ++i) {
                    const e = this.createComponentElem(
                        (i + 1 == upTo && topTypeId !== null)
                            ? topTypeId
                            : backTypeId
                    );
                    if (i == 0 && count == 0) {
                        e.classList.add('gb-component-ghost');
                    }
                    supplyElem.appendChild(e);
                }
                for (const e of frontElems) {
                    supplyElem.appendChild(e);
                }
            },

            suffleDeck(supplyElem, backTypeId) {
                if (gameui.isFastMode()) {
                    return Promise.resolve();
                }
                return new Promise((resolve, reject) => {
                    // To be able to change the duration, would need to change the css
                    const duration = 600;
                    const under = this.createComponentElem(backTypeId);
                    const over = this.createComponentElem(backTypeId);
                    supplyElem.appendChild(under);
                    supplyElem.appendChild(over);
                    setTimeout(() => {
                        let i = 0;
                        for (const e of supplyElem.querySelectorAll('.gb-component')) {
                            if (e.classList.contains('bx-moving')) {
                                continue;
                            }
                            if (e != under && e != over) {
                                i = (i + 1) % 2;
                                e.classList.add('gb-shuffle-shake-animation-' + i);
                            }
                        }
                        under.classList.add('gb-shuffle-under-animation');
                        over.classList.add('gb-shuffle-over-animation');
                    }, 1);
                    setTimeout(() => {
                        under.remove();
                        over.remove();
                        for (const e of supplyElem.querySelectorAll('.gb-component')) {
                            e.classList.remove('gb-shuffle-shake-animation-0');
                            e.classList.remove('gb-shuffle-shake-animation-1');
                        }
                        resolve();
                    }, duration);
                });
            },

            discardComponent(componentId) {
                const elem = this.getComponentById(componentId);
                elem.classList.add('gb-destroy-animation-x');
                return gameui.discardElement(elem);
            },

            removeComponent(componentId) {
                const elem = this.getComponentById(componentId);
                return gameui.discardElement(elem);
            },

            sortAndResizeComponentsWithOverlap(containerElem, componentHeight, sortElemFct) {
                const componentElemArray = [];
                for (const componentElem of containerElem.querySelectorAll('.gb-component')) {
                    if (componentElem.classList.contains('bx-moving')) {
                        continue;
                    }
                    componentElemArray.push(componentElem);
                    componentElem.remove();
                }
                componentElemArray.sort(sortElemFct);
                for (const componentElem of componentElemArray) {
                    containerElem.appendChild(componentElem);
                }
                containerElem.style.minHeight = null;
                if (componentElemArray.length >= 1) {
                    containerElem.style.minHeight =
                        'calc('
                        + Math.max(
                            componentHeight,
                            componentHeight + 0.4 * componentHeight * (componentElemArray.length - 1)
                        )
                        + 'px * var(--gb-zoom))';
                }
                return Promise.resolve();
            },

            useComponent(componentId, isUsed, isInstantaneous = false) {
                const elem = this.getComponentById(componentId);
                if (elem === null) {
                    return Promise.resolve();
                }
                if (gameui.isTrue(isUsed)) {
                    elem.classList.add('gb-component-used');
                } else {
                    elem.classList.remove('gb-component-used');
                }
                return gameui.wait(300, isInstantaneous);
            },

            showComponentDetailDialog(typeIds, title = null, showDesc = true, showHelp = false, secondTitle = null, secondList = []) {
                if (!this.componentDialogId) {
                    this.componentDialogId = 0;
                }
                this.componentDialogId += 1;
                if (!(typeIds instanceof Array)) {
                    if (title === null) {
                        title = _(gameui.gamedatas.componentDefs[typeIds].name);
                    }
                    typeIds = [typeIds];
                }
                const allLists = [typeIds, secondList];
                const dialog = new bx.ModalDialog('gb-component-detail-dialog-' + this.componentDialogId, {
                    title: secondTitle == null ? title : '',
                    contentsTpl: `<div class='gb-component-detail-container'></div>`,
                    onShow: () => {
                        const fullContainerElem = document.getElementById('popin_gb-component-detail-dialog-' + this.componentDialogId);
                        if (!showHelp) {
                            fullContainerElem.classList.add('gb-component-help-hidden');
                        }
                        let zoom = fullContainerElem.offsetWidth / 400;
                        if (zoom > 1) {
                            zoom = 1;
                        }
                        if (typeIds.length + secondList.length > 1) {
                            zoom = zoom / 2.0;
                        }
                        const detailElem = document.querySelector('#popin_gb-component-detail-dialog-' + this.componentDialogId + ' .gb-component-detail-container');

                        for (let i = 0; i < allLists.length; ++i) {
                            if (secondTitle !== null) {
                                const container = document.createElement('div');
                                container.classList.add('gb-detail-dialog-inner-title');
                                if (i == 0) {
                                    container.innerHTML = title;
                                } else {
                                    container.innerHTML = secondTitle;
                                }
                                detailElem.appendChild(container);
                            }
                            for (const typeId of allLists[i]) {
                                const container = document.createElement('div');
                                container.classList.add('gb-component-and-desc');
                                detailElem.appendChild(container);

                                const componentElem = this.createComponentElem(typeId);
                                componentElem.style.setProperty('--gb-zoom', zoom);
                                container.appendChild(componentElem);

                                const desc = gameui.gamedatas.componentDefs[typeId].desc;
                                if (showDesc && desc) {
                                    const descElem = document.createElement('div');
                                    descElem.classList.add('gb-component-detail-desc');
                                    descElem.innerText = _(desc);
                                    container.appendChild(descElem);
                                }
                            }
                        }
                    },
                });
                dialog.show();
            },

            createComponentList(parentElement, componentList, filterFct = null, displayCount = true) {
                for (const c of componentList) {
                    if (filterFct !== null && !filterFct(c)) {
                        continue;
                    }
                    const componentElem = this.createComponentElem(c.typeId);

                    const countElem = document.createElement('div');
                    countElem.classList.add('gb-detail-list-count');
                    countElem.innerText = c.setupCount + 'x';

                    const containerElem = document.createElement('div');
                    containerElem.classList.add('gb-detail-list-container');
                    containerElem.appendChild(componentElem);
                    if (displayCount) {
                        containerElem.appendChild(countElem);
                    }

                    parentElement.appendChild(containerElem);
                }
            },

            createTypeIdList(parentElement, typeIdList) {
                typeIdList.sort();
                for (const typeId of typeIdList) {
                    const componentElem = this.createComponentElem(typeId);
                    parentElement.appendChild(componentElem);
                }
            },
        });
    });