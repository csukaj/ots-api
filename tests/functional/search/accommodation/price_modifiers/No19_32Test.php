<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

/**
 * For test setup and parameters @see https://docs.google.com/spreadsheets/d/1N3G3oyaqNeMeaipDyHD6r8DBlGIobd6jA_jIwfoJpr0/edit?ts=586f5dfc#gid=1635043115
 */
class No19_32Test extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($requestParams)
    {

        $request = $requestParams['request'];
        $wedding = isset($request['wedding_date']) ? $request['wedding_date'] : null;

        return $this->prepareAccommodationSearchResult($request['interval'], $requestParams['hotel'], $request['usages'],
            $wedding);
    }

    /**
     * @test
     */
    public function No19Test()
    {
        $request = [
            'hotel' => 'Hilton St. Anne',
            'request' => [
                'interval' => [
                    'date_from' => '2027-04-09',
                    'date_to' => '2027-04-14'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2],
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(1, count($bestPrice['devices']));
        $this->assertEquals('Standard Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(400, $bestPrice['original_price']);
        $this->assertEquals(320, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 80, 'percentage' => 20], $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No21Test()
    {
        $request = [
            'hotel' => 'Hotel A',
            'request' => [
                'interval' => [
                    'date_from' => '2027-06-10',
                    'date_to' => '2027-06-16'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1],
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1],
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(2, count($bestPrice['devices']));
        $this->assertEquals('Deluxe Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals('Deluxe Room', $bestPrice['devices'][1]['name']['en']);
        $this->assertEquals(1320, $bestPrice['original_price']);
        $this->assertEquals(990, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 1320 - 990, 'percentage' => 25], $bestPrice['total_discount']);
        $this->assertEquals('h/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No22Test()
    {
        $request = [
            'hotel' => 'Hilton St. Anne',
            'request' => [
                'interval' => [
                    'date_from' => '2027-06-04',
                    'date_to' => '2027-06-14'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1],
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(1, count($bestPrice['devices']));
        $this->assertEquals('Standard Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(800, $bestPrice['original_price']);
        $this->assertEquals(640, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 160, 'percentage' => 20], $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No23Test()
    {
        $request = [
            'hotel' => 'Hotel B',
            'request' => [
                'interval' => [
                    'date_from' => '2027-06-05',
                    'date_to' => '2027-06-12'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2],
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(1, count($bestPrice['devices']));
        $this->assertEquals('Double Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(924, $bestPrice['original_price']);
        $this->assertEquals(792, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 132, 'percentage' => 14.29], $bestPrice['total_discount']);
        $this->assertEquals('h/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No24Test()
    {
        $request = [
            'hotel' => 'Hotel C',
            'request' => [
                'interval' => [
                    'date_from' => '2027-08-10',
                    'date_to' => '2027-08-19'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2],
                            ['age' => 3, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(1, count($bestPrice['devices']));
        $this->assertEquals('Dorm', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(3654, $bestPrice['original_price']);
        $this->assertEquals(3160.8, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 3654 - 3160.8, 'percentage' => 13.5], $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No25Test()
    {
        $request = [
            'hotel' => 'Hotel B',
            'request' => [
                'interval' => [
                    'date_from' => '2027-06-01',
                    'date_to' => '2027-06-15'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ]
                ],
                'wedding_date' => '2027-05-06'
            ]
        ];

        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(1, count($bestPrice['devices']));
        $this->assertEquals('Double Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(1848, $bestPrice['original_price']);
        $this->assertEquals(792, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 1848 - 792, 'percentage' => 57.14], $bestPrice['total_discount']);
        $this->assertEquals('h/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No26Test()
    {
        $request = [
            'hotel' => 'Hotel C',
            'request' => [
                'interval' => [
                    'date_from' => '2027-07-01',
                    'date_to' => '2027-07-10'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 4, 'amount' => 1],
                            ['age' => 6, 'amount' => 1]
                        ]
                    ]
                ],
                'wedding_date' => '2022-06-20'
            ]
        ];


        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(2, count($bestPrice['devices']));
        $this->assertEquals('Dorm', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(4842, $bestPrice['original_price']);
        $this->assertEquals(3718.8, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 4842 - 3718.8, 'percentage' => 23.2], $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No28Test()
    {
        $request = [
            'hotel' => 'Hotel F',
            'request' => [
                'interval' => [
                    'date_from' => '2027-04-03',
                    'date_to' => '2027-04-18'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(2, count($bestPrice['devices']));
        $this->assertEquals('Apartman', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(5940, $bestPrice['original_price']);
        $this->assertEquals(4752, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 5940 - 4752, 'percentage' => 20], $bestPrice['total_discount']);
        $this->assertEquals('h/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No29Test()
    {
        $request = [
            'hotel' => 'Hilton Budapest',
            'request' => [
                'interval' => [
                    'date_from' => '2027-06-05',
                    'date_to' => '2027-06-13'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 9, 'amount' => 1],
                            ['age' => 12, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $originalPrice = (80 + 80 + 80) * 8; //1920
        $priceModifieredPrice = 1670.4;
        $bestPrice = $actual['best_price'];
        $this->assertEquals(3, count($bestPrice['devices']));
        $this->assertEquals('Standard Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals($originalPrice, $bestPrice['original_price']);
        $this->assertEquals($priceModifieredPrice, $bestPrice['discounted_price']);
        $this->assertEquals([
            'value' => $originalPrice - $priceModifieredPrice,
            'percentage' => round(($originalPrice - $priceModifieredPrice) / $originalPrice * 100, 2)
        ], $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No30Test()
    {
        $request = [
            'hotel' => 'Hilton Budapest',
            'request' => [
                'interval' => [
                    'date_from' => '2027-10-12',
                    'date_to' => '2027-10-19'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 5, 'amount' => 1],
                            ['age' => 7, 'amount' => 1],
                            ['age' => 9, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(2, count($bestPrice['devices']));
        $this->assertEquals('Standard Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals('Standard Room', $bestPrice['devices'][1]['name']['en']);
        $this->assertEquals((90 + 120) * 7, $bestPrice['original_price']);
            $this->assertEquals((630 - 63) + (840 - 84 - 75.6), $bestPrice['discounted_price']);
        $this->assertEquals(['value' => (90 + 120) * 7 - ((630 - 63) + (840 - 84 - 75.6)), 'percentage' => 15.14],
            $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No31Test()
    {
        $request = [
            'hotel' => 'Hilton St. Anne',
            'request' => [
                'interval' => [
                    'date_from' => '2027-09-04',
                    'date_to' => '2027-09-10'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 6, 'amount' => 1],
                            ['age' => 8, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(2, count($bestPrice['devices']));
        $this->assertEquals('Standard Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(1140, $bestPrice['original_price']);
        $this->assertEquals(750, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 1140 - 750, 'percentage' => 34.21], $bestPrice['total_discount']);
        $this->assertEquals('b/b', $bestPrice['meal_plan']);
    }

    /**
     * @test
     */
    public function No32Test()
    {
        $request = [
            'hotel' => 'Hotel A',
            'request' => [
                'interval' => [
                    'date_from' => '2027-09-12',
                    'date_to' => '2027-09-19'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 4, 'amount' => 1],
                            ['age' => 5, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($request);

        // check best price
        $bestPrice = $actual['best_price'];
        $this->assertEquals(2, count($bestPrice['devices']));
        $this->assertEquals('Double Room', $bestPrice['devices'][0]['name']['en']);
        $this->assertEquals(2100, $bestPrice['original_price']);
        $this->assertEquals(1800, $bestPrice['discounted_price']);
        $this->assertEquals(['value' => 2100 - 1800, 'percentage' => 14.29], $bestPrice['total_discount']);
        $this->assertEquals('h/b', $bestPrice['meal_plan']);
    }

}
