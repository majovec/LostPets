<?php
namespace LostTeam;

use LostTeam\Entities\PetsManager;

use pocketmine\item\Item;
use pocketmine\network\SourceInterface;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Custom player to extend inside all game plugins
 * override player inside your plugin inside event onPlayerCreation
 */
class PetsPlayer extends Player {

    // player state for alert system
    const IN_LOBBY = 0;
    const IN_COUNTDOWN = 1;
    const IN_GAME = 2;
    const NOT_IN_ARENA = -1;
    const VIP = 'vip';
    const VIP_PLUS = 'vip+';
    const DEFAULT_NAME_COLOR = TextFormat::WHITE;
    // modes for special work with chat
    const CHAT_MODE_NORMAL = 0;
    const CHAT_MODE_PWD_CHANGE = 1;
    const CHAT_MODE_REGISTRATION = 2;
    // TH consts
    const FOUND_TREASURE = "Congratulations, you have won a free tee-shirt.\n".
    "  You must be first to it claim at ".TextFormat::RED."lbsg.net/contest".TextFormat::GREEN."   \n".
    "           Subject to rules on page.           ";
    const TH_SHOW_MESSAGE_DURATION = 20;	// in seconds

    /**@var string*/
    protected $passHash = '';
    /** @var string */
    protected $bcryptPassHash = '';
    /**@var string*/
    public $language = 'English';
    /**@var string*/
    public $countryIsoCode = '';
    /**@var string*/
    public $idAddress = '';
    /**@var bool*/
    protected $isVipEnabled = true;
    /**@var string*/
    public $vipStatus = '';
    /**@var string*/
    protected $namePrefix = '';
    /**@var string*/
    protected $namePrefixWithRanks = '';
    /**@var bool*/
    protected $showRanksInPrefix = true;
    /**@var bool*/
    protected $isAuthenticated = false;
    /**@var bool*/
    protected $isRegistered = false;
    /**@var bool*/
    protected $isMuted = false;
    /**@var bool*/
    protected $isInvincible = false;
    /**@var bool*/
    protected $isInvisible = false;
    /**@var bool*/
    protected $isInvoulnerable = false;
    /**@var bool*/
    protected $isLocked = false;
    /**@var string*/
    protected $lockReason = '';
    // for statistics
    public $coinsNum = 0;
    public $killsNum = 0;
    public $deathNum = 0;
    public $goldNum = 0;
    public $hackingFlag = false;
    /**@var array*/
    public $ignoreList = array();

    // fields for alert system
    /**@var int*/
    protected $stateForAlertSystem = self::IN_LOBBY;
    /**@var int*/
    protected $currentArenaId = self::NOT_IN_ARENA;

    // for pass change and registraion processes
    /**@var int*/
    protected $chatMode = self::CHAT_MODE_NORMAL;
    /**@var array*/
    protected $passChangeData = array();
    /**@var array*/
    protected $registrationData = array();
    /** @var bool */
    protected $isNeedsNewPassHash = false;	// necessary to change the password

    // for kits component
    /**@var array*/
//	protected $kits = array();
    /**@var int - only one kit allowed*/
    protected $currentKit = 0;
    /**@var array*/
    protected $kitsAdditionalData = array();
    /**@var int - contains kit id on sign with that player interacted*/
    public $kitSignLastTapped = 0;

    //for vip lounge
    /**@var bool*/
    protected $pushedByLoungeGuard = false;
    /**@var bool*/
    protected $gotHealEffect = false;

    protected $foundTreasureTimestamp = 0;

    protected $isInDeathmatch = false;

    protected $needParticles = true;
    protected $particleHotbar = [];

    protected $lastMove = -1;

    protected $pet = null;
    protected $petType = "";
    protected $petEnable = true;
    protected $petState;
    protected $muteTime = 0;

    protected $savePassword = "";
    /** @var string|null - used for pet messages*/
    protected $lastDamager = null;
    /** @var string|null */
    protected $joinedGameLast = null;
    /** @var bool */
    public $isPetChanging = false;
    /** @var string */
    public $wishPet = "";
    /** @var string */
    protected $lastCameInLobby = null;

    protected $particleEffectId = 0;

    protected $wonLastMacth = false;

    public function __construct(SourceInterface $interface, $clientID, $ip, $port) {
        parent::__construct($interface, $clientID, $ip, $port);
    }
    public function createPet() {
        if ($this->stateForAlertSystem == self::IN_LOBBY && $this->petEnable && !isset($this->pet)) {
            PetsManager::createPet($this, $this->petType);
            Pets::sendPetMessage($this, Pets::OWNER_IS_BACK);
        }
    }

