<?php

namespace App\Providers\ChannelManager\HLS;

class RatePlan
{

    /**
     * @var string $RatePlanId
     * @access public
     */
    public $RatePlanId = null;

    /**
     * @var string $Name
     * @access public
     */
    public $Name = null;

    /**
     * @var int $GuestsIncluded
     * @access public
     */
    public $GuestsIncluded = null;

    /**
     * @var int $AdultGuestsIncluded
     * @access public
     */
    public $AdultGuestsIncluded = null;

    /**
     * @var int $ChildGuestsIncluded
     * @access public
     */
    public $ChildGuestsIncluded = null;

    /**
     * @var int $MaxGuests
     * @access public
     */
    public $MaxGuests = null;

    /**
     * @var string $ExtraGuestsConfig
     * @access public
     */
    public $ExtraGuestsConfig = null;

    /**
     * @var float $MinRoomRate
     * @access public
     */
    public $MinRoomRate = null;

    /**
     * @var MealsIncluded $MealsIncluded
     * @access public
     */
    public $MealsIncluded = null;

    /**
     * @var LastMinuteDefault $LastMinuteDefault
     * @access public
     */
    public $LastMinuteDefault = null;

    /**
     * @var BookingCondition $BookingCondition
     * @access public
     */
    public $BookingCondition = null;

    /**
     * @var string $RoomId
     * @access public
     */
    public $RoomId = null;

    /**
     * @var string $Inclusions
     * @access public
     */
    public $Inclusions = null;

    /**
     * @var string $InclusionsName
     * @access public
     */
    public $InclusionsName = null;

    /**
     * @var string $InclusionsDescription
     * @access public
     */
    public $InclusionsDescription = null;

    /**
     * @var int $Order
     * @access public
     */
    public $Order = null;

    /**
     * @access public
     */
    public function __construct()
    {

    }

}
