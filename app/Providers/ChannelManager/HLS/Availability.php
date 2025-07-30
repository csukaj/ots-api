<?php

namespace App\Providers\ChannelManager\HLS;

class Availability
{

    /**
     * @var DateRange $DateRange
     * @access public
     */
    public $DateRange = null;

    /**
     * @var int $Quantity
     * @access public
     */
    public $Quantity = null;

    /**
     * @var int $ReleasePeriod
     * @access public
     */
    public $ReleasePeriod = null;

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

    public function isValid(): bool
    {
        return !empty($this->DateRange) && is_a($this->DateRange, DateRange::class) && (is_numeric($this->Quantity) || is_null($this->Quantity));
    }

}
