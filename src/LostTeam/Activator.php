<?php
namespace LostTeam;

use LostTeam\Entities\PetsManager;
use LostTeam\task\PetsTick;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class Activator extends PluginBase {
    private static $instance = null;
    public $commands;
    public function onEnable() {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new PetsManager($this), $this);
        // $this->getServer()->getScheduler()->scheduleRepeatingTask(new PetsTick($this), 20*60);
        $this->getLogger()->notice(TF::GREEN."Enabled!");
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        $commandName = $command->getName();
        $params = array('sender' => $sender, 'args' => $args);
        $this->commands->$commandName($params);
        return true;
    }
    public function onDisable() {
        $this->getLogger()->notice(TF::GREEN."Disabled!");
    }
}