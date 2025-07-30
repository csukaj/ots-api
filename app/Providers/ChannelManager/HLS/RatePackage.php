<?php

namespace App\Providers\ChannelManager\HLS;

class RatePackage
{

    /**
     * @var string $RatePlanId
     * @access public
     */
    public $RatePlanId = null;

    /**
     * @var RateDetail $Rate
     * @access public
     */
    public $Rate = null;

    /**
     * @var RateDetail $ExtraAdultRate
     * @access public
     */
    public $ExtraAdultRate = null;

    /**
     * @var RateDetail $ExtraChildRate
     * @access public
     */
    public $ExtraChildRate = null;

    /**
     * @var int $MinNights
     * @access public
     */
    public $MinNights = null;

    /**
     * @var int $MaxNights
     * @access public
     */
    public $MaxNights = null;

    /**
     * @var int $CloseToArrival
     * @access public
     */
    public $CloseToArrival = null;

    /**
     * @var int $CloseToDeparture
     * @access public
     */
    public $CloseToDeparture = null;

    /**
     * @var int $StopSell
     * @access public
     */
    public $StopSell = null;

    /**
     * @var DateRange $DateRange
     * @access public
     */
    public $DateRange = null;

    /**
     * @var string $Channel
     * @access public
     */
    public $Channel = null;

    /**
     * @access public
     */
    public function __construct()
    {
    
    }

}
