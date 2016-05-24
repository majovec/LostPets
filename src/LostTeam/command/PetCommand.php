<?php
namespace LostTeam\command;

use LostTeam\PetsPlayer;

use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\CommandSender;

class PetCommand extends VanillaCommand {

    public function __construct($name) {
        parent::__construct(
            $name, "Enable/disable/change pet", "/pet param", ["pets"]
        );
        $this->setPermission("pets.cmd");
    }

    public function execute(CommandSender $sender, $currentAlias, array $args) {
        if (!$this->testPermission($sender)) {
            return true;
        }

        if (!($sender instanceof PetsPlayer)) {
            return true;
        }
        if (!isset($args[0])) {
            $sender->setPetState('toggle');
//			$sender->togglePetEnable();
            return true;
        }

        $arg = strtolower($args[0]);

        if ($arg == "yes" || $arg == "on") {
            $sender->setPetState('show');
//			$sender->showPet();
            return true;
        }

        if ($arg == "no" || $arg == "off") {
            $sender->setPetState('hide');
//			$sender->hidePet();
            return true;
        }

        $avilablePets = array("dog", "pig", "chicken");
        if (in_array($arg, $avilablePets)) {
            if ($arg == "dog") {
                $arg = "wolf";
            }
            $sender->setPetState('show', ucfirst($arg) . "Pet");
//			$sender->showPet(ucfirst($arg) . "Pet");
            return true;
        }

        $sender->togglePetEnable();
        return true;
    }

}
