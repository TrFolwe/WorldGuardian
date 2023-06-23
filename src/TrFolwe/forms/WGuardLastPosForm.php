<?php

namespace TrFolwe\forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use pocketmine\player\Player;
use TrFolwe\manager\WorldManager;
use TrFolwe\WGuardian;

class WGuardLastPosForm extends CustomForm {
    public function __construct() {
        parent::__construct(
            "Setup area",
            [
                new Label("e0","Enter the area name"),
                new Input("e1","","Area name...")
            ],
            function (Player $player, CustomFormResponse $response) :void {
                $areaName = $response->getString("e1");
                if(!trim($areaName)) {
                    $player->sendMessage("§c> Try again by filling in the blanks.");
                    unset(WGuardian::getInstance()->worldAreaQuee[$player->getName()]);
                    return;
                }

                $selectedPos = WGuardian::getInstance()->worldAreaQuee[$player->getName()];

                WorldManager::addAreaPos(
                    $areaName,
                    [
                        "firstX" => $selectedPos["firstPos"]["x"],
                        "firstZ" => $selectedPos["firstPos"]["z"],
                        "lastX" => $selectedPos["lastPos"]["x"],
                        "lastZ" => $selectedPos["lastPos"]["z"],
                        "world" => $player->getWorld()->getFolderName()
                    ]
                );
                $player->sendMessage("§a> Saved `".$areaName."` new area");
                unset(WGuardian::getInstance()->worldAreaQuee[$player->getName()]);
            }, function(Player $player) :void {
                unset(WGuardian::getInstance()->worldAreaQuee[$player->getName()]);
        });
    }
}