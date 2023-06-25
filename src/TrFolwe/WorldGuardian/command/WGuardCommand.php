<?php

namespace TrFolwe\WorldGuardian\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use TrFolwe\WorldGuardian\forms\WGuardMainForm;
use TrFolwe\WorldGuardian\WGuardian;

class WGuardCommand extends Command implements PluginOwned
{

    /*** @var WGuardian $instance */
    private WGuardian $instance;
    public function __construct(WGuardian $instance)
    {
        parent::__construct(
            "worldguardian",
            "WorldGuard command",
            "/worldguardian",
            ["wg"]
        );
        $this->setPermission("worldguardian.perm");
        $this->instance = $instance;
    }

    /*** @return Plugin */
    public function getOwningPlugin(): Plugin
    {
        return $this->instance;
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