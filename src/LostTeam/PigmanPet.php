<?php
namespace LostTeam;

class PigmanPet extends Pets {

    const NETWORK_ID = 36;

    public $width;
    public $height;

    public function getName() {
        return "PigmanPet";
    }

    public function getSpeed() {
        return 1.2;
    }
}