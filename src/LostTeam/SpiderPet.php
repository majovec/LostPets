<?php
namespace LostTeam;

class SpiderPet extends Pets {

    const NETWORK_ID = 35;

    public $width;
    public $height;

    public function getName() {
        return "SpiderPet";
    }

    public function getSpeed() {
        return 1.4;
    }
}