<?php

namespace App\Providers\ChannelManager\HLS;

class InventoryRQ
{

    /**
     * @var ArrayCustom $RatePlans
     * @access public
     */
    public $RatePlans = null;

    /**
     * @var DateRange $DateRange
     * @access public
     */
    public $DateRange = null;

    /**
     * @var Credential $Credential
     * @access public
     */
    public $Credential = null;

    /**
     * @var string $Language
     * @access public
     */
    public $Language = null;

    /**
     * @access public
     * @param $ratePlans
     * @param $dateRange
     * @param Credential $credential
     * @param string $language
     */
    public function __construct(array $ratePlans, DateRange $dateRange, Credential $credential, string $language = 'en')
    {
        $this->RatePlans = $ratePlans;
        $this->DateRange = $dateRange;
        $this->Credential = $credential;
        $this->Language = $language;
    }

}
