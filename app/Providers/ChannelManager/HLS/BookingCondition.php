<?php

namespace App\Providers\ChannelManager\HLS;

class BookingCondition
{

    /**
     * @var string $BookingConditionId
     * @access public
     */
    public $BookingConditionId = null;

    /**
     * @var int $DepositType
     * @access public
     */
    public $DepositType = null;

    /**
     * @var float $DepositAmount
     * @access public
     */
    public $DepositAmount = null;

    /**
     * @var Policy[] $CancellationRules
     * @access public
     */
    public $CancellationRules = null;

    /**
     * @var string $AdditionalPolicies
     * @access public
     */
    public $AdditionalPolicies = null;

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

}
