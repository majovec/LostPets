<?php
namespace LostTeam;

class EndermanPet extends Pets {

    const NETWORK_ID = 38;

    public $width;
    public $height;

    public function getName() {
        return "EndermanPet";
    }

    public function getSpeed() {
        return 1.8;
    }
}