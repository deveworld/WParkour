<?php
namespace blugin\Parkour\UI\all;

use blugin\Parkour\Parkour;
use blugin\Parkour\Utils\Color;
use blugin\Parkour\Utils\Text;
use pocketmine\level\Location;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use blugin\Parkour\UI\UIPage;

class Play extends UIPage {
    public const FORM_ID = 18321003;

    public function handle(Player $player, $response, $id) : void {
        $parkour = Parkour::getParkour();
        if(!empty($parkour) && isset($parkour[$response])) {
            $time = false;
            if(isset(Parkour::$db["save"][strtolower($player->getName())][$response])) {
                if(Parkour::$db["save"][strtolower($player->getName())][$response] < time()) {
                    $time = true;
                }
            } else {
                $time = true;
            }
            if(!$time) {
                $format = "s";
                $remain = Parkour::$db["save"][strtolower($player->getName())][$response] - time();
                if(round($remain/3600) != 0) {
                    $format = "h";
                    $time = round($remain/3600);
                } elseif(round($remain/60) != 0) {
                    $format = "i";
                    $time = round($remain/60);
                } elseif(round($remain) != 0) {
                    $format = "s";
                    $time = round($remain);
                }
                $player->sendMessage(
                    new Text(
                        "timeLater",
                        Color::$warning,
                        Text::EXPLAIN,
                        "",
                        "{time}",
                        (
                            new Text(
                                "time.".$format,
                                search: "{".$format."}",
                                change: $time
                            )
                        )
                    )
                );
            } else {
                $levelName = $parkour[$response]["world"];
                if(!Parkour::getInstance()->getServer()->isLevelLoaded($levelName)){
                    if(!Parkour::getInstance()->getServer()->loadLevel($levelName)){
                        $player->sendMessage(new Text("noWorld", Color::$error, Text::EXPLAIN));
                        return;
                    }
                }
                $playerData = Parkour::getData($player);
                $playerData["parkour"] = $response;
                $playerData["location"] = $player->getLocation();
                $playerData["checkPoint"] = 0;
                $playerData["noCheckPoint"] = false;
                $playerData["gameMode"] = $player->getGamemode();
                Parkour::setData($player, $playerData);
                $level = Parkour::getInstance()->getServer()->getLevelByName($parkour[$response]["world"]);
                $start = new Location(
                    $parkour[$response]["start"]["x"] + 0.5,
                    $parkour[$response]["start"]["y"],
                    $parkour[$response]["start"]["z"] + 0.5,
                    $player->getYaw(),
                    $player->getPitch(),
                    $level
                );
                Parkour::addPlay($player);
                $player->setGamemode(2);
                $player->teleport($start);
                $player->sendMessage(new Text("entrance", Color::$explain, Text::EXPLAIN, "", "{name}", $parkour[$response]["name"]));
                $player->sendMessage(new Text("out", Color::$explain, Text::EXPLAIN));
            }
        }
    }

    public function sendTo(Player $player, $id = self::FORM_ID) : void {
        $uiData = [];
        $uiData["type"] = "form";
        $uiData = $this->setTitle($uiData, new Text("name", Color::$explain, Text::EXPLAIN));
        $parkour = Parkour::getParkour();
        $uiData["buttons"] = [];
        if(empty($parkour)) {
            $uiData = $this->addContent($uiData, new Text("empty", Color::$explain, Text::EXPLAIN));
            $uiData = $this->addButton($uiData, new Text("close", Color::$warning, Text::BUTTON));
        } else {
            $uiData = $this->addContent($uiData, new Text("selectParkourPlay", Color::$explain, Text::EXPLAIN));
            foreach($parkour as $key => $value) {
                $name = $value["name"];
                $uiData = $this->addButton($uiData, new Text($name, Color::$button, Text::BUTTON, translate:false));
            }
        }
        $ui = new ModalFormRequestPacket();
        $ui->formId = self::FORM_ID;
        $ui->formData = json_encode($uiData);
        $player->dataPacket($ui);
    }

    public function getFolderName() : string {
        return "all";
    }

    public function getName() : string {
        return "Play";
    }
}