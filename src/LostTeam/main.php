<?php
namespace LostTeam;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Compound;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Server;
use LostTeam\command\PetCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class main extends PluginBase implements Listener {

	public static $pet;
	public $pets;
	public static $petState;
	public $petType;
	public $wishPet;
	public static $isPetChanging;
	public static $type;
	public function onEnable() {
		$server = Server::getInstance();
		$server->getCommandMap()->register('pets', new PetCommand($this));
		Entity::registerEntity(ChickenPet::class);
		Entity::registerEntity(WolfPet::class);
		Entity::registerEntity(PigPet::class);
		Entity::registerEntity(BlazePet::class);
		Entity::registerEntity(MagmaPet::class);
		Entity::registerEntity(RabbitPet::class);
		Entity::registerEntity(BatPet::class);
		Entity::registerEntity(SilverfishPet::class);
		//Entity::registerEntity(BlockPet::class);
		//$server->getScheduler()->scheduleRepeatingTask(new task\PetsTick($this), 20*60);//run each minute for random pet messages
		//$server->getScheduler()->scheduleRepeatingTask(new task\SpawnPetsTick($this), 20);
		
	}

	public function create($player,$type, Position $source, ...$args) {
		$chunk = $source->getLevel()->getChunk($source->x >> 4, $source->z >> 4, true);
		$nbt = new Compound("", [
			"Pos" => new Compound("Pos", [
				new Double("", $source->x),
				new Double("", $source->y),
				new Double("", $source->z)
					]),
			"Motion" => new Compound("Motion", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
					]),
			"Rotation" => new Compound("Rotation", [
				new Float("", $source instanceof Location ? $source->yaw : 0),
				new Float("", $source instanceof Location ? $source->pitch : 0)
					]),
		]);
		$pet = Entity::createEntity($type, $chunk, $nbt, ...$args);
		if($pet instanceof Pets);
		$pet->setOwner($player);
		$pet->spawnToAll();
		return $pet; 
	}

	public function createPet(Player $player, $type) {
 		if (isset($this->pets[$player->getName()]) != true) {
			$len = rand(8, 12); 
			$x = (-sin(deg2rad($player->yaw))) * $len  + $player->getX();
			$z = cos(deg2rad($player->yaw)) * $len  + $player->getZ();
			$y = $player->getLevel()->getHighestBlockAt($x, $z);

			$source = new Position($x , $y + 2, $z, $player->getLevel());
			if (isset(self::$type[$player->getName()])){
				$type = self::$type[$player->getName()];
			}
 			switch ($type){
 				case "WolfPet":
 				break;
 				case "ChickenPet":
 				break;
 				case "PigPet":
 				break;
 				case "BlazePet":
 				break;
 				case "MagmaPet";
 				break;
				case "RabbitPet";
					break;
				case "BatPet";
					break;
				case "SilverfishPet";
					break;
 				default:
 					$pets = array("ChickenPet", "PigPet","WolfPet","BlazePet","RabbitPet","BatPet", "SilverfishPet", "MagmaPet");
 					$type = $pets[rand(0, count($pets)-1)];
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
	
	/**
	 * Get last damager name if it's another player
	 * 
	 * @param PlayerDeathEvent $event
	 */
	public function onPlayerDeath(PlayerDeathEvent $event) {
		$player = $event->getEntity();
		$attackerEvent = $player->getLastDamageCause();
		if ($attackerEvent instanceof EntityDamageByEntityEvent) {
			$attacker = $attackerEvent->getDamager();
			if (isset(self::$pet[$player->getName()])) {
				self::$pet[$player->getName()]->setLastDamager($attacker->getName());
				unset(self::$pet[$player->getName()]);
				$player->sendMessage("Pet Disappeared because you died!");
				return;
			}
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		self::$pet[$player->getName()] = $this->createPet($player, "");
	}

	public function togglePet(Player $player){
		if (isset(self::$pet[$player->getName()])){
			self::$pet[$player->getName()]->fastClose();
			unset(self::$pet[$player->getName()]);
			$player->sendMessage("Pet Disappeared");
				
			return;
		}
		self::$pet[$player->getName()] = $this->createPet($player, "");
		$player->sendMessage("Pet Created!");
	}
	
	public function disablePet(Player $player){
		if (isset(self::$pet[$player->getName()])){
			self::$pet[$player->getName()]->fastClose();
			unset(self::$pet[$player->getName()]);
		}
	}
	
	public function changePet(Player $player, $newtype){
		$this->disablePet($player);
		self::$pet[$player->getName()] = $this->createPet($player, $newtype);
	}
	
	public function getPet($player) {
		return self::$pet[$player];
	}
}
