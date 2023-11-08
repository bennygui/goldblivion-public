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

require_once('ComponentAbility.php');

const COMPONENT_CATEGORY_ID_CARD_BLUE = 1;
const COMPONENT_CATEGORY_ID_CARD_RED = 2;
const COMPONENT_CATEGORY_ID_VILLAGE = 3;
const COMPONENT_CATEGORY_ID_MAGIC = 4;
const COMPONENT_CATEGORY_ID_ENEMY = 5;

const COMPONENT_ICON_ID_BUILDING = 1;
const COMPONENT_ICON_ID_HUMAN = 2;
const COMPONENT_ICON_ID_ELF = 3;
const COMPONENT_ICON_ID_DWARF = 4;
const COMPONENT_ICON_ID_ENEMY = 5;
const COMPONENT_ICON_IDS = [
    COMPONENT_ICON_ID_BUILDING,
    COMPONENT_ICON_ID_HUMAN,
    COMPONENT_ICON_ID_ELF,
    COMPONENT_ICON_ID_DWARF,
    COMPONENT_ICON_ID_ENEMY,
];

const COMPONENT_SUB_CATEGORY_ID_NOBLE = 1;
const COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE = 2;
const COMPONENT_SUB_CATEGORY_ID_PLAYER_RED = 3;
const COMPONENT_SUB_CATEGORY_ID_PLAYER_GREEN = 4;
const COMPONENT_SUB_CATEGORY_ID_PLAYER_YELLOW = 5;
const COMPONENT_SUB_CATEGORY_ID_DECK = 6;
const COMPONENT_SUB_CATEGORY_ID_LEFT = 7;
const COMPONENT_SUB_CATEGORY_ID_RIGHT = 8;
const COMPONENT_SUB_CATEGORY_ID_FOREST = 9;
const COMPONENT_SUB_CATEGORY_ID_MOUNTAIN = 10;
const COMPONENT_SUB_CATEGORY_ID_PERMANENT = 11;

const COMPONENT_SUB_CATEGORY_IDS_PLAYER_COLORS = [
    COMPONENT_SUB_CATEGORY_ID_PLAYER_BLUE => COLOR_ID_BLUE,
    COMPONENT_SUB_CATEGORY_ID_PLAYER_RED => COLOR_ID_RED,
    COMPONENT_SUB_CATEGORY_ID_PLAYER_GREEN => COLOR_ID_GREEN,
    COMPONENT_SUB_CATEGORY_ID_PLAYER_YELLOW => COLOR_ID_YELLOW,
];

class ComponentDef
{
    public $typeId;
    public $categoryId;
    public $subCategoryId;
    public $setupCount;
    public $name;
    public $desc;
    public $cost;
    public $icons;
    // Multiple abilities = OR between abilities
    public $abilities;

    public function __construct()
    {
        $this->typeId = null;
        $this->categoryId = null;
        $this->subCategoryId = null;
        $this->setupCount = 1;
        $this->name = null;
        $this->desc = null;
        $this->cost = null;
        $this->icons = [];
        $this->abilities = [];
    }

    public function isCardBlue()
    {
        return ($this->categoryId == COMPONENT_CATEGORY_ID_CARD_BLUE);
    }

    public function isCardRed()
    {
        return ($this->categoryId == COMPONENT_CATEGORY_ID_CARD_RED);
    }

    public function isVillage()
    {
        return ($this->categoryId == COMPONENT_CATEGORY_ID_VILLAGE);
    }

    public function isMagic()
    {
        return ($this->categoryId == COMPONENT_CATEGORY_ID_MAGIC);
    }

    public function isEnemy()
    {
        return ($this->categoryId == COMPONENT_CATEGORY_ID_ENEMY);
    }

    public function isBuilding()
    {
        return ($this->isCardBlue()
            && array_search(COMPONENT_ICON_ID_BUILDING, $this->icons) !== false
        );
    }

    public function isHumanoid()
    {
        return ($this->isCardBlue()
            && array_search(COMPONENT_ICON_ID_BUILDING, $this->icons) === false
        );
    }

