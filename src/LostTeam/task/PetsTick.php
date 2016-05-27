<?php
namespace LostTeam\task;

use LostTeam\Pets;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

/**
 * This task checks every minute if player is need to get random pet message
 */
class PetsTick extends PluginTask {
	public $main;
	/**
	 * Base class constructor
	 * @param $plugin
	 */
	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->main = $plugin;
	}
	
	/**
	 * Repeatable check for pet message receivers
	 * 
	 * @param int $currentTick
	 */
	public function onRun($currentTick) {
		$onlinePlayers = \pocketmine\Server::getInstance()->getOnlinePlayers();
 		foreach ($onlinePlayers as $player) {
 			if (self::needPetMessage($player)) {
 				Pets::sendPetMessage($player, "PET_RANDOM");
 			}
 		}
	}
	
	/**
	 * Check if player needs pet message
	 *
	 * @ param LbPlayer $player
	 * @ return bool
	 */
 	private function needPetMessage($player) {
		if($player instanceof Player);
		if($this->main->getPet($player)) {
			if(rand(1,15) == 13) {
				return true;
			}
		}
		return false;
	}
}
