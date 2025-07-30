<?php

namespace App\Providers\ChannelManager\HLS;

class Credential
{

    /**
     * @var string $ChannelManagerUsername
     * @access public
     */
    public $ChannelManagerUsername = null;

    /**
     * @var string $ChannelManagerPassword
     * @access public
     */
    public $ChannelManagerPassword = null;

    /**
     * @var string $ChannelManagerAuthenticationKey
     * @access public
     */
    public $ChannelManagerAuthenticationKey = null;

    /**
     * @var string $HotelId
     * @access public
     */
    public $HotelId = null;

    /**
     * @var string $HotelUsername
     * @access public
     */
    public $HotelUsername = null;

    /**
     * @var string $HotelPassword
     * @access public
     */
    public $HotelPassword = null;

    /**
     * @var string $HotelAuthenticationKey
     * @access public
     */
    public $HotelAuthenticationKey = null;

    /**
     * @var string $HotelAuthenticationChannelKey
     * @access public
     */
    public $HotelAuthenticationChannelKey = null;

    /**
     * @access public
     * @param array $credentialConfig
     */
    public function __construct(array $credentialConfig)
    {
        foreach ($credentialConfig as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

}
