<?php

namespace App\Providers\ChannelManager\HLS;

class GetRatePlans
{

    /**
     * @var RatePlansRQ $Request
     * @access public
     */
    public $Request = null;

    /**
     * @param RatePlansRQ $Request
     * @access public
     */
    public function __construct(RatePlansRQ $Request)
    {
        $this->Request = $Request;
    }

}
