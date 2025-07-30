<?php

namespace App\Providers\ChannelManager\HLS;

class RoomType
{

    /**
     * @var string $RoomId
     * @access public
     */
    public $RoomId = null;

    /**
     * @var string $Name
     * @access public
     */
    public $Name = null;

    /**
     * @var RatePlan[] $RatePlans
     * @access public
     */
    public $RatePlans = null;

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
