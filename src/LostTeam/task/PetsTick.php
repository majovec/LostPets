<?php
namespace LostTeam\task;

use LostTeam\Main;
use LostTeam\Pets;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class PetsTick extends PluginTask {
	public $main;
	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->main = $plugin;
	}

	public function onRun($currentTick) {
		$onlinePlayers = \pocketmine\Server::getInstance()->getOnlinePlayers();
 		foreach ($onlinePlayers as $player) {
 			if ($this->needPetMessage($player)) {
 				Pets::sendPetMessage($player, 3);
 			}
 		}
	}

 	private function needPetMessage(Player $player) {
		if($this->main instanceof Main);
		if($this->main->getPet($player)) {
			if(rand(1,15) == 13) {
				return true;
			}
		}
		return false;
	}
}
