<?php

namespace App\Providers\ChannelManager\HLS;

class GetInventoryResponse
{

    /**
     * @var GetInventoryResponse $GetInventoryResult
     * @access public
     */
    public $GetInventoryResult = null;

    /**
     * @param GetInventoryResponse $GetInventoryResult
     * @access public
     */
    public function __construct(GetInventoryResponse $GetInventoryResult)
    {
        $this->GetInventoryResult = $GetInventoryResult;
    }

}
