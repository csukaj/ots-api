<?php

namespace Tests\Procedure\Ots\PriceModifier;

class LongStayPercentageBasedOnBbPricePriceModifierTest extends PriceModifierTestCase
{

    protected $priceModifierName = 'Long Stay Percentage Based On B/B Price';
    protected $organizationId = 1;
    protected $meta = ['minimum_nights' => '2', 'minimum_nights_checking_level' => '515'];
    protected $classification = [];
    protected $offer = 'PercentageOffer';
    protected $application_type = 'room_request';

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
