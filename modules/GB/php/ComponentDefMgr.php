<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldblivion implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace GB;

require_once('ComponentDef.php');
require_once('ComponentDefMgrCardBlue.php');
require_once('ComponentDefMgrCardRed.php');
require_once('ComponentDefMgrVillage.php');
require_once('ComponentDefMgrMagic.php');
require_once('ComponentDefMgrEnemy.php');

class ComponentDefMgr
{
    public static function getAll()
    {
        self::initComponentDefs();
        return self::$componentDefs;
    }

    public static function getAllCardBlue()
    {
        self::initComponentDefs();
        return array_filter(self::$componentDefs, fn ($cd) => $cd->isCardBlue());
    }

    public static function getAllCardRed()
    {
        self::initComponentDefs();
        return array_filter(self::$componentDefs, fn ($cd) => $cd->isCardRed());
    }

    public static function getAllVillage()
    {
        self::initComponentDefs();
        return array_filter(self::$componentDefs, fn ($cd) => $cd->isVillage());
    }

    public static function getAllMagic()
    {
        self::initComponentDefs();
        return array_filter(self::$componentDefs, fn ($cd) => $cd->isMagic());
    }

    public static function getAllEnemy()
    {
        self::initComponentDefs();
        return array_filter(self::$componentDefs, fn ($cd) => $cd->isEnemy());
    }

    public static function getByTypeId(int $typeId)
    {
        self::initComponentDefs();
        if (!array_key_exists($typeId, self::$componentDefs)) {
            return null;
        }
        return self::$componentDefs[$typeId];
    }

    public static function getIconName(int $icon)
    {
        switch ($icon) {
            case COMPONENT_ICON_ID_BUILDING:
                return clienttranslate('Building');
            case COMPONENT_ICON_ID_HUMAN:
                return clienttranslate('Human');
            case COMPONENT_ICON_ID_ELF:
                return clienttranslate('Elf');
            case COMPONENT_ICON_ID_DWARF:
                return clienttranslate('Dwarf');
            case COMPONENT_ICON_ID_ENEMY:
                return clienttranslate('Enemy');
            default:
                throw new \BgaSystemException("Unknown icon $icon for name");
        }
    }

    use \GB\ComponentDefMgrCardBlue;
    use \GB\ComponentDefMgrCardRed;
    use \GB\ComponentDefMgrVillage;
    use \GB\ComponentDefMgrMagic;
    use \GB\ComponentDefMgrEnemy;

    private static $componentDefs;

    private static function initComponentDefs()
    {
        if (self::$componentDefs != null) {
            return;
        }
        self::$componentDefs = [];
        foreach (self::getComponentDefCardBlue() as $componentDef) {
            self::$componentDefs[$componentDef->typeId] = $componentDef;
        }
        foreach (self::getComponentDefCardRed() as $componentDef) {
            self::$componentDefs[$componentDef->typeId] = $componentDef;
        }
        foreach (self::getComponentDefVillage() as $componentDef) {
            self::$componentDefs[$componentDef->typeId] = $componentDef;
        }
        foreach (self::getComponentDefMagic() as $componentDef) {
            self::$componentDefs[$componentDef->typeId] = $componentDef;
        }
        foreach (self::getComponentDefEnemy() as $componentDef) {
            self::$componentDefs[$componentDef->typeId] = $componentDef;
        }
    }
}
