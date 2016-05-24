<?php

namespace LostTeam\Entities;

use LostTeam\command\PetCommand;
use LostTeam\task\PetsTick;
// use LostTeam\task\SpawnPetsTick;
use LostTeam\PetsPlayer;
use LostTeam\Pets;

use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Server;

class PetsManager implements Listener {

    public function __construct($plugin) {
        $server = Server::getInstance();
        $server->getCommandMap()->register('pets', new PetCommand('pet'));
        Entity::registerEntity(ChickenPet::class);
        Entity::registerEntity(WolfPet::class);
        Entity::registerEntity(PigPet::class);
        $server->getScheduler()->scheduleRepeatingTask(new PetsTick($plugin), 20*60);//run each minute for random pet messages
        //$server->getScheduler()->scheduleRepeatingTask(new SpawnPetsTick($plugin), 20);

    }

    public static function create($type, Position $source, ...$args) {
        $chunk = $source->getLevel()->getChunk($source->x >> 4, $source->z >> 4, true);

        $nbt = new Compound("", [
            "Pos" => new Enum("Pos", [
                new Double("", $source->x),
                new Double("", $source->y),
                new Double("", $source->z)
            ]),
            "Motion" => new Enum("Motion", [
                new Double("", 0),
                new Double("", 0),
                new Double("", 0)
            ]),
            "Rotation" => new Enum("Rotation", [
                new Float("", $source instanceof Location ? $source->yaw : 0),
                new Float("", $source instanceof Location ? $source->pitch : 0)
            ]),
        ]);
        return Entity::createEntity($type, $chunk, $nbt, $args);
    }

    /**
     * @param PetsPlayer $player
     * @param string $type
     * @param string $holdType
     */
    public static function createPet(PetsPlayer $player, $type = "", $holdType = "") {
        if (is_null($player->getPet())) {
            $len = rand(8, 12);
            $x = (-sin(deg2rad($player->yaw))) * $len  + $player->getX();
            $z = cos(deg2rad($player->yaw)) * $len  + $player->getZ();
            $y = $player->getLevel()->getHighestBlockAt($x, $z);

            $source = new Position($x , $y + 2, $z, $player->getLevel());
            if (empty($type)) {
                $pets = array("ChickenPet", "PigPet", "WolfPet");
                $type = $pets[rand(0, 2)];
            }
            if (!empty($holdType)) {
                $pets = array("ChickenPet", "PigPet", "WolfPet");
                foreach ($pets as $key => $petType) {
                    if($petType == $holdType) {
                        unset($pets[$key]);
                        break;
                    }
                }
                $type = $pets[array_rand($pets)];
            }
            $pet = self::create($type, $source);
            if($pet instanceof Pets);
            $pet->setOwner($player);
            $player->addPet($pet);
            $pet->spawnToAll();
        }
    }

    public function onJoin(PlayerJoinEvent $ev) {
        $player = $ev->getPlayer();
        $this->createPet($player);
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        if($player instanceof PetsPlayer);
        $pet = $player->getPet();
        if($pet instanceof Pets);
        if (!is_null($pet)) {
            $pet->fastClose();
        }
    }

    /**
     * Get last damager name if it's another player
     *
     * @param PlayerDeathEvent $event
     */
    public function onPlayerDeath(PlayerDeathEvent $event) {
        $player = $event->getEntity();
        if($player instanceof PetsPlayer);
        $attackerEvent = $player->getLastDamageCause();
        if ($attackerEvent instanceof EntityDamageByEntityEvent) {
            $attacker = $attackerEvent->getDamager();
            if ($attacker instanceof PetsPlayer) {
                $player->setLastDamager($attacker->getName());
            }
        }
    }
}