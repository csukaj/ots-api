<?php

namespace Tests\Procedure\Ots\Offer;

class FixedPriceOfferTest extends OfferTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $priceModifierName = 'Annual Minimum Nights';
    protected $organizationId = 16;
    private $offerMetaTxId;


    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->offerMetaTxId = 280;//Config::get('taxonomies.price_modifier_offers.fixed_price.metas.modifier_value.id');
    }

    /**
     * @test
     */
    function it_can_calculate_normal_case() {
        //negative number for meta[modifier_value]
        $fromDate = '2027-05-05';
        $toDate = '2027-05-10';
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
        $actual = $result['results'][0][0]['prices'][0]['discounts'][0]['discount_value'];
        $this->assertEquals(-100, $actual);
    }

    /**
     * @test
     */
    function it_doesnt_fail_for_empty_meta() {
        //meta[modifier_value] not defined
        $fromDate = '2027-05-05';
        $toDate = '2027-05-10';
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 1]
                ]
            ]
        ];

        list($priceModifier,, $organization) = $this->prepareTests();
        $metaModifier = $this->getMetaModifierScript($priceModifier->id, $this->offerMetaTxId, '');
        $rawResult = $this->getRooms($organization->id, $request, $fromDate, $toDate, $bookingDate, null, $metaModifier);
        $result = $this->jsonDecode($rawResult, true);
        $actual = $result['results'][0][0]['prices'][0]['discounts'];
        $this->assertEmpty($actual);
    }

    /**
     * @test
     */
    function it_can_calculate_negative_price_modifier_case() {
        //positive number for meta[modifier_value]
        $fromDate = '2027-05-05';
        $toDate = '2027-05-10';
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 1]
                ]
            ]
        ];

        list($priceModifier,, $organization) = $this->prepareTests();
        $metaModifier = $this->getMetaModifierScript($priceModifier->id, $this->offerMetaTxId, '{\"adult\":100}');
        $result = $this->jsonDecode($this->getRooms($organization->id, $request, $fromDate, $toDate, $bookingDate, null, $metaModifier), true);
        $actual = $result['results'][0][0]['prices'][0]['discounts'][0]['discount_value'];
        $this->assertEquals(100, $actual);
    }

    /**
     * @test
     */
    function it_doesnt_fail_for_illegal_argument() {
        //string or some crap value like that for meta[modifier_value]
        $fromDate = '2027-05-05';
        $toDate = '2027-05-10';
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 1]
                ]
            ]
        ];

        list($priceModifier,, $organization) = $this->prepareTests();
        $metaModifier = $this->getMetaModifierScript($priceModifier->id, $this->offerMetaTxId, '{badjsonvalue');
        $result = $this->jsonDecode($this->getRooms($organization->id, $request, $fromDate, $toDate, $bookingDate, null, $metaModifier), true);
        $actual = $result['results'][0][0]['prices'][0]['discounts'];
        $this->assertEmpty($actual);
    }

}
