<?php

namespace blugin\Parkour\Event;

use blugin\Parkour\Parkour;
use blugin\Parkour\UI\UIPage;
use blugin\Parkour\Utils\Color;
use blugin\Parkour\Utils\LocationMath;
use blugin\Parkour\Utils\Text;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;

class EventListener implements Listener {
    /**
     * Player data maker
     *
     * @brief Make player data when player join
     *
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        if(is_null(Parkour::getData($player))) {
            Parkour::setData($player, []);
        } elseif(isset(Parkour::getData($player)["parkour"])) {
            $playerData = Parkour::getData($player);
            $player->setGamemode($playerData["gameMode"]);
            $player->teleport($playerData["location"]);
            Parkour::delData($player);
            Parkour::delPlay($player);
        }
    }

    /**
     * player data delete
     *
     * @brief when LogOut, teleport and delete
     *
     * @param PlayerQuitEvent $event
     */
    public function onLogOut(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        if(isset(Parkour::getData($player)["parkour"])) {
            $playerData = Parkour::getData($player);
            $player->setGamemode($playerData["gameMode"]);
            $player->teleport($playerData["location"]);
            Parkour::delData($player);
            Parkour::delPlay($player);
        }
    }

    /**
     * no hunger!
     *
     * @brief no hunger when play parkour.
     *
     * @param PlayerExhaustEvent $event
     */
    public function onHunger(PlayerExhaustEvent $event) {
        if (isset(Parkour::$plays[strtolower($event->getPlayer()->getName())])) {
            $event->setCancelled(true);
        }
    }

    /**
     * no hit and no death.
     *
     * @brief cannot hit and death when play parkour.
     *
     * @param EntityDamageEvent $event
     */
    public function Hit(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        if($entity instanceof Player) {
            if($event->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
                if($event instanceof EntityDamageByEntityEvent) {
                    if ($event->getDamager() instanceof Player) {
                        if (isset(Parkour::$plays[strtolower($entity->getName())])) {
                            $event->setCancelled(true);
                        }
                    }
                }
            } else {
                if (isset(Parkour::$plays[strtolower($entity->getName())])) {
                    if($event->getFinalDamage() >= $entity->getHealth()) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
    }


//    Author = World
//    Parkour Task's teleportation is block, too.
//    I can solve this, but so many disadvantage follow, I think about this.
//
//    /**
//     * no teleport!
//     *
//     * @brief cannot teleport when play parkour.
//     *
//     * @param EntityTeleportEvent $event
//     */
//    public function onTeleport(EntityTeleportEvent $event) {
//        $entity = $event->getEntity();
//        if($entity instanceof Player) {
//            if (isset(Parkour::$plays[strtolower($entity->getName())])) {
//                $entity->sendMessage(new Text("noWarp", Color::$warning, Text::EXPLAIN));
//                $event->setCancelled(true);
//            }
//        }
//    }

    /**
     * addParkour set position
     *
     * @brief Break Block to set position for add parkour.
     *
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if($event->getBlock()->getId() != 0) {
            if(isset(Parkour::getData($player)["addParkour"]["select"])) {
                if(Parkour::getData($player)["addParkour"]["select"] != 0) {
                    $event->setCancelled(true);
                    $block = $event->getBlock();
                    $playerData = Parkour::getData($player);
                    $toModify = $playerData["addParkour"]["select"];
                    $x = $block->getFloorX();
                    $y = $block->getFloorY() + 1;
                    $z = $block->getFloorZ();
                    if($toModify != "checkPoint") {
                        if($toModify == "floor") {
                            $playerData["addParkour"][(string) $toModify]["y"] = $y;
                        } else {
                            $playerData["addParkour"][(string) $toModify]["x"] = $x;
                            $playerData["addParkour"][(string) $toModify]["y"] = $y;
                            $playerData["addParkour"][(string) $toModify]["z"] = $z;
                        }
                    } else {
                        $i = $playerData["addParkour"]["checkPoint"]["select"];
                        $playerData["addParkour"][(string) $toModify][$i] = [];
                        $playerData["addParkour"][(string) $toModify][$i]["x"] = $x;
                        $playerData["addParkour"][(string) $toModify][$i]["y"] = $y;
                        $playerData["addParkour"][(string) $toModify][$i]["z"] = $z;
                        $playerData["addParkour"]["checkPoint"]["select"] += 1;
                    }
                    $playerData["addParkour"]["select"] = 0;
                    Parkour::setData($player, $playerData);
                    $UI = UIPage::getPageByName("admin", "AddParkour");
                    switch($toModify) {
                        case "start":
                            if(Parkour::$checkPos) {
                                $UI->sendTo($player, 3);
                            } else {
                                $UI->sendTo($player, 4);
                            }
                            break;

                        case "checkPoint":
                            if(Parkour::$checkPos) {
                                $UI->sendTo($player, 5);
                            } else {
                                $UI->sendTo($player, 6);
                            }
                            break;

                        case "end":
                            if(Parkour::$checkPos) {
                                $UI->sendTo($player, 8);
                            } else {
                                $UI->sendTo($player, 9);
                            }
                            break;

                        case "floor":
                            if(Parkour::$checkPos) {
                                $UI->sendTo($player, 10);
                            } else {
                                $UI->sendTo($player, 11);
                            }
                            break;

                        default:
                            Parkour::delData($player, "addParkour");
                            $player->sendMessage(new Text("unknownError", Color::$error, Text::EXPLAIN));
                            break;
                    }
                }
            }
        }
    }

    /**
     * UI response packet handler.
     *
     * @brief When receiving the packet,
     *  verify that it is a UI response packet.
     *  If valid, send the player and data to the handler.
     */
    public function onReceivePacket(DataPacketReceiveEvent $event) {
        $pk = $event->getPacket();
        if($pk instanceof ModalFormResponsePacket) {
            $player = $event->getPlayer();
            $data = json_decode($pk->formData, true);
            if (!is_null($data) && ($player instanceof Player)) {
                $id = $pk->formId;
                if(UIPage::getPageByFormId($id)) {
                    UIPage::getPageByFormId($id)->handle($player, $data, $id);
                }
            }
        }
    }
}