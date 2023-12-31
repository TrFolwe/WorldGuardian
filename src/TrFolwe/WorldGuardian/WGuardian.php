<?php

namespace TrFolwe\WorldGuardian;

use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use TrFolwe\WorldGuardian\command\WGuardCommand;
use TrFolwe\WorldGuardian\listener\WGuardListener;

class WGuardian extends PluginBase
{
    /*** @var WGuardian $instance */
    private static self $instance;

    /*** @var Config $yamlDatabase */
    private Config $yamlDatabase;

    /*** @var array $worldAreaQuee */
    public array $worldAreaQuee = [];

   protected function onLoad(): void
   {
       self::$instance = $this;
       $this->saveDefaultConfig();
       $this->yamlDatabase = new Config($this->getDataFolder()."wguard.yml", Config::YAML, [
           "lockedWorlds" => [],
           "areaPos" => []
       ]);
   }

   protected function onEnable(): void
   {
       $this->getServer()->getCommandMap()->register("worldguardian", new WGuardCommand($this));
       $this->getServer()->getPluginManager()->registerEvents(new WGuardListener(), $this);
   }

    /*** @return self */
   public static function getInstance() :self {
       return self::$instance;
   }

    /*** @return Config */
   public function getYamlDatabase() :Config {
       $this->yamlDatabase->reload();
       return $this->yamlDatabase;
   }
}