<?php
namespace LostTeam;

class PigmanPet extends Pets {

    const NETWORK_ID = 36;

    public $width = 0.6;
    public $height = 1.8;

    public function getName() {
        return "PigmanPet";
    }

    public function getSpeed() {
        return 1.2;
    }
}