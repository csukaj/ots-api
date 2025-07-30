<?php

namespace Tests\Functional\Search\Accommodation\Pricing;

use App\Organization;
use Tests\TestCase;

class BundledTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    
     private function prepare($request, $hotelName) {
        return $this->prepareAccommodationSearchResult($request['interval'], $hotelName, $request['usages']);
    }

    /**
     * @test
     */
    public function No01Test() {
        $organization = 'Hotel B';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-09',
                        'date_to' => '2026-06-14'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals(660, $actual['original_price']);
        $this->assertEquals(660, $actual['discounted_price']);
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function No02Test() {
        $organization = 'Hotel E';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-10',
                        'date_to' => '2026-06-17'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals(924, $actual['original_price']);
        $this->assertEquals(924, $actual['discounted_price']);
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('f/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function No03Test() {
        $organization = 'Hotel C';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-10',
                        'date_to' => '2026-06-16'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                                    ['age' => 8, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Dorm', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals(2436, $actual['original_price']);
        $this->assertEquals(2436, $actual['discounted_price']);
    }

    /**
     * @test
     */
    public function No04Test() {
        $organization = 'Hotel A';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-04',
                        'date_to' => '2026-06-14'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals(1100, $actual['original_price']);
        $this->assertEquals(1100, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No05Test() {
        $organization = 'Hotel A';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-14',
                        'date_to' => '2026-06-17'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(825, $actual['original_price']);
        $this->assertEquals(825, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No06Test() {
        $organization = 'Hotel C';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-17',
                        'date_to' => '2026-06-22'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Dorm', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals(660, $actual['original_price']);
        $this->assertEquals(660, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No07Test() {
        $organization = 'Hotel B';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-22',
                        'date_to' => '2026-06-27'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(660, $actual['original_price']);
        $this->assertEquals(660, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No08Test() {
        $organization = 'Hotel F';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-10',
                        'date_to' => '2026-07-23'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Apartman', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(1716, $actual['original_price']);
        $this->assertEquals(1716, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No09Test() {
        $organization = 'Hotel D';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-05',
                        'date_to' => '2026-07-09'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(2, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('Single Room', $actual['devices'][1]['name']['en']);
        $this->assertEquals(1056, $actual['original_price']);
        $this->assertEquals(1056, $actual['discounted_price']);
        $this->assertEquals('f/b', $actual['meal_plan']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No10Test() {
        $organization = 'Hotel B';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-10',
                        'date_to' => '2026-07-19'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(1188, $actual['original_price']);
        $this->assertEquals(1188, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No11Test() {
        $organization = 'Hotel B';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-03',
                        'date_to' => '2026-06-19'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                                    ['age' => 3, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(4384, $actual['original_price']);
        $this->assertEquals(4384, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No12Test() {
        $organization = 'Hotel C';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-05',
                        'date_to' => '2026-06-13'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                                    ['age' => 9, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Dorm', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals(3248, $actual['original_price']);
        $this->assertEquals(3248, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No13Test() {
        $organization = 'Hotel F';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-12',
                        'date_to' => '2026-06-19'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                                    ['age' => 1, 'amount' => 1],
                                    ['age' => 4, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Apartman', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(3696, $actual['original_price']);
        $this->assertEquals(3696, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No14Test() {

        $organization = 'Hotel E';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-04',
                        'date_to' => '2026-07-09'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2]
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 9, 'amount' => 1],
                                    ['age' => 18, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(2, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('Double Room', $actual['devices'][1]['name']['en']);
        $this->assertEquals(1320, $actual['original_price']);
        $this->assertEquals(1320, $actual['discounted_price']);
        $this->assertEquals('f/b', $actual['meal_plan']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No15Test() {

        $organization = 'Hotel G';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-06-12',
                        'date_to' => '2026-06-19'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2]
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 16, 'amount' => 1],
                                    ['age' => 17, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(2, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('Double Room', $actual['devices'][1]['name']['en']);
        $this->assertEquals('inc', $actual['meal_plan']);
        $this->assertEquals(3696, $actual['original_price']);
        $this->assertEquals(3696, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No16Test() {
        $organization = 'Hotel C';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-12',
                        'date_to' => '2026-07-19'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                                    ['age' => 5, 'amount' => 1],
                                    ['age' => 8, 'amount' => 1],
                                    ['age' => 12, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Dorm', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals(4760, $actual['original_price']);
        $this->assertEquals(4760, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No17Test() {
        $organization = 'Hotel Intercontinental';
        
        ///this is a pricing test but this date range is in 2027. Need to refactor Intercontinental date range 
        // in hotel.json for consistency
        
        $frontendData = [
                    'interval' => [
                        'date_from' => '2027-07-01',
                        'date_to' => '2027-07-10'
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
                                    ['age' => 7, 'amount' => 1],
                                    ['age' => 9, 'amount' => 1]
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 13, 'amount' => 1],
                                    ['age' => 15, 'amount' => 1]
                            ]
                        ],
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(3, count($actual['devices']));
        $this->assertEquals('Classic Standard Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('Classic Standard Room', $actual['devices'][1]['name']['en']);
        $this->assertEquals('Classic Standard Room', $actual['devices'][2]['name']['en']);
        $this->assertEquals(5580, $actual['discounted_price']);
        $this->assertEquals(5580, $actual['original_price']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No18Test() {
        $organization = 'Hotel F';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-28',
                        'date_to' => '2026-08-02'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(2, count($actual['devices']));
        $this->assertEquals('Apartman', $actual['devices'][0]['name']['en']);
        $this->assertEquals('Apartman', $actual['devices'][1]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
        $this->assertEquals(1980, $actual['original_price']);
        $this->assertEquals(1980, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No19Test() {

        $organization = 'Hotel J';
        $frontendData = [
                    'interval' => [
                        'date_from' => '2026-07-06',
                        'date_to' => '2026-07-13'
                    ],
                    'usages' => [
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 2],
                                    ['age' => 5, 'amount' => 1],
                                    ['age' => 8, 'amount' => 1],
                                    ['age' => 12, 'amount' => 1]
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 5, 'amount' => 1],
                                    ['age' => 8, 'amount' => 1],
                                    ['age' => 12, 'amount' => 1]
                            ]
                        ],
                            [
                            'usage' => [
                                    ['age' => 21, 'amount' => 1]
                            ]
                        ]
                    ]
                ];

        $expected_price = ((200 + 3 * 80) + (200 + 80) + 110) * 7;

        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(3, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('Comfy Room', $actual['devices'][1]['name']['en']);
        $this->assertEquals('Comfy Room', $actual['devices'][2]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals($expected_price, $actual['original_price']);
        $this->assertEquals($expected_price, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No20Test() {
        $organization = 'Hotel B';
        $frontendData = [
            'interval' => [
                'date_from' => '2026-06-09',
                'date_to' => '2026-06-14'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1],
                        ['age' => 6, 'amount' => 1]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals(660, $actual['original_price']);
        $this->assertEquals(660, $actual['discounted_price']);
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('h/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function No21Test() {

        $organization = 'Hotel J';
        $frontendData = [
            'interval' => [
                'date_from' => '2026-07-06',
                'date_to' => '2026-07-11'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2],
                        ['age' => 3, 'amount' => 1]
                    ]
                ]
            ]
        ];

        $expected_price = 280 * 5;

        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals($expected_price, $actual['original_price']);
        $this->assertEquals($expected_price, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No22Test() {
        $organization = TestCase::getOrganizationsByName('Hotel I')[0];
        $frontendData = [
            'interval' => [
                'date_from' => '2026-07-06',
                'date_to' => '2026-07-11'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2],
                        ['age' => 10, 'amount' => 1]
                    ]
                ]
            ]
        ];
        $actual = $this->prepare($frontendData,null);

        $this->assertTrue(!isset($actual[$organization->id]));
    }

    /**
     * @test
     */
    public function No23Test() {

        $organization = 'Hotel I';
        $frontendData = [
            'interval' => [
                'date_from' => '2026-06-06',
                'date_to' => '2026-06-11'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2],
                        ['age' => 16, 'amount' => 1]
                    ]
                ]
            ]
        ];

        $expected_price = 550;

        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals($expected_price, $actual['original_price']);
        $this->assertEquals($expected_price, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

    /**
     * @test
     */
    public function No24Test() {

        $organization = 'Hotel I';
        $frontendData = [
            'interval' => [
                'date_from' => '2026-06-16',
                'date_to' => '2026-06-21'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2]
                    ]
                ],
                [
                    'usage' => [
                        ['amount' => 1, 'age' => 16],
                        ['amount' => 1, 'age' => 16]
                    ]
                ]
            ]
        ];

        $expected_price = 880;

        $actual = $this->prepare($frontendData,$organization)['best_price'];
        $this->assertEquals(2, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals('b/b', $actual['meal_plan']);
        $this->assertEquals($expected_price, $actual['original_price']);
        $this->assertEquals($expected_price, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
    }

}
