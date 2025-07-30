<?php

namespace App\Providers\ChannelManager\HLS;

class RatePlansRQ
{

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
     * @param Credential $credential
     * @param string $language
     */
    public function __construct(Credential $credential, string $language = 'en')
    {
        $this->Credential = $credential;
        $this->Language = $language;
    }

}
