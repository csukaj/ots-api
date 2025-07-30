<?php

namespace App\Providers\ChannelManager\HLS;

class Inventory
{

    /**
     * @var string $RoomId
     * @access public
     */
    public $RoomId = null;

    /**
     * @var Availability[] $Availabilities
     * @access public
     */
    public $Availabilities = null;

    /**
     * @var RatePackage[] $RatePackages
     * @access public
     */
    public $RatePackages = null;

    /**
     * @access public
     */
    public function __construct()
    {
    
    }

    public function isValid(): bool
    {
        if(!is_array($this->Availabilities)){
            return false;
        }
        foreach ($this->Availabilities as $availability){
            if(!is_a($availability, Availability::class) || !$availability->isValid()){
                return false;
            }
        }
        return !empty($this->RoomId) && is_string($this->RoomId);
    }

}
