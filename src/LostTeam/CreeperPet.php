<?php

namespace LostTeam;

class CreeperPet extends Pets {
    const NETWORK_ID = 33;

    public $height = 0.75;
    public $width = 0.4;

    public function getName(){
        return "CreeperPet";
    }

    public function getSpeed(){
        return 1.2;
    }
}
