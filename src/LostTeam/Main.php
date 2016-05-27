<?php
namespace LostTeam;

use LostTeam\task\PetsTick;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {
	public static $pet, $petState, $isPetChanging, $type;
	public $pets, $petType, $wishPet, $current;
	public function onEnable() {
		Entity::registerEntity(ChickenPet::class);
		Entity::registerEntity(WolfPet::class);
		Entity::registerEntity(PigPet::class);
		Entity::registerEntity(BlazePet::class);
		Entity::registerEntity(MagmaPet::class);
		Entity::registerEntity(RabbitPet::class);
		Entity::registerEntity(BatPet::class);
		Entity::registerEntity(SilverfishPet::class);
		//Entity::registerEntity(BlockPet::class);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new PetsTick($this), 20*60); //run each minute for random pet messages
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if(strtolower($command) === "pet" or strtolower($command) === "pets") {
			if(!$sender instanceof Player) {
				$sender->sendMessage("Only Players can use this plugin");
				return true;
			}
			if (!isset($args[0])) {
				if($sender->hasPermission('pet.command.help')) {
					$sender->sendMessage(TF::YELLOW."=======".TF::BLUE."Pets".TF::YELLOW."=======");
					$sender->sendMessage(TF::YELLOW."/pets help");
					$sender->sendMessage(TF::YELLOW."/pets cycle");
					$sender->sendMessage(TF::YELLOW."/pets name <Pet Name>");
					$sender->sendMessage(TF::YELLOW."/pets list");
					$sender->sendMessage(TF::YELLOW."/pets clear");
					return true;
				}else{
					$sender->sendMessage(TF::RED . "You do not have permission to use this command");
				}
				return true;
			}
			switch (strtolower($args[0])) {
				case "name":
				case "setname":
					if(!$sender->hasPermission('pet.command.name')) {
						$sender->sendMessage(TF::RED . "You do not have permission to use this command");
						return true;
					}
					if (isset($args[1])) {
						$this->getPet($sender)->setNameTag($args[1]);
						$sender->sendMessage("Name now set to: ".$args[1]);
					}
					break;
				case "help":
					if(!$sender->hasPermission('pet.command.help')) {
						$sender->sendMessage(TF::RED . "You do not have permission to use this command");
						return true;
					}
					$sender->sendMessage(TF::YELLOW."=======".TF::BLUE."Pets".TF::YELLOW."=======");
					$sender->sendMessage(TF::YELLOW."/pets help");
					$sender->sendMessage(TF::YELLOW."/pets cycle");
					$sender->sendMessage(TF::YELLOW."/pets name <Pet Name>");
					$sender->sendMessage(TF::YELLOW."/pets list");
					$sender->sendMessage(TF::YELLOW."/pets clear");
					break;
				case "cycle":
					if(!$sender->hasPermission('pet.command.cycle')) {
						$sender->sendMessage(TF::RED . "You do not have permission to use this command");
						return true;
					}
					$types = array("ChickenPet","PigPet","WolfPet","BlazePet","RabbitPet","BatPet","SilverfishPet","MagmaPet");
					$new = null;
					if($this->current[$sender->getName()] != count($types)-1) {
						$new = $this->current[$sender->getName()]+1;
					}else{
						$new = 0;
					}
					$this->changePet($sender, $types[$new]);
					break;
				case "list":
					if(!$sender->hasPermission('pet.command.help')) {
						$sender->sendMessage(TF::RED . "You do not have permission to use this command");
						return true;
					}
					$sender->sendMessage(TF::YELLOW."=======".TF::BLUE."Pets List".TF::YELLOW."=======");
					$n = null;
					foreach($this->getServer()->getLevels() as $level) {
						foreach($level->getEntities() as $entity) {
							if($entity instanceof Pets) {
								if($entity->getName() !== null) {
									$sender->sendMessage($entity->getName());
								}else {
									$sender->sendMessage("Un-Named Pet");
								}
								$n+=1;
							}
						}
					}
					$sender->sendMessage(TF::YELLOW."Total Pet Count is ".TF::BLUE.TF::BOLD.$n);
					break;
				case "clear":
					$n = null;
					foreach($this->getServer()->getLevels() as $level) {
						foreach($level->getEntities() as $entity) {
							if($entity instanceof Pets) {
								$entity->close();
								$n+=1;
							}
						}
					}
					$sender->sendMessage(TF::YELLOW."Total Cleared Pets are ".TF::BLUE.TF::BOLD.$n." Pets");
					break;
				default:
					if($sender->hasPermission('pet.command')) {
						$sender->sendMessage(TF::YELLOW."=======".TF::BLUE."Pets".TF::YELLOW."=======");
						$sender->sendMessage(TF::YELLOW."/pets help");
						$sender->sendMessage(TF::YELLOW."/pets cycle");
						$sender->sendMessage(TF::YELLOW."/pets name <Pet Name>");
						$sender->sendMessage(TF::YELLOW."/pets list");
						$sender->sendMessage(TF::YELLOW."/pets clear");
						return true;
					}else{
						$sender->sendMessage(TF::RED . "You do not have permission to use this command");
						return true;
					}
					break;
			}
			return true;
		}
		return false;
	}

	public function create(Player $player,$type, Position $source, ...$args)
	{
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
		$pet = Entity::createEntity($type, $chunk, $nbt, ...$args);
		if ($pet instanceof Pets and !is_null($pet)) {
			$pet->setOwner($player);
			$pet->spawnToAll();
		}else{
			$player->sendMessage("");
		}
		return $pet;
	}

	public function createPet(Player $player, $type = null) {
		if (isset($this->pets[$player->getName()]) != true) {
			$pets = array("ChickenPet", "PigPet","WolfPet","BlazePet","RabbitPet","BatPet", "SilverfishPet", "MagmaPet", "OcelotPet");
			$len = rand(8, 12);
			$x = (-sin(deg2rad($player->yaw))) * $len  + $player->getX();
			$z = cos(deg2rad($player->yaw)) * $len  + $player->getZ();
			$y = $player->getLevel()->getHighestBlockAt($x, $z);

			$source = new Position($x , $y + 2, $z, $player->getLevel());
			if (!isset($type)) {
				$this->current[$player->getName()] = rand(0, count($pets)-1);
				$type = $pets[$this->current[$player->getName()]];
			}
			for($n = 0; $n != 9; $n+=1) {
				if($type === $pets[$n]) {
					$this->current[$player->getName()] = $n;
					break;
				}
			}
			$pet = $this->create($player,$type, $source);
			return $pet;
		}
		$player->sendMessage(TF::RED."You can only have one pet! This may be a glitch...");
		return null;
	}

	public function onPlayerQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$pet = $this->getPet($player);
		if (!is_null($pet)) {
			$this->disablePet($player);
		}
	}

	public function onDeath(EntityDeathEvent $event) {
		$entity = $event->getEntity();
		$attackerEvent = $entity->getLastDamageCause();
		if(!$entity instanceof Player and $entity instanceof Pets) {
			$this->disablePet($entity->getOwner());
		}
		if ($attackerEvent instanceof EntityDamageByEntityEvent) {
			$attacker = $attackerEvent->getDamager();
			if (isset(self::$pet[$entity->getName()])) {
				self::$pet[$entity->getName()]->setLastDamager($attacker->getName());
				return;
			}
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$this->getServer()->getCommandMap()->dispatch($player, "pet cycle");
	}

	public function togglePet(Player $player) {
		if (isset(self::$pet[$player->getName()])) {
			self::$pet[$player->getName()]->close();
			unset(self::$pet[$player->getName()]);
			return;
		}
		self::$pet[$player->getName()] = $this->createPet($player);
	}

	public function disablePet(Player $player) {
		if (isset(self::$pet[$player->getName()])) {
			self::$pet[$player->getName()]->close();
			self::$pet[$player->getName()] = null;
		}
	}

	public function changePet(Player $player, $newtype) {
		$this->disablePet($player);
		self::$pet[$player->getName()] = $this->createPet($player, $newtype);
	}

	public function getPet(Player $player) {
		if(self::$pet instanceof Pets) {
			return self::$pet[$player->getName()];
		}else{
			return self::$pet[$player->getName()];
		}
	}
}
