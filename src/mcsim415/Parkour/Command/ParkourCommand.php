<?php

namespace mcsim415\Parkour\Command;

use mcsim415\Parkour\Parkour;
use mcsim415\Parkour\UI\UIPage;
use mcsim415\Parkour\Utils\Color;
use mcsim415\Parkour\Utils\Text;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class ParkourCommand implements CommandExecutor {
    public Parkour $loader;

    function __construct(Parkour $plugin){
        global $loader;
        $loader = $plugin;
    }

    function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if($command->getName() == "parkour"){
            if(!$sender instanceof Player) {
                $sender->sendMessage(new Text("pleaseInGame", Text::getTranslatedString("warning"), Text::EXPLAIN));
            } else {
                if (isset(Parkour::$plays[strtolower($sender->getName())])){
                    $player = $sender;
                    $playerData = Parkour::getData($player);
                    $player->setGamemode($playerData["gameMode"]);
                    $player->teleport($playerData["location"]);
                    Parkour::delData($player);
                    Parkour::delPlay($player);
                    $player->sendMessage(new Text("giveUp", Color::$explain, Text::EXPLAIN));
                } else {
                    if($sender->isOp()){
                        UIPage::getPageByName("admin", "SelWork")->sendTo($sender);
                    } else {
                        UIPage::getPageByName("all", "Play")->sendTo($sender);
                    }
                }
            }
            return true;
        }
    }
}