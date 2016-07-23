<?php
namespace LostTeam;

use pocketmine\entity\ProjectileSource;

class SkeletonPet extends Pets implements ProjectileSource {

    const NETWORK_ID = 34;

    public $width = 0.6;
    public $height = 1.8;

    public function getName(){
        return "SkeletonPet";
    }

    public function getSpeed() {
        return 1.2;
    }
}