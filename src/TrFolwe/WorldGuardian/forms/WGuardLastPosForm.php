<?php

namespace TrFolwe\WorldGuardian\forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Toggle;
use pocketmine\player\Player;
use TrFolwe\WorldGuardian\manager\WorldManager;
use TrFolwe\WorldGuardian\WGuardian;

class WGuardLastPosForm extends CustomForm {
    public function __construct() {
        parent::__construct(
            "Setup area",
            [
                new Label("e0","Enter the area name"),
                new Toggle("e1", "Include the y coordinate?", false),
                new Input("e2","","Area name...")
            ],
            function (Player $player, CustomFormResponse $response) :void {
                $areaName = $response->getString("e2");
                $includeY = $response->getBool("e1");
                if(!trim($areaName)) {
                    $player->sendMessage("§c> Try again by filling in the blanks.");
                    unset(WGuardian::getInstance()->worldAreaQuee[$player->getName()]);
                    return;
                }

                $selectedPos = WGuardian::getInstance()->worldAreaQuee[$player->getName()];

                $posArr = [
                    "firstX" => $selectedPos["firstPos"]["x"],
                    "firstZ" => $selectedPos["firstPos"]["z"],
                    "lastX" => $selectedPos["lastPos"]["x"],
                    "lastZ" => $selectedPos["lastPos"]["z"],
                    "world" => $player->getWorld()->getFolderName()
                ];

                if($includeY) {
                    $posArr["firstY"] = $selectedPos["firstPos"]["y"];
                    $posArr["lastY"] = $selectedPos["lastPos"]["y"];
                }

                WorldManager::addAreaPos(
                    $areaName,
                    $includeY,
                    $posArr
                );
                $player->sendMessage("§a> Saved `".$areaName."` new area");
                unset(WGuardian::getInstance()->worldAreaQuee[$player->getName()]);
            }, function(Player $player) :void {
                unset(WGuardian::getInstance()->worldAreaQuee[$player->getName()]);
        });
    }
}