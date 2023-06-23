<?php

namespace TrFolwe\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use TrFolwe\forms\WGuardMainForm;

class WGuardCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            "worldguard",
            "WorldGuard command",
            "",
            ["wg"]
        );
        $this->setPermission("wguard.permission");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$sender->hasPermission($this->getPermissions()[0])) {
            $sender->sendMessage("§c> You are not permission to use this command!");
            return;
        }
        if (!$sender instanceof Player) {
            $sender->sendMessage("§c> You can only use the command in-game");
            return;
        }
        $sender->sendForm(new WGuardMainForm());
    }
}