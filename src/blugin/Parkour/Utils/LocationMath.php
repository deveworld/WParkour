<?php

namespace blugin\Parkour\Utils;

use blugin\Parkour\Parkour;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\Player;

class LocationMath {

    /**
     * go to last checkpoint
     *
     * @brief teleport player to last checkpoint. If not reached any checkpoint, go to start point.
     *
     * @param Player $player target player
     * @param bool   $level set location Level with parkour World Level
     */
    public static function goToLastCheckpoint(Player $player, bool $level = false) {
        $player->teleport(self::getLastCheckPoint($player, $level));
    }

    /**
     * @param Player $player
     * @param bool   $level
     *
     * @return Location
     */
    public static function getLastCheckPoint(Player $player, bool $level = false): Location {
        $playerData = Parkour::getData($player);
        $parkour = Parkour::getParkour()[$playerData["parkour"]];
        if($level) {
            $level = Parkour::getInstance()->getServer()->getLevelByName($parkour["world"]);
        } else {
            $level = $player->getLevel();
        }
        if($playerData["checkPoint"] == 0) {
            return self::arrayToLocation($player, $parkour["start"], $level);
        } else {
            return self::arrayToLocation($player, $parkour["checkPoint"][$playerData["checkPoint"] - 1], $level);
        }
    }

    /**
     * @param Player     $player
     * @param array      $arrayLoc
     * @param Level|null $level
     *
     * @return Location
     */
    public static function arrayToLocation(Player $player, array $arrayLoc, Level $level = null): Location {
        if($level == null) {
            $level = $player->getLevel();
        }
        return new Location(
            $arrayLoc["x"]+0.5,
            $arrayLoc["y"],
            $arrayLoc["z"]+0.5,
            $player->getYaw(),
            $player->getPitch(),
            $level
        );
    }

    /**
     * check location is equal
     *
     * @brief check location and location2 is equal
     *
     * @param Location $location location
     * @param array    $location2 array location
     *
     * @return bool
     */
    public static function equal(Location $location, array $location2): bool {
        $location = new Vector3(
            $location->getFloorX(),
            $location->getFloorY(),
            $location->getFloorZ()
        );
        return $location->equals(
            new Vector3(
                (int) $location2["x"],
                (int) $location2["y"],
                (int) $location2["z"]
            )
        );
    }
}