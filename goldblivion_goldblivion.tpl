{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- goldblivion implementation : © Guillaume Benny bennygui@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    goldblivion_goldblivion.tpl
-->
<div id="gb-display-last-round" class="bx-hidden">
    <div>{DISPLAY_LAST_ROUND}</div>
</div>

<div id='gb-area-full'>
    <div id='gb-area-common-container'>
        <div id='gb-area-common'>
            <div id='gb-magic-supply-container'></div>
            <div id='gb-main-board-wrap'>
                <div id='gb-main-board'>
                    <div id='gb-boat-container'>
                        <div class='gb-boat'></div>
                    </div>
                    <div id='gb-score-container-0' class='gb-score-container'></div>
                    <div id='gb-score-container-1' class='gb-score-container'></div>
                    <div id='gb-score-container-2' class='gb-score-container'></div>
                    <div id='gb-score-container-3' class='gb-score-container'></div>
                    <div id='gb-score-container-4' class='gb-score-container'></div>
                    <div id='gb-score-container-5' class='gb-score-container'></div>
                    <div id='gb-score-container-6' class='gb-score-container'></div>
                    <div id='gb-score-container-7' class='gb-score-container'></div>
                    <div id='gb-score-container-8' class='gb-score-container'></div>
                    <div id='gb-score-container-9' class='gb-score-container'></div>
                    <div id='gb-score-container-10' class='gb-score-container'></div>
                    <div id='gb-village-container-0'></div>
                    <div id='gb-village-container-1'></div>
                    <div id='gb-card-blue-supply-container-wrap'>
                        <div id='gb-card-blue-supply-container'></div>
                        <div id='gb-card-blue-supply-count' class='gb-counter'>0</div>
                    </div>
                    <div id='gb-card-red-supply-container-wrap-0'>
                        <div id='gb-card-red-supply-container-0'></div>
                        <div id='gb-card-red-supply-count-0' class='gb-counter'>0</div>
                    </div>
                    <div id='gb-card-red-supply-container-wrap-1'>
                        <div id='gb-card-red-supply-container-1'></div>
                        <div id='gb-card-red-supply-count-1' class='gb-counter'>0</div>
                    </div>
                    <div id='gb-enemy-supply-container-1' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-2' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-3' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-4' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-5' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-6' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-7' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-8' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-9' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-10' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-11' class='gb-enemy-supply-container'></div>
                    <div id='gb-enemy-supply-container-12' class='gb-enemy-supply-container'></div>
                </div>
                <div id='gb-card-blue-market-container'></div>
            </div>
        </div>
    </div>
    <div id='gb-area-card-draft-container'></div>
    <div id='gb-area-card-hand-container'>
        <div id='gb-area-card-hand'>
            <div id='gb-area-card-hand-empty'>{NO_CARDS_IN_HAND}</div>
        </div>
    </div>
    <div class='gb-area-player-container'>
        <!-- BEGIN player-area -->
        <div id='gb-area-player-{PLAYER_ID}' class='gb-area-player' data-player-id='{PLAYER_ID}'>
            <div class='gb-area-player-title'>
                <h3><span class='player-name' style='color: #{PLAYER_COLOR};'>{PLAYER_NAME}</span></h3>
            </div>
            <div class='gb-area-player-wrap'>
                <div class='gb-area-played-red'>
                    <div class='gb-area-played-red-0'>
                        <div class='gb-red-title'>{RED_FIGHTER}</div>
                        <div class='gb-red-power-counter-container'>
                            <div class='gb-red-power-counter-name'>
                                <span class='player-name' style='color: #{PLAYER_COLOR};'>{PLAYER_NAME}</span>
                            </div>
                            <div class='gb-red-power-counter'></div>
                        </div>
                        <div class='gb-red-power-counter-container'>
                            <div class='gb-red-power-counter-name'>
                                {ENEMY}
                            </div>
                            <div class='gb-red-enemy-power-counter'></div>
                        </div>
                        <div class='gb-switch-container gb-switch-compact-red-0'>
                            <label class='bx-checkbox-switch'><span>{COMPACT}</span><input id='a' type='checkbox' checked='checked'><i></i></label>
                        </div>
                        <div class='gb-red-played-container-0'></div>
                    </div>
                    <div class='gb-area-played-red-1'>
                        <div class='gb-red-title'>{RED_EXHAUSTED}</div>
                        <div class='gb-switch-container gb-switch-compact-red-1'>
                            <label class='bx-checkbox-switch'><span>{COMPACT}</span><input id='a' type='checkbox' checked='checked'><i></i></label>
                        </div>
                        <div class='gb-red-played-container-1'></div>
                    </div>
                </div>
                <div class='gb-player-board-wrap'>
                    <div class='gb-player-board'>
                        <div class='gb-player-board-shield'>
                            <div class='gb-shield'></div>
                        </div>
                        <div class='gb-development-side-0'></div>
                        <div class='gb-development-side-0-counter gb-counter'>0</div>
                        <div class='gb-development-side-1'></div>
                        <div class='gb-development-side-1-counter gb-counter'>0</div>
                        <div class='gb-development-help'></div>

                        <div class='gb-nugget-counter gb-counter'>0</div>
                        <div class='gb-nugget-box'></div>

                        <div class='gb-material-counter gb-counter'>0</div>
                        <div class='gb-material-box'></div>

                        <div class='gb-player-magic-container'></div>
                    </div>
                    <div class='gb-player-deck-container'>
                        <div class='gb-player-deck-red-container-wrap'>
                            <div class='gb-player-deck-red-container'></div>
                            <div class='gb-player-deck-red-count gb-counter'>0</div>
                            <div class='gb-player-deck-red-help gb-component-help'></div>
                        </div>
                        <div class='gb-player-deck-blue-container-wrap'>
                            <div class='gb-player-deck-blue-container'></div>
                            <div class='gb-player-deck-blue-count gb-counter'>0</div>
                            <div class='gb-player-deck-blue-help gb-component-help'></div>
                        </div>
                    </div>
                    <div class='gb-player-enemy-container'></div>
                </div>
                <div class='gb-area-played-blue'>
                    <div class='gb-blue-icons'>
                        <div class='bx-pill'>
                            <div class='gb-blue-icon-human'></div>
                            <div class='bx-pill-counter'>0</div>
                        </div>
                        <div class='bx-pill'>
                            <div class='gb-blue-icon-elf'></div>
                            <div class='bx-pill-counter'>0</div>
                        </div>
                        <div class='bx-pill'>
                            <div class='gb-blue-icon-dwarf'></div>
                            <div class='bx-pill-counter'>0</div>
                        </div>
                        <div class='bx-pill'>
                            <div class='gb-blue-icon-building'></div>
                            <div class='bx-pill-counter'>0</div>
                        </div>
                        <div class='bx-pill'>
                            <div class='gb-blue-icon-enemy'></div>
                            <div class='bx-pill-counter'>0</div>
                        </div>
                    </div>
                    <div class='gb-switch-container gb-switch-compact-blue'>
                        <label class='bx-checkbox-switch'><span>{COMPACT}</span><input id='a' type='checkbox' checked='checked'><i></i></label>
                    </div>
                    <div class='gb-blue-played-cards-wrap'>
                        <div class='gb-blue-building-container'></div>
                        <div class='gb-blue-played-container'></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END player-area -->
    </div>
    <div class='gb-area-solo-container-wrap'>
        <div class='gb-area-solo-container bx-hidden'>
            <div class='gb-solo-icons'></div>
            <div class='gb-solo-board'>
                <div class='gb-solo-board-card-icon-1'></div>
                <div class='gb-solo-board-card-icon-1-counter gb-counter'>0</div>
                <div class='gb-solo-board-card-icon-2'></div>
                <div class='gb-solo-board-card-icon-2-counter gb-counter'>0</div>
                <div class='gb-solo-board-card-icon-3'></div>
                <div class='gb-solo-board-card-icon-3-counter gb-counter'>0</div>
                <div class='gb-solo-board-card-icon-4'></div>
                <div class='gb-solo-board-card-icon-4-counter gb-counter'>0</div>

                <div class='bx-pill bp-solo-board-nugget-count'>
                    <div class='gb-nugget'></div>
                    <div class='bx-pill-counter'>0</div>
                </div>
                <div class='bx-pill bp-solo-board-material-count'>
                    <div class='gb-material'></div>
                    <div class='bx-pill-counter'>0</div>
                </div>
                <div class='bx-pill bp-solo-board-gold-count'>
                    <div class='gb-gold'></div>
                    <div class='bx-pill-counter'>0</div>
                </div>

                <div class='gb-solo-board-card-help'>
                    <div class='gb-component-help'></div>
                </div>
            </div>
            <div class='gb-player-enemy-container'></div>
        </div>
    </div>

    <div id='gb-discarded-help'>{DISCARDED_CARDS}</div>
    <details class='gb-detail-list'>
        <summary>{LIST_INITIAL_BLUE_CARDS}</summary>
        <div id='gb-detail-list-initial-blue'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_INITIAL_RED_CARDS}</summary>
        <div id='gb-detail-list-initial-red'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_DECK_BLUE_CARDS}</summary>
        <div id='gb-detail-list-deck-blue'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_DECK_RED_CARDS}</summary>
        <div id='gb-detail-list-deck-red'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_MAGIC}</summary>
        <div id='gb-detail-list-magic'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_ENEMY}</summary>
        <div id='gb-detail-list-enemy'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_VILLAGE}</summary>
        <div id='gb-detail-list-village'></div>
    </details>
    <details class='gb-detail-list'>
        <summary>{LIST_DICE_FACE}</summary>
        <div id='gb-detail-list-dice-face'></div>
    </details>
</div>

<div id='gb-shortcut-area'></div>
<div id='gb-element-creation' class='bx-hidden'></div>

<audio id="audiosrc_goldblivion_victory" src="{GAMETHEMEURL}img/sound/goldblivion_victory.mp3" preload="none" autobuffer></audio>
<audio id="audiosrc_o_goldblivion_victory" src="{GAMETHEMEURL}img/sound/goldblivion_victory.ogg" preload="none" autobuffer></audio>
<audio id="audiosrc_goldblivion_defeat" src="{GAMETHEMEURL}img/sound/goldblivion_defeat.mp3" preload="none" autobuffer></audio>
<audio id="audiosrc_o_goldblivion_defeat" src="{GAMETHEMEURL}img/sound/goldblivion_defeat.ogg" preload="none" autobuffer></audio>

{OVERALL_GAME_FOOTER}