    public function setStateCountdown($arenaId) {
        $this->stateForAlertSystem = self::IN_COUNTDOWN;
        $this->currentArenaId = $arenaId;
        if($this->needParticles){
            $this->getInventory()->remove(Item::get(Item::BUCKET));
            $this->getInventory()->remove(Item::get(Item::REDSTONE));
            $this->getInventory()->remove(Item::get(120));
            $this->getInventory()->remove(Item::get(378));
            $this->getInventory()->sendContents($this);
        }
        if (isset($this->pet) && $this->pet instanceof Pets) {
            $this->pet->close();
            $this->pet = null;
            $this->joinedGameLast = date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Set item to player's inventory, give it to hand,
     * show it inside inventory immediately
     *
     * @param int $index
     * @param Item $item
     */
    public function setHotbarItem($index, Item $item) {
        $inventory = $this->getInventory();
        $inventory->clear($index);
        $inventory->setItem($index, $item);
        $inventory->setHotbarSlotIndex($index, $index);
        $inventory->sendContents($this);
    }

    public function isInvisible() {
        return $this->isInvisible;
    }

    public function setInvisible($invisible = true) {
        $this->isInvisible = $invisible;
    }

    public function spawnTo(Player $player) {
        if (!$this->isInvisible()) {
            parent::spawnTo($player);
        }
    }

    public function isInvoulnerable() {
        return $this->isInvoulnerable;
    }

    public function setInvoulnerable($invoulnerable = true) {
        $this->isInvoulnerable = $invoulnerable;
    }

    public function getLastMove(){
        return $this->lastMove;
    }

    public function setLastMove($lastMove){
        $this->lastMove = $lastMove;
    }

    public function addPet($pet) {
        if($pet instanceof Pets);
        $this->pet = $pet;
        $this->petType = $pet->getName();
    }

    public function getPet() {
        return isset($this->pet) ? $this->pet : null;
    }

    public function togglePetEnable() {
        if ($this->stateForAlertSystem == self::IN_LOBBY && !$this->isPetChanging) {
            if (isset($this->pet)) {
                if($this->pet instanceof Pets);
                $this->pet->close();
                $this->pet = null;
                $this->isPetChanging = true;
            } else {
                $this->enablePet($this);
            }
        }
    }

    public function hidePet() {
        $this->petEnable = false;
        if (isset($this->pet)) {
            if($this->pet instanceof Pets);
            $this->pet->close();
            $this->pet = null;
            //send random bye message from pet
            Pets::sendPetMessage($this, Pets::PET_IS_GONE);
        }
    }

    public function showPet($type = "") {
        if ($this->stateForAlertSystem == self::IN_LOBBY && !$this->isPetChanging) {
            $this->wishPet = !empty($type) ? $type : $this->petType;
            if (isset($this->pet)) {
                if($this->pet instanceof Pets);
                $this->pet->close();
                $this->pet = null;
                $this->isPetChanging = true;
            } else {
                $this->enablePet($this, $this->wishPet);
            }
        }
    }

    /**
     * Enable pet depending on params - have or not wishPet,
     * also: toggle or the same pet we need
     *
     * @param PetsPlayer $player
     * @param string $wishPet
     */
    public function enablePet($player, $wishPet = "") {
        $player->petEnable = true;
        if ($this->stateForAlertSystem == self::IN_LOBBY) {
            $type = "";
            $holdType = "";
            if (empty($wishPet)) {
                $holdType = $this->petType;
            } else {
                $type = $this->wishPet;
                $this->wishPet = "";
            }
            PetsManager::createPet($player, $type, $holdType);
            Pets::sendPetMessage($player, Pets::PET_SUMMONING);
        }
    }
    /* saved lastDamager name for pets messages*/
    public function getLastDamager() {
        return $this->lastDamager;
    }

    public function setLastDamager($name = null) {
        $this->lastDamager = $name;
    }
    
    public function getLobbyTime() {
        return $this->lastCameInLobby;
    }

    public function setLobbyTime($value = null) {
        $this->lastCameInLobby = $value;
    }

    public function getMuteTime(){
        return $this->muteTime;
    }

    public function setMuteTime($muteTime){
        $this->muteTime = $muteTime;
    }

    public function savePassword($password) {
        $this->savePassword = $password;
    }

    public function getAirTick() {
        return $this->inAirTicks;
    }

    public function setPetState($state, $petType = "", $delay = 2) {
        $this->petState = array(
            'state' => $state,
            'petType' => $petType,
            'delay' => $delay
        );
    }

    public function getPetState(){
        if(isset($this->petState['state'])) {
            if($this->petState['delay'] > 0){
                $this->petState['delay']--;
                return false;
            }
            return $this->petState;
        }
        return false;
    }

    public function clearPetState(){
        unset($this->petState);
    }

    public function setParticleEffectId($id){
        $this->particleEffectId = $id;
    }

    public function getParticleEffectId(){
        return $this->particleEffectId;
    }
}
