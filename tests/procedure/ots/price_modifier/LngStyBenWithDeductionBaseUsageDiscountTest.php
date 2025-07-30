<?php

namespace Tests\Procedure\Ots\PriceModifier;

class LngStyBenWithDeductionBaseUsagePriceModifierTest extends PriceModifierTestCase {
    
    protected $priceModifierName = 'Long stay benefit w/ deduction base usage';
    protected $organizationId = 1;
    protected $meta =[];
    protected $classification =[];
    protected $offer = 'FreeNightsOffer';
    protected $application_type = 'room_request';

    /**
     * @test
     */
    function it_can_modify_offer() {
        $script = "
usage_elements = [{\"age\": 21, \"amount\": 1}, {\"age\": 5, \"amount\": 1}, {\"age\": 6, \"amount\": 1}]
room_request = factory.room_request(usage_elements=usage_elements)
room_offer = factory.room_offer(room_request=room_request)
print discount.calculate(room_offer, 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id'], 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id']).price";
        $actual = $this->jsonDecode($this->prepareAndRun([], $script), true);
        $this->assertEquals(-55, $actual);
    }
}