    public function hasAbilityChoice()
    {
        return (count($this->abilities) > 1);
    }

    public function hasReactivateAbility()
    {
        foreach ($this->abilities as $ab) {
            if ($ab->hasReactivateAbility()) {
                return true;
            }
        }
        return false;
    }

    public function hasDrawRedCardAbility()
    {
        foreach ($this->abilities as $ab) {
            if ($ab->hasDrawRedCardAbility()) {
                return true;
            }
        }
        return false;
    }

    public function hasAbilityCost()
    {
        foreach ($this->abilities as $ab) {
            if ($ab->hasCost()) {
                return true;
            }
        }
        return false;
    }

    public function getCombatPower()
    {
        foreach ($this->abilities as $ab) {
            $power = $ab->getCombatPower();
            if ($power !== null) {
                return $power;
            }
        }
        return null;
    }

    public function isValidAbilityChoice(int $index)
    {
        return ($index >= 0 && $index < count($this->abilities));
    }

    public function hasIcon(int $icon)
    {
        return (array_search($icon, $this->icons) !== false);
    }

    public function countIcon(int $icon)
    {
        return count(array_filter($this->icons, fn ($i) => $i == $icon));
    }

    public function uniqueIcons()
    {
        return array_values(array_unique($this->icons));
    }

    public function canPayComponentCost(int $playerNuggets, int $playerMaterial)
    {
        if ($this->cost === null) {
            return true;
        }
        return $this->cost->canPayCost($playerNuggets, $playerMaterial);
    }

    public function payComponentCost(int &$playerNuggets, int &$playerMaterial, int &$nuggetPay, int &$materialPay)
    {
        if ($this->cost === null) {
            return true;
        }
        return $this->cost->payCost($playerNuggets, $playerMaterial, $nuggetPay, $materialPay);
    }

    public function canPayAnyAbilityCost(int $playerNuggets, int $playerMaterial)
    {
        foreach ($this->abilities as $ab) {
            if ($ab->canPayCost($playerNuggets, $playerMaterial)) {
                return true;
            }
        }
        return false;
    }
}

class ComponentDefBuilder
{
    private $def;

    public function __construct()
    {
        $this->def = new ComponentDef();
    }

    public function build()
    {
        return $this->def;
    }

    public function cardBlue(int $typeId)
    {
        $this->def->categoryId = COMPONENT_CATEGORY_ID_CARD_BLUE;
        $this->def->typeId = $typeId;
        return $this;
    }

    public function cardRed(int $typeId)
    {
        $this->def->categoryId = COMPONENT_CATEGORY_ID_CARD_RED;
        $this->def->typeId = $typeId;
        return $this;
    }

    public function village(int $typeId)
    {
        $this->def->categoryId = COMPONENT_CATEGORY_ID_VILLAGE;
        $this->def->typeId = $typeId;
        return $this;
    }

    public function magic(int $typeId)
    {
        $this->def->categoryId = COMPONENT_CATEGORY_ID_MAGIC;
        $this->def->typeId = $typeId;
        return $this;
    }

    public function enemy(int $typeId)
    {
        $this->def->categoryId = COMPONENT_CATEGORY_ID_ENEMY;
        $this->def->typeId = $typeId;
        return $this;
    }

    public function name(string $name)
    {
        $this->def->name = $name;
        return $this;
    }

    public function desc(string $desc)
    {
        $this->def->desc = $desc;
        return $this;
    }

    public function cost(int $resourceTypeId, int $count, bool $isMandatory = true)
    {
        $this->def->cost = new \GB\ComponentCost($resourceTypeId, $count, $isMandatory);
        return $this;
    }

    public function icon(int $iconId)
    {
        $this->def->icons[] = $iconId;
        return $this;
    }

    public function subCategory(int $subCategoryId)
    {
        $this->def->subCategoryId = $subCategoryId;
        return $this;
    }

    public function setupCount(int $setupCount)
    {
        $this->def->setupCount = $setupCount;
        return $this;
    }

    public function ability(\GB\ComponentAbility $ability)
    {
        $this->def->abilities[] = $ability;
        return $this;
    }
}
