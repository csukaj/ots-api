<?php

namespace Tests\Procedure\Ots\PriceModifier;

class EarlyBirdPriceModifierTest extends PriceModifierTestCase
{
    protected $priceModifierName = 'Early Bird';
    protected $organizationId = 1;
    protected $meta = [
        'minimum_nights' => '2',
        'booking_prior_maximum_days' => '60',
        'booking_prior_minimum_days' => '30',
        'minimum_nights_checking_level' => '515'
    ];
    protected $offer = 'PercentageOffer';
    protected $application_type = 'room_request';
    protected $bookingDate = '2027-04-15';

    /**
     * @test
     */
    function it_can_modify_offer()
    {
        $script = "print discount.calculate(factory.room_offer(), 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id'], 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id']).price";
        $actual = $this->jsonDecode($this->prepareAndRun([], $script), true);
        $this->assertEquals(-99, $actual);
    }
}