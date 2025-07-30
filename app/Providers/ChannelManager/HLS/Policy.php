<?php

namespace App\Providers\ChannelManager\HLS;

class Policy
{

    /**
     * @var string $PolicyId
     * @access public
     */
    public $PolicyId = null;

    /**
     * @var int $DaysPriorCheckin
     * @access public
     */
    public $DaysPriorCheckin = null;

    /**
     * @var int $PenaltyType
     * @access public
     */
    public $PenaltyType = null;

    /**
     * @var float $PenaltyAmount
     * @access public
     */
    public $PenaltyAmount = null;

    /**
     * @var string $Action
     * @access public
     */
    public $Action = null;

    /**
     * @access public
     */
    public function __construct()
    {
    
    }

}
