<?php

namespace App\Providers\ChannelManager\HLS;

class GetInventory
{

    /**
     * @var InventoryRQ $Request
     * @access public
     */
    public $Request = null;

    /**
     * @param InventoryRQ $Request
     * @access public
     */
    public function __construct(InventoryRQ $Request)
    {
        $this->Request = $Request;
    }

}
