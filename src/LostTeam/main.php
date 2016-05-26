<?php
namespace LostTeam;

use LostTeam\command\PetCommand;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class main extends PluginBase implements Listener {

	public static $pet;
	public static $petState;
	public $petType;
	public $wishPet;
	public static $isPetChanging;
	public static $type;

	public function onEnable() {
		$server = Server::getInstance();
		$server->getCommandMap()->register('Pets', new PetCommand($this, "pets"));
		Entity::registerEntity(ChickenPet::class);
		Entity::registerEntity(WolfPet::class);
		Entity::registerEntity(PigPet::class);
		Entity::registerEntity(BlazePet::class);
		Entity::registerEntity(MagmaPet::class);
		Entity::registerEntity(RabbitPet::class);
		Entity::registerEntity(BatPet::class);
		Entity::registerEntity(SilverfishPet::class);
		Entity::registerEntity(OcelotPet::class);
		//Entity::registerEntity(BlockPet::class);
		//$server->getScheduler()->scheduleRepeatingTask(new task\PetsTick($this), 20*60); //run each minute for random pet messages
		
	}

	public function create(Player $player,$type, Position $source, ...$args) {
		$chunk = $source->getLevel()->getChunk($source->x >> 4, $source->z >> 4, true);
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $source->x),
				new DoubleTag("", $source->y),
				new DoubleTag("", $source->z)
					]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
					]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", $source instanceof Location ? $source->yaw : 0),
				new FloatTag("", $source instanceof Location ? $source->pitch : 0)
					]),
		]);
		$pet = Entity::createEntity($type, $chunk, $nbt, ...$args);
		if(!is_null($pet)) {
			$pet->setOwner($player);
			$pet->spawnToAll();
		}
		return $pet; 
	}

	public function createPet(Player $player, $type) {
 		if (isset($this->pet[$player->getName()]) != true) {
			$len = rand(8, 12); 
			$x = (-sin(deg2rad($player->yaw))) * $len  + $player->getX();
			$z = cos(deg2rad($player->yaw)) * $len  + $player->getZ();
			$y = $player->getLevel()->getHighestBlockAt($x, $z);

			$source = new Position($x , $y + 2, $z, $player->getLevel());
			if (isset(self::$type[$player->getName()])){
				$type = self::$type[$player->getName()];
			}
			$pets = array("ChickenPet", "PigPet","WolfPet","BlazePet","RabbitPet","BatPet", "SilverfishPet", "OcelotPet");
 			$type = $pets[rand(0, 3)];
 			$pet = $this->create($player,$type, $source);
			return $pet;
 		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$pet = $player->getPet();
		if (!is_null($pet)) {
			$this->disablePet($player);
		}
	}

	public function onEntityDeath(EntityDeathEvent $event) {
		$entity = $event->getEntity();
		$attackerEvent = $entity->getLastDamageCause();
		if(!$entity instanceof Player and $entity instanceof Pets) {
			$this->disablePet($this->getOwner());
		}
		if ($attackerEvent instanceof EntityDamageByEntityEvent) {
			$attacker = $attackerEvent->getDamager();
			if ($attacker instanceof Player) {
				$entity->setLastDamager($attacker->getName());
			}
		}
	}

	public function togglePet(Player $player){
		if (isset(self::$pet[$player->getName()])){
			self::$pet[$player->getName()]->close();
			unset(self::$pet[$player->getName()]);
			$player->sendMessage("Pet Disapeared");
				
			return;
		}
		self::$pet[$player->getName()] = $this->createPet($player, "");
		$player->sendMessage("Enabled Pet!");
	}
	
	public function disablePet(Player $player) {
		if (isset(self::$pet[$player->getName()])){
			self::$pet[$player->getName()]->close();
			self::$pet[$player->getName()] = null;
		}
	}
	
	public function changePet(Player $player, $type){
		$this->disablePet($player);
		self::$pet[$player->getName()] = $this->createPet($player, $type);
	}
	
	public function getPet($player) {
		return self::$pet[$player];
	}
}
