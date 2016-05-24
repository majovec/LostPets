<?php

namespace LostTeam\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use LostTeam\main;
use pocketmine\utils\TextFormat as TF;

class PetCommand extends PluginCommand {

	public function __construct(main $main, $name) {
		parent::__construct(
				$name, $main
		);
		$this->main = $main;
		$this->setPermission("pets.command");
		$this->setAliases(array("pet"));
	}

	public function execute(CommandSender $sender, $currentAlias, array $args) {
	
		if (!isset($args[0])) {
			$this->main->togglePet($sender);
			return true;
		}
		switch (strtolower($args[0])){
			case "name":
			case "setname":
				if (isset($args[1])){
					unset($args[0]);
					$name = implode(" ", $args);
					$this->main->getPet($sender->getName())->setNameTag($name);
					$sender->sendMessage("Set Name to ".$name);
				}
				return true;
			break;
			case "help":
				if($sender->hasPermission('pet.command.help')) {
					$sender->sendMessage("§e=======PetHelp=======");
					$sender->sendMessage("§b/pets = Spawn your pet");
					$sender->sendMessage("§b/pets type [type]");
					return true;
				}else{
					$sender->sendMessage(TF::RED . "You do not have permission to use this command");
				}
			break;
			case "type":
				if (isset($args[1])){
					switch ($args[1]){
						case "wolf":
						case "dog":
							if ($sender->hasPermission("pets.type.dog")){
								$this->main->changePet($sender, "WolfPet");
								$sender->sendMessage("Changed Pet to Wolf!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for dog pet!");
								return true;
							}
						break;
						case "chicken":
							if ($sender->hasPermission("pets.type.chicken")){
								$this->main->changePet($sender, "ChickenPet");
								$sender->sendMessage("Changed Pet to Chicken!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for chicken pet!");
								return true;
							}
						break;
						case "pig":
							if ($sender->hasPermission("pets.type.pig")){
								$this->main->changePet($sender, "PigPet");
								$sender->sendMessage("Changed Pet to Pig!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for pig pet!");
								return true;
							}
						break;
						case "blaze":
							if ($sender->hasPermission("pets.type.blaze")){
								$this->main->changePet($sender, "BlazePet");
								$sender->sendMessage("Changed Pet to Blaze!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for blaze pet!");
								return true;
							}
						break;
						case "magma":
							if ($sender->hasPermission("pets.type.magma")){
								$this->main->changePet($sender, "MagmaPet");
								$sender->sendMessage("Changed Pet to Magma!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for blaze pet!");
								return true;
							}
							break;
						case "rabbit":
							if($sender->hasPermission("pets.type.rabbit")) {
								$this->main->changePet($sender, "RabbitPet");
								$sender->sendMessage("Changed Pet to Rabbit!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for the rabbit pet!");
								return true;
							}
						break;
						case "bat":
							if($sender->hasPermission("pets.type.bat")) {
								$this->main->changePet($sender, "BatPet");
								$sender->sendMessage("Changed Pet to Bat!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for the bat pet!");
								return true;
							}
							break;
						case "silverfish":
							if($sender->hasPermission("pets.type.silverfish")) {
								$this->main->changePet($sender, "SilverFishPet");
								$sender->sendMessage("Changed Pet to SilverFish!");
								return true;
							}else{
								$sender->sendMessage("You do not have permission for the silverfish pet!");
								return true;
							}
							break;
						default:
							$sender->sendMessage("/pet type [type]");
							$sender->sendMessage("Types: blaze, pig, chicken, dog, rabbit, magma, bat, silverfish");
						return true;
					}
				}
			break;
		}
		return true;
	}

}
