<?php

namespace App\Providers\ChannelManager\HLS;

class GetRatePlansResponse
{

    /**
     * @var GetRatePlansResponse $GetRatePlansResult
     * @access public
     */
    public $GetRatePlansResult = null;

    /**
     * @param GetRatePlansResponse $GetRatePlansResult
     * @access public
     */
    public function __construct(GetRatePlansResponse $GetRatePlansResult)
    {
        $this->GetRatePlansResult = $GetRatePlansResult;
    }

}
