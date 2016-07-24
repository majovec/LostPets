<?php
namespace LostTeam\task;

use LostTeam\Main;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class PetsTick extends PluginTask {

    public $main;

    /**
     * PetsTick constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
		parent::__construct($plugin);
		$this->main = $plugin;
	}

    /**
     * @param $currentTick
     */
    public function onRun($currentTick) {
		if($this->main instanceof Main);
		$onlinePlayers = Server::getInstance()->getOnlinePlayers();
		foreach ($onlinePlayers as $player) {
			if ($this->needPetMessage($player)) {
				$this->main->sendPetMessage($player, 3);
			}
		}
	}

    /**
     * @param Player $player
     * @return bool
     */
    private function needPetMessage(Player $player) {
		if($this->main instanceof Main);
		if(in_array($player->getName(),$this->main->users)) {
			if($this->main->getPet($player) !== null) {
				if(rand(1,15) == 13) {
					return true;
				}
			}
		}
		return false;
	}
}
