<?php

namespace TrFolwe\WorldGuardian\forms;

use dktapps\pmforms\element\Toggle;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use TrFolwe\WorldGuardian\manager\WorldManager;
use TrFolwe\WorldGuardian\WGuardian;

class WGuardMainForm extends MenuForm
{

    private array $optionsArr = [
        "lock_world" => "Lock the world",
        "world_permission" => "World lock permissions",
        "unlock_world" => "Unlock the world",
        "add_area" => "Add new area",
        "edit_area" => "Edit area permissions",
        "delete_area" => "Delete area"
    ];

    public function __construct()
    {
        $closureArr = [
            "lock_world" => [
                "formOptions" => ["title" => "Lock the world", "content" => "Select the world to lock"],
                "menuButtons" => WorldManager::getAllWorlds(),
                "submitClosure" => function (Player $formPlayer, string $selectedWorld): void {
                    WorldManager::lockWorld($selectedWorld);
                    $formPlayer->sendMessage("§a> Locked `" . $selectedWorld . "` is world");
                }],
            "world_permission" => [
                "formOptions" => ["title" => "Select the world", "content" => "Select the world whose permissions you want to set"],
                "menuButtons" => WorldManager::getLockedWorlds(),
                "submitClosure" => function (Player $formPlayer, string $selectedWorld): void {
                    $formPlayer->sendForm(new WGuardPermissionForm("Set world permission", array_map(fn($c) => new Toggle($c, ucfirst(str_replace("_", " ", $c)), WorldManager::getWorldPermission($selectedWorld)[$c]), array_keys(WorldManager::$worldRegionPermissions)), function (Player $player, array $responseAll) use($selectedWorld) :void {
                        WorldManager::setWorldPermission($selectedWorld, $responseAll);
                        $player->sendMessage("§a> Edited ".$selectedWorld." permissions");
                    }));
                }],
            "unlock_world" => [
                "formOptions" => ["title" => "Unlock the world", "content" => "Select the world to Unlock"],
                "menuButtons" => WorldManager::getLockedWorlds(),
                "submitClosure" => function (Player $formPlayer, string $selectedWorld): void {
                    WorldManager::unlockWorld($selectedWorld);
                    $formPlayer->sendMessage("§a> Unlocked `" . $selectedWorld . "` is world");
                }],
            "edit_area" => [
                "formOptions" => ["title" => "Edit area", "content" => "Select the area to edit"],
                "menuButtons" => WorldManager::getAllArea(),
                "submitClosure" => function (Player $formPlayer, string $selectedArea): void {
                    $formPlayer->sendForm(new WGuardPermissionForm("Set area permission", array_map(fn($c) => new Toggle($c, ucfirst(str_replace("_", " ", $c)), WorldManager::getAreaPermission($selectedArea)[$c]), array_keys(WorldManager::$worldRegionPermissions)), function (Player $player, array $responseAll) use($selectedArea) :void {
                        WorldManager::setAreaPermission($selectedArea, $responseAll);
                        $player->sendMessage("§a> Edited ".$selectedArea." permissions");
                    }));
                }],
            "delete_area" => [
                "formOptions" => ["title" => "Delete area", "content" => "Select the are to delete"],
                "menuButtons" => WorldManager::getAllArea(),
                "submitClosure" => function (Player $formPlayer, string $selectedArea): void {
                    WorldManager::deleteArea($selectedArea);
                    $formPlayer->sendMessage("§a> Deleted ".$selectedArea." is area");
                }]
        ];
        parent::__construct(
            "World Guardian",
            "",
            array_map(fn($c) => new MenuOption($c), $this->optionsArr),
            function (Player $player, int $selected) use ($closureArr): void {
                $buttonKeyText = array_search($this->getOption($selected)->getText(), $this->optionsArr);
                if ($buttonKeyText === "add_area") {
                    if (isset(WGuardian::getInstance()->worldAreaQuee[$player->getName()])) {
                        $player->sendMessage("§c> Finish the post");
                        return;
                    }
                    WGuardian::getInstance()->worldAreaQuee[$player->getName()] = ["firstPos" => [], "lastPos" => []];
                    $player->sendMessage("§a> Break first pos");
                    return;
                }
                $player->sendForm(new WGuardClosureForm(
                    $closureArr[$buttonKeyText]["formOptions"],
                    $closureArr[$buttonKeyText]["menuButtons"],
                    $closureArr[$buttonKeyText]["submitClosure"]
                ));
            });
    }
}