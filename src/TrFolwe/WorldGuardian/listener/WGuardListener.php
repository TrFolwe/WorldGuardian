<?php

namespace TrFolwe\WorldGuardian\listener;

use pocketmine\block\Chest;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use TrFolwe\WorldGuardian\forms\WGuardLastPosForm;
use TrFolwe\WorldGuardian\manager\WorldManager;
use TrFolwe\WorldGuardian\WGuardian;

class WGuardListener implements Listener {

    /*** @var Config $yamlDatabase */
    private Config $yamlDatabase, $config;

    public function __construct() {
        $this->yamlDatabase = WGuardian::getInstance()->getYamlDatabase();
        $this->config = WGuardian::getInstance()->getConfig();
    }

    public function onBlockBreak(BlockBreakEvent $event) :void {
        $player = $event->getPlayer();
        $blockPosition = $event->getBlock()->getPosition();
        $worldName = $player->getWorld()->getFolderName();

        $worldQuee = WGuardian::getInstance()->worldAreaQuee;

        if(isset($worldQuee[$player->getName()])) {
            if(empty($worldQuee[$player->getName()]["firstPos"])) {
                WGuardian::getInstance()->worldAreaQuee[$player->getName()]["firstPos"] = ["x" => $blockPosition->getFloorX(), "y" => $blockPosition->getFloorY(), "z" => $blockPosition->getFloorZ()];
                $player->sendMessage("§a> Selected first pos (".implode(":", WGuardian::getInstance()->worldAreaQuee[$player->getName()]["firstPos"])."), select now last pos");
            }else if(empty($worldQuee[$player->getName()]["lastPos"])) {
                WGuardian::getInstance()->worldAreaQuee[$player->getName()]["lastPos"] = ["x" => $blockPosition->getFloorX(), "y" => $blockPosition->getFloorY(), "z" => $blockPosition->getFloorZ()];
                $player->sendForm(new WGuardLastPosForm());
            }
            $event->cancel();
            return;
        }

        if(!Server::getInstance()->isOp($player->getName()) && $areaName = WorldManager::inAreaPos($blockPosition->getFloorX(), $blockPosition->getFloorY(), $blockPosition->getFloorZ(), $worldName)) {
            $areaPermissions = WorldManager::getAreaPermission($areaName);
            if(!$areaPermissions["block_break"]) {
                $player->sendMessage("§c> This area is locked");
                $event->cancel();
            }
        }

        if(isset($this->yamlDatabase->get("lockedWorlds")[$worldName])) {
            if(!Server::getInstance()->isOp($player->getName()) && !$this->yamlDatabase->get("lockedWorlds")[$worldName]["block_break"]) {
                $lockedWorldSettings = $this->config->get("locked-world-settings");
                if($lockedWorldSettings["active"]) $player->sendMessage($lockedWorldSettings["message"]);
                $event->cancel();
            }
            return;
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onBlockPlace(BlockPlaceEvent $event) :void {
        $player = $event->getPlayer();
        $blockPosition = $event->getBlockAgainst()->getPosition();
        $worldName = $player->getWorld()->getFolderName();

        if(!Server::getInstance()->isOp($player->getName()) && $areaName = WorldManager::inAreaPos($blockPosition->getFloorX(), $blockPosition->getFloorZ(), $blockPosition->getFloorZ(), $worldName)) {
            $areaPermissions = WorldManager::getAreaPermission($areaName);
            if(!$areaPermissions["block_place"]) {
                $player->sendMessage("§c> This area is locked");
                $event->cancel();
            }
        }

        if(isset($this->yamlDatabase->get("lockedWorlds")[$worldName])) {
            if(!Server::getInstance()->isOp($player->getName()) && !$this->yamlDatabase->get("lockedWorlds")[$worldName]["block_place"]) {
                $lockedWorldSettings = $this->config->get("locked-world-settings");
                if($lockedWorldSettings["active"]) $player->sendMessage($lockedWorldSettings["message"]);
                $event->cancel();
            }
            return;
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     */
    public function onInteract(PlayerInteractEvent $event) :void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $blockPosition = $block->getPosition();
        $worldName = $player->getWorld()->getFolderName();

        if(!Server::getInstance()->isOp($player->getName()) && $areaName = WorldManager::inAreaPos($blockPosition->getFloorX(), $blockPosition->getFloorY(), $blockPosition->getFloorZ(), $worldName)) {
            $areaPermissions = WorldManager::getAreaPermission($areaName);
            if(!$areaPermissions["chest_open"]) {
                if($block instanceof Chest) $player->sendMessage("§c> This area is locked");
                $event->cancel();
            }
        }

        if(isset($this->yamlDatabase->get("lockedWorlds")[$worldName])) {
            if(!Server::getInstance()->isOp($player->getName()) && !$this->yamlDatabase->get("lockedWorlds")[$worldName]["chest_open"]) {
                $lockedWorldSettings = $this->config->get("locked-world-settings");
                if($lockedWorldSettings["active"] && $block instanceof Chest) $player->sendMessage($lockedWorldSettings["message"]);
                $event->cancel();
            }
            return;
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     * @return void
     */
    public function onDropItem(PlayerDropItemEvent $event) :void {
        $player = $event->getPlayer();
        $playerPosition = $player->getPosition();
        $worldName = $player->getWorld()->getFolderName();

        if(!Server::getInstance()->isOp($player->getName()) && $areaName = WorldManager::inAreaPos($playerPosition->getFloorX(), $playerPosition->getFloorY(), $playerPosition->getFloorZ(), $worldName)) {
            $areaPermissions = WorldManager::getAreaPermission($areaName);
            if(!$areaPermissions["drop_item"]) {
                $player->sendMessage("§c> This area is locked");
                $event->cancel();
            }
        }

        if(isset($this->yamlDatabase->get("lockedWorlds")[$worldName])) {
            if(!Server::getInstance()->isOp($player->getName()) && !$this->yamlDatabase->get("lockedWorlds")[$worldName]["drop_item"]) {
                $lockedWorldSettings = $this->config->get("locked-world-settings");
                if($lockedWorldSettings["active"]) $player->sendMessage($lockedWorldSettings["message"]);
                $event->cancel();
            }
            return;
        }
    }

    /**
     * @param EntityItemPickupEvent $event
     * @return void
     */
    public function onPickupItem(EntityItemPickupEvent $event) :void {
        $player = $event->getEntity();
        if(!$player instanceof Player) return;
        $playerPosition = $player->getPosition();
        $worldName = $player->getWorld()->getFolderName();

        if(!Server::getInstance()->isOp($player->getName()) && $areaName = WorldManager::inAreaPos($playerPosition->getFloorX(), $playerPosition->getFloorY(), $playerPosition->getFloorZ(), $worldName)) {
            $areaPermissions = WorldManager::getAreaPermission($areaName);
            if(!$areaPermissions["pick_item"]) {
                $player->sendTip("§c> This area is locked");
                $event->cancel();
            }
        }

        if(isset($this->yamlDatabase->get("lockedWorlds")[$worldName])) {
            if(!Server::getInstance()->isOp($player->getName()) && !$this->yamlDatabase->get("lockedWorlds")[$worldName]["pick_item"]) {
                $lockedWorldSettings = $this->config->get("locked-world-settings");
                if($lockedWorldSettings["active"]) $player->sendTip($lockedWorldSettings["message"]);
                $event->cancel();
            }
            return;
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     */
    public function onEntityDamage(EntityDamageByEntityEvent $event) :void {
        $damager = $event->getDamager();
        $player = $event->getEntity();
        if(!$player instanceof Player || !$damager instanceof Player) return;
        $playerPosition = $player->getPosition();
        $damagerPosition = $damager->getPosition();
        $worldName = $player->getWorld()->getFolderName();

        if(!Server::getInstance()->isOp($damager->getName()) && (($areaName = WorldManager::inAreaPos($playerPosition->getFloorX(), $playerPosition->getFloorY(), $playerPosition->getFloorZ(), $worldName)) || WorldManager::inAreaPos($damagerPosition->getFloorX(), $damagerPosition->getFloorY(), $damagerPosition->getFloorZ(), $worldName))) {
            $areaPermissions = WorldManager::getAreaPermission($areaName);
            if(!$areaPermissions["player_pvp"]) {
                $damager->sendTip("§c> This area is locked");
                $event->cancel();
            }
        }

        if(isset($this->yamlDatabase->get("lockedWorlds")[$worldName])) {
            if(!Server::getInstance()->isOp($damager->getName()) && !$this->yamlDatabase->get("lockedWorlds")[$worldName]["player_pvp"]) {
                $lockedWorldSettings = $this->config->get("locked-world-settings");
                if($lockedWorldSettings["active"]) $damager->sendTip($lockedWorldSettings["message"]);
                $event->cancel();
            }
            return;
        }
    }
}