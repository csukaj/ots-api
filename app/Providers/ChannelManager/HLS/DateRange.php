<?php

namespace App\Providers\ChannelManager\HLS;

class DateRange
{

    /**
     * @var string $From
     * @access public
     */
    public $From = null;

    /**
     * @var string $To
     * @access public
     */
    public $To = null;

    /**
     * @param string $From
     * @param string $To
     * @access public
     */
    public function __construct(string $From, string $To)
    {
        $this->From = $From;
        $this->To = $To;
    }

    public function isValid(): bool
    {
        return !empty($this->From) && is_string($this->From) && !empty($this->To) && is_string($this->To);
    }

}
