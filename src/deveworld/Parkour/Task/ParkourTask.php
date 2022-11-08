<?php

namespace deveworld\Parkour\Task;

use deveworld\Parkour\Parkour;
use deveworld\Parkour\Utils\Color;
use deveworld\Parkour\Utils\LocationMath;
use deveworld\Parkour\Utils\Text;
use pocketmine\scheduler\Task;

class ParkourTask extends Task {

    private Parkour $plugin;

    public function __construct(Parkour $plugin){
        $this->plugin = $plugin;
    }

    public function onRun() : void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            if(isset(Parkour::$plays[strtolower($player->getName())])) {
                $playerData = Parkour::getData($player);
                $parkour = Parkour::getParkour()[$playerData["parkour"]];
                $location = $player->getLocation();

                // if parkour world and player world isn't same
                if ($parkour['world'] != $player->getWorld()->getFolderName()) {
                    LocationMath::goToLastCheckpoint($player, true);
                    $player->sendMessage((string) new Text("noWarp", Color::$warning, Text::EXPLAIN));
                }

                // If remain more checkpoint
                if(!$playerData["noCheckPoint"]) {
                    if(LocationMath::equal($location, $parkour["checkPoint"][$playerData["checkPoint"]])) {
                        $playerData["checkPoint"] += 1;
                        if(!isset($parkour["checkPoint"][$playerData["checkPoint"]])) {
                            $playerData["noCheckPoint"] = true;
                        }
                        Parkour::setData($player, $playerData);
                        $player->sendMessage((string) new Text("reachCheckPoint", Color::$explain, Text::EXPLAIN, "", "{n}", (string) $playerData["checkPoint"]));
                    }
                }

                // Reach floor
                if($location->getFloorY() == $parkour["floor"]["y"]) {
                    LocationMath::goToLastCheckpoint($player);
                }

                // Reach END
                if(LocationMath::equal($location, $parkour["end"])) {
                    if(!$playerData["noCheckPoint"]) {
                        $player->sendMessage((string) new Text("reachAll", Color::$warning, Text::EXPLAIN));
                        continue;
                    }
                    $parkourName = $parkour["name"];
                    $player->sendMessage((string) new Text("clear", Color::$explain, Text::EXPLAIN, "", "{name}", $parkourName));
                    $player->setGamemode($playerData["gameMode"]);
                    $player->teleport($playerData["location"]);
                    Parkour::$db["save"][strtolower($player->getName())][$playerData["parkour"]] = mktime(
                        (
                            (int) date("H") + $parkour["time"]
                        ),
                        date("i"),
                        date("s"),
                        date("m"),
                        date("d"),
                        date("Y")
                    );
                    Parkour::delData($player);
                    Parkour::delPlay($player);
                    if(class_exists(" onebone\\economyapi\\EconomyAPI")) {
                        \onebone\economyapi\EconomyAPI::getInstance()->addMoney($player, $parkour['money']);
                    }
                }
            }
        }
    }

}