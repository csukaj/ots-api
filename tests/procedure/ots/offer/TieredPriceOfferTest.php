<?php

namespace Tests\Procedure\Ots\Offer;

class TieredPriceOfferTest extends OfferTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $priceModifierName = 'Tiered pricing 6-9pax';
    protected $organizationId = 16;
    private $offerMetaTxId = 469;
    private $fromDate = '2027-02-22';
    private $toDate = '2027-02-27';


    /**
     * @test
     */
    function it_can_calculate_normal_case()
    {
        //positive number for meta[*_value]
        $bookingDate = '2027-01-01';
        $testSetups =
            [
                [
                    'request' => [
                        [
                            'usage' => [
                                ['age' => 21, 'amount' => 3]
                            ]
                        ]
                    ],
                    'expectedValue' => 300 * 5 // fix value * nights
                ],
                [
                    'request' => [
                        [
                            'usage' => [
                                ['age' => 21, 'amount' => 3],
                                ['age' => 10, 'amount' => 1]
                            ]
                        ]
                    ],
                    'expectedValue' => (300 + (4 - 4 + 1) * 165) * 5 // (fix value + headcount@border * pax_value) * nights
                ],
                [
                    'request' => [
                        [
                            'usage' => [
                                ['age' => 21, 'amount' => 3],
                                ['age' => 10, 'amount' => 1],
                                ['age' => 10, 'amount' => 1],
                                ['age' => 10, 'amount' => 1]
                            ]
                        ]
                    ],
                    'expectedValue' => (300 + (6 - 4 + 1) * 165) * 5 // (fix value + headcount_difference * pax_value) * nights
                ]
            ];

        list(, , $organization) = $this->prepareTests();

        foreach ($testSetups as $testSetup) {
            $result = $this->jsonDecode($this->getRooms($organization->id, $testSetup['request'], $this->fromDate,
                $this->toDate,
                $bookingDate),
                true);
            $actual = $result['results'][0][0]['prices'][0];
            $this->assertEquals($testSetup['expectedValue'], $actual['discounted_price']);
            $this->assertCount(1, $actual['discounts']);
            $this->assertEquals($this->priceModifierName, $actual['discounts'][0]['name']['en']);
            $this->assertEquals('tiered_price', $actual['discounts'][0]['offer']);
        }
    }

    /**
     * @test
     */
    function it_doesnt_fail_for_empty_meta()
    {
        //meta[modifier_value] not defined
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 3],
                    ['age' => 21, 'amount' => 3]
                ]
            ]
        ];

        list($priceModifier, , $organization) = $this->prepareTests();
        $metaModifier = $this->getMetaModifierScript($priceModifier->id, $this->offerMetaTxId, '');
        $rawResult = $this->getRooms($organization->id, $request, $this->fromDate, $this->toDate, $bookingDate, null,
            $metaModifier);
        $result = $this->jsonDecode($rawResult, true);
        $actual = $result['results'][0][0]['prices'][0]['discounts'];
        $this->assertEmpty($actual);
    }


    /**
     * @test
     */
    function it_doesnt_fail_for_illegal_argument()
    {
        //string or some crap value like that for meta[modifier_value]
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 3],
                    ['age' => 21, 'amount' => 3]
                ]
            ]
        ];

        list($priceModifier, , $organization) = $this->prepareTests();
        $metaModifier = $this->getMetaModifierScript($priceModifier->id, $this->offerMetaTxId, '{badjsonvalue');
        $result = $this->jsonDecode($this->getRooms($organization->id, $request, $this->fromDate, $this->toDate,
            $bookingDate, null,
            $metaModifier), true);
        $actual = $result['results'][0][0]['prices'][0]['discounts'];
        $this->assertEmpty($actual);
    }

}
