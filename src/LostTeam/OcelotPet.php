<?php
namespace LostTeam;

use pocketmine\entity\Tameable;

class OcelotPet extends Pets  implements Tameable{

    const NETWORK_ID = 22;

    const DATA_CAT_TYPE = 18;

    const TYPE_WILD = 0;

    public $width = 0.312;

    public $height = 0.75;

    public function getName(){
        return "OcelotPet";
    }

    public function getSpeed(){
        return "1.4";
    }
    
    public function isTamed() {
        return false;
    }
}