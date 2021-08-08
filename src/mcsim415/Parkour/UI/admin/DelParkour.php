<?php

namespace mcsim415\Parkour\UI\admin;

use mcsim415\Parkour\Parkour;
use mcsim415\Parkour\UI\UIPage;
use mcsim415\Parkour\Utils\Color;
use mcsim415\Parkour\Utils\Text;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class DelParkour extends UIPage {
    public const FORM_ID = 18321026;

    public function handle(Player $player, $response, $id) : void{
        $parkour = Parkour::getParkour();
        if(!empty($parkour) && isset($parkour[$response])) {
            if(Parkour::delParkour($response)) {
                $player->sendMessage(new Text("deletedParkour", Color::$explain, Text::EXPLAIN));
            } else {
                $player->sendMessage(new Text("errorDelete", Color::$error, Text::EXPLAIN));
            }
        }
    }

    public function sendTo(Player $player, $id = 0) : void{
        $uiData = [];
        $uiData["type"] = "form";
        $uiData = $this->setTitle($uiData, new Text("name", Color::$explain, Text::EXPLAIN));
        $parkour = Parkour::getParkour();
        $uiData["buttons"] = [];
        if(empty($parkour)) {
            $uiData = $this->addContent($uiData, new Text("empty", Color::$explain, Text::EXPLAIN));
            $uiData = $this->addButton($uiData, new Text("close", Color::$warning, Text::BUTTON));
        } else {
            $uiData = $this->addContent($uiData, new Text("selectParkourDel", Color::$explain, Text::EXPLAIN));
            foreach($parkour as $value) {
                $name = $value["name"];
                $uiData = $this->addButton($uiData, new Text($name, Color::$button, Text::BUTTON,"", "", "", false));
            }
        }
        $ui = new ModalFormRequestPacket();
        $ui->formId = self::FORM_ID;
        $ui->formData = json_encode($uiData);
        $player->dataPacket($ui);
    }

    public function getFolderName() : string{
        return "admin";
    }

    public function getName() : string{
        return "DelParkour";
    }
}