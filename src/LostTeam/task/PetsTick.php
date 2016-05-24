<?php

namespace LostTeam\task;

use LostTeam\Pets;
use LostTeam\PetsPlayer;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

/**
 * This task checks every 1 minute if player is need to get random pet message
 */
class PetsTick extends PluginTask {

    /**
     * Base class constructor
     * @param $plugin
     */
    public function __construct($plugin) {
        parent::__construct($plugin);
    }

    /**
     * Repeating check for pet message receivers
     *
     * @param int $currentTick
     */
    public function onRun($currentTick) {
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();
        foreach ($onlinePlayers as $player) {
            if($player instanceof PetsPlayer);
            if (self::needPetMessage($player)) {
                Pets::sendPetMessage($player, Pets::PET_LOBBY_RANDOM);
            }
        }
    }

    /**
     * Check if player gets pet message
     *
     * @param PetsPlayer $player
     * @return bool
     */
    private static function needPetMessage($player) {
        if($player instanceof PetsPlayer);
        if(rand(1,15) == 13) {
            return true;
        }else{
            return false;
        }
    }
}
