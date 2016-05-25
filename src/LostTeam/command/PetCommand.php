<?php

namespace LostTeam\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use LostTeam\main;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class PetCommand extends PluginCommand {
	public $main, $current;
	public function __construct(main $main, $name) {
		parent::__construct($name, $main);
		$this->main = $main;
		$this->setPermission("pet.command");
		$this->setAliases(["pet"]);
	}

	public function execute(CommandSender $sender, $currentAlias, array $args) {
		if(!$sender instanceof Player) {
			$sender->sendMessage("Only Players can use this plugin");
			return true;
		}
		if (!isset($args[0])) {
			if($sender->hasPermission('pet.command.help')) {
				$sender->sendMessage(TF::YELLOW."=======".TF::BLUE."Pets".TF::YELLOW."=======");
				$sender->sendMessage(TF::YELLOW."/pets ");
				$sender->sendMessage(TF::YELLOW."/pets cycle");
				return true;
			}else{
				$sender->sendMessage(TF::RED . "You do not have permission to use this command");
			}
			return true;
		}
		switch (strtolower($args[0])){
			case "name":
			case "setname":
				if (isset($args[1])){
//					unset($args[0]);
					$name = implode(" ", $args);
					$this->main->getPet($sender->getName())->setNameTag($name);
					$sender->sendMessage("Set Name to ".$name);
				}
			break;
			case "help":
				if($sender->hasPermission('pet.command.help')) {
					$sender->sendMessage(TF::YELLOW."=======".TF::BLUE."Pets".TF::YELLOW."=======");
					$sender->sendMessage(TF::YELLOW."/pets help");
					$sender->sendMessage(TF::YELLOW."/pets cycle");
					$sender->sendMessage(TF::YELLOW."/pets name <Pet Name>");
				}else{
					$sender->sendMessage(TF::RED . "You do not have permission to use this command");
				}
			break;
			case "cycle":
				$types = array("ChickenPet","PigPet","WolfPet","BlazePet","RabbitPet","BatPet","SilverfishPet","MagmaPet");
				$new = null;
				if($this->current != count($types)-1) {
					$new = $this->current+1;
				}else{
					$new = 0;
				}
				$this->main->changePet($sender, $types[$new]);
			break;
		}
		return true;
	}

}
