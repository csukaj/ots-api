<?php

namespace Tests\Functional\Search\Accommodation\Pricing;

use Tests\TestCase;

class NetBasedDiscountsTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($request, $name) {
        return $this->prepareAccommodationSearchResult($request['interval'], $name, $request['usages']);
    }

    /**
     * @test
     */
    public function No01Test() {
        $organization = 'Hotel Net Price';
        $frontendData = [
            'interval' => [
                'date_from' => '2027-06-09',
                'date_to' => '2027-06-14'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($frontendData, $organization)['best_price'];
        
        $this->assertEquals(650, $actual['original_price']);
        $this->assertEquals(496, $actual['discounted_price']);
        $this->assertEquals(['value' => 154, 'percentage' => 23.69], $actual['total_discount']);
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
