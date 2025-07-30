<?php

namespace Tests\Procedure\Ots\PriceModifier;

class AnniversaryPriceModifierTest extends PriceModifierTestCase
{

    protected $priceModifierName = 'Wedding Anniversary';
    protected $organizationId = 1;
    protected $weddingDate = '2022-07-06';
    protected $meta = [
        'anniversary_in_range_days' => '30',
        'minimum_nights' => '2',
        'minimum_nights_checking_level' => '515'
    ];
    protected $classification = [];
    protected $offer = 'FreeNightsOffer';
    protected $application_type = 'room_request';

    /**
     * @test
     */
    function it_can_modify_offer_with_normal_anniversary_priceModifier()
    {
        $script = "print discount.calculate(factory.room_offer(), 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id'], 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id']).price";
        $actual = $this->jsonDecode($this->prepareAndRun([], $script), true);
        $this->assertEquals(-110, $actual);
    }
}
