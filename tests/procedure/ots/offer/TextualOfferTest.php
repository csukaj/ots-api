<?php

namespace Tests\Procedure\Ots\Offer;

class TextualOfferTest extends OfferTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $priceModifierName = 'Textual Offer';
    protected $organizationId = 1;

    /**
     * @test
     */
    function it_can_calculate_normal_case() {
        //negative number for meta[modifier_value]
        $fromDate = '2027-06-05';
        $toDate = '2027-06-10';
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 1]
                ]
            ]
        ];

        list(,, $organization) = $this->prepareTests();
        $result = $this->jsonDecode($this->getRooms($organization->id, $request, $fromDate, $toDate, $bookingDate), true);
        $actual = $result['results'][0][0]['prices'][0]['discounts'][2];
        $this->assertEquals(0, $actual['discount_value']);
        $this->assertEquals('textual', $actual['offer']);
        $this->assertNotEmpty($actual['description']);
        $this->assertEquals('This is a textual offer',$actual['description']['en']);
    }

}
