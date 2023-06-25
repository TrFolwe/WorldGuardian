<?php

namespace TrFolwe\WorldGuardian\manager;

use pocketmine\Server;
use TrFolwe\WorldGuardian\WGuardian;

final class WorldManager {

    /*** @var string[] $worldRegionPermissions */
    public static array $worldRegionPermissions = [
        "block_break" => false,
        "block_place" => false,
        "chest_open" => false,
        "drop_item" => false,
        "pick_item" => false,
        "player_pvp" => false
    ];

    /*** @return string[] */
    public static function getAllWorlds() :array {
        $worlds = [];

        foreach(new \RecursiveDirectoryIterator(Server::getInstance()->getDataPath()."worlds/", \FilesystemIterator::SKIP_DOTS) as $file) {
            if($file->isDir()) $worlds[] = $file->getFilename();
        }

        return $worlds;
    }

    /*** @return string[] */
    public static function getLockedWorlds() :array {
        $worlds = [];
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();

        foreach(new \RecursiveDirectoryIterator(Server::getInstance()->getDataPath()."worlds/", \FilesystemIterator::SKIP_DOTS) as $file) {
            if($file->isDir() && isset($yamlDatabase->get("lockedWorlds")[$file->getFilename()])) $worlds[] = $file->getFilename();
        }

        return $worlds;
    }

    /*** @throws \JsonException */
    public static function lockWorld(string $worldName) :void {
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();

        $lockedWorlds = $yamlDatabase->get("lockedWorlds");
        $lockedWorlds[$worldName] = self::$worldRegionPermissions;
        $yamlDatabase->set("lockedWorlds", $lockedWorlds);
        $yamlDatabase->save();
    }

    /*** @throws \JsonException */
    public static function unlockWorld(string $worldName) :void {
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();

        $lockedWorlds = $yamlDatabase->get("lockedWorlds");
        if(isset($lockedWorlds[$worldName])) unset($lockedWorlds[$worldName]);
        $yamlDatabase->set("lockedWorlds", $lockedWorlds);
        $yamlDatabase->save();
    }

    /*** @throws \JsonException */
    public static function setWorldPermission(string $worldName, array $newPermissions) :void {
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();

        foreach ($newPermissions as $permissionName => $permissionValue) {
            $yamlDatabase->setNested("lockedWorlds.".$worldName.".".$permissionName, $permissionValue);
            $yamlDatabase->save();
        }
    }

    /**
     * @param string $worldName
     * @return array
     */
    public static function getWorldPermission(string $worldName) :array {
        return WGuardian::getInstance()->getYamlDatabase()->get("lockedWorlds")[$worldName];
    }

    /**
     * @param string $areaName
     * @param bool $includeY
     * @param array $pos
     * @return void
     * @throws \JsonException
     */
    public static function addAreaPos(string $areaName, bool $includeY, array $pos) :void {
        extract($pos);
        $minX = min($firstX, $lastX);
        if($includeY) $minY = min($firstY, $lastY);
        $minZ = min($firstZ, $lastZ);
        $maxX = max($firstX, $lastX);
        if($includeY) $maxY = max($firstY, $lastY);
        $maxZ = max($firstZ, $lastZ);

        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();
        $areaPos = $yamlDatabase->get("areaPos");

        $areaPos[$areaName] = [
            "pos" => !$includeY ? ($minX.":".$maxX.":".$minZ.":".$maxZ) : ($minX.":".$maxX.":".$minY.":".$maxY.":".$minZ.":".$maxZ),
            "world" => $world,
            "permissions" => self::$worldRegionPermissions,
        ];
        if($includeY) $areaPos[$areaName]["includeY"] = true;
        $yamlDatabase->set("areaPos", $areaPos);
        $yamlDatabase->save();
    }

    /**
     * @param string $areaName
     * @param array $newPermission
     * @return void
     * @throws \JsonException
     */
    public static function setAreaPermission(string $areaName, array $newPermission) :void
    {
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();

        foreach ($newPermission as $permissionName => $permissionValue) {
            $yamlDatabase->setNested("areaPos." . $areaName . ".permissions." . $permissionName, $permissionValue);
            $yamlDatabase->save();
        }
    }

    /**
     * @param string $areaName
     * @return array
     */
    public static function getAreaPermission(string $areaName) :array {
        return WGuardian::getInstance()->getYamlDatabase()->get("areaPos")[$areaName]["permissions"];
    }

    /**
     * @param string $areaName
     * @return void
     * @throws \JsonException
     */
    public static function deleteArea(string $areaName) :void {
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();

        $areaPos = $yamlDatabase->get("areaPos");
        unset($areaPos[$areaName]);
        $yamlDatabase->set("areaPos", $areaPos);
        $yamlDatabase->save();
    }

    /**
     * @param int $x
     * @param $y
     * @param int $z
     * @param string $world
     * @return string|null
     */
    public static function inAreaPos(int $x, $y, int $z, string $world) :?string {
        $yamlDatabase = WGuardian::getInstance()->getYamlDatabase();
        foreach($yamlDatabase->get("areaPos") as $areaName => $areaData) {
            $posExplode = array_map(fn($c) => $c * 1, explode(":", $areaData["pos"]));
            if(isset($areaData["includeY"])) $posCondition = in_array($x, range($posExplode[0], $posExplode[1])) && in_array($y, range($posExplode[2], $posExplode[3])) && in_array($z, range($posExplode[4], $posExplode[5]));
            else $posCondition = in_array($x, range($posExplode[0], $posExplode[1])) && in_array($z, range($posExplode[2], $posExplode[3]));
            if($posCondition && $world === $areaData["world"]) return $areaName;
        }
        return null;
    }

    /*** @return string[] */
    public static function getAllArea() :array {
        return array_keys(WGuardian::getInstance()->getYamlDatabase()->get("areaPos"));
    }
}