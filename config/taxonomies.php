<?php
return [
    '_next_id' => 563,
    'language' => 1,
    'languages' => [
        'english' => [
            'id' => 2,
            'language_id' => 1,
            'iso_code' => 'en',
            'date_format' => 'MM/DD/YYYY',
            'time_format' => 'MM/DD/YYYY h:mmA',
            'first_day_of_week' => 'sunday',
            'plural' => 300,
            'plurals' => [
                'plural' => [
                    'id' => 301,
                    'name' => 'Plural'
                ]
            ]
        ],
        'german' => [
            'id' => 3,
            'language_id' => 2,
            'iso_code' => 'de',
            'date_format' => 'D.M.YYYY',
            'time_format' => 'D.M.YYYY H:mm',
            'first_day_of_week' => 'monday',
            'plural' => 302,
            'plurals' => [
                'plural' => [
                    'id' => 303,
                    'name' => 'Plural'
                ]
            ]
        ],
        'hungarian' => [
            'id' => 4,
            'language_id' => 3,
            'iso_code' => 'hu',
            'date_format' => 'YYYY.MM.DD.',
            'time_format' => 'YYYY.MM.DD. H:mm',
            'first_day_of_week' => 'monday',
            'plural' => 304,
            'plurals' => [
                'plural' => [
                    'id' => 305,
                    'name' => 'Plural'
                ]
            ]
        ],
        'russian' => [
            'id' => 29,
            'language_id' => 4,
            'iso_code' => 'ru',
            'date_format' => 'D.M.YYYY',
            'time_format' => 'D.M.YYYY H:mm',
            'first_day_of_week' => 'monday',
            'plural' => 306,
            'plurals' => [
                'few' => [
                    'id' => 307,
                    'name' => 'Few ("-a"  or "-i" form)'
                ],
                'many' => [
                    'id' => 308,
                    'name' => 'Many ("-ov" or "-ey" form)'
                ]
            ]
        ],
    ],
    'default_language' => 'english',
    'contact_type' => 5,
    'contact_types' => [
        'email' => 6,
        'web' => 7,
        'phone' => 8,
        'bank_account_number' => 523,
        'limonetik_merchant_id' => 540
    ],
    'organization_type' => 9,
    'organization_types' => [
        'hotel_chain' => [
            'id' => 10,
            'categories' => []
        ],
        'accommodation' => [
            'id' => 11,
            'categories' => []
        ],
        'ship_company' => [
            'id' => 12,
            'categories' => []
        ],
        'ship' => [
            'id' => 13,
            'categories' => []
        ],
        'customer' => [
            'id' => 14,
            'categories' => []
        ],
        'supplier' => [
            'id' => 522,
            'categories' => []
        ]
    ],
    'organization_group_type' => 374,
    'organization_group_types' => [
        'ship_group' => [
            'id' => 375,
            'categories' => []
        ]
    ],
    'age_range' => 15,
    'age_ranges' => [
        'adult' => [
            'id' => 16,
            'translations' => [
                'en' => [
                    'singular' => 'adult',
                    'plurals' => [
                        'plural' => 'adults'
                    ]
                ],
                'de' => [
                    'singular' => 'Erwachsener',
                    'plurals' => [
                        'plural' => 'Erwachsene'
                    ]
                ],
                'hu' => [
                    'singular' => 'felnőtt',
                    'plurals' => [
                        'plural' => 'felnőttek'
                    ]
                ],
                'ru' => [
                    'singular' => 'взрослый',
                    'plurals' => [
                        'few' => 'взрослых',
                        'many' => 'взрослых'
                    ]
                ]
            ]
        ],
        'child' => [
            'id' => 17,
            'translations' => [
                'en' => [
                    'singular' => 'child',
                    'plurals' => [
                        'plural' => 'children'
                    ]
                ],
                'de' => [
                    'singular' => 'Kind',
                    'plurals' => [
                        'plural' => 'Kinder'
                    ]
                ],
                'hu' => [
                    'singular' => 'gyerek',
                    'plurals' => [
                        'plural' => 'gyerekek'
                    ]
                ],
                'ru' => [
                    'singular' => 'ребенок',
                    'plurals' => [
                        'few' => 'ребенка',
                        'many' => 'детей'
                    ]
                ]
            ]
        ],
        'baby' => [
            'id' => 18,
            'translations' => [
                'en' => [
                    'singular' => 'baby',
                    'plurals' => [
                        'plural' => 'babies'
                    ]
                ],
                'de' => [
                    'singular' => 'Baby',
                    'plurals' => [
                        'plural' => 'Babys'
                    ]
                ],
                'hu' => [
                    'singular' => 'csecsemő',
                    'plurals' => [
                        'plural' => 'csecsemők'
                    ]
                ],
                'ru' => [
                    'singular' => 'ребенок',
                    'plurals' => [
                        'few' => 'младенцев',
                        'many' => 'детей'
                    ]
                ]
            ]
        ]
    ],
    'meal_plan' => 19,
    'meal_plans' => [
        'e/p' => ['id' => 20, 'meal_plan_id' => 1, 'service_bitmap' => 0],
        'b/b' => ['id' => 21, 'meal_plan_id' => 2, 'service_bitmap' => 1],
        'h/b' => ['id' => 22, 'meal_plan_id' => 3, 'service_bitmap' => 5],
        'f/b' => ['id' => 23, 'meal_plan_id' => 4, 'service_bitmap' => 7],
        'inc' => ['id' => 24, 'meal_plan_id' => 5, 'service_bitmap' => 15]
    ],
    'device' => 25,
    'devices' => [
        'room' => 26,
        'cabin' => 403
    ],
    'organization_description' => 30,
    'organization_descriptions' => [
        'short_description' => 31,
        'long_description' => 108
    ],
    'organization_group_description' => 400,
    'organization_group_descriptions' => [
        'short_description' => 401,
        'long_description' => 402
    ],
    'organization_date_range' => 32,
    'organization_date_ranges' => [
        'pricing_date_range' => 33
    ],
    'product' => 34,
    'products' => [],
    'price_difference_type' => 35,
    'price_difference_types' => [
        'from_net_with_value' => 36,
        'from_net_with_percent' => 37,
        'from_gross_with_value' => 38,
        'from_gross_with_percent' => 39,
    ],
    'currency' => 40,
    'currencies' => [
        'eur' => 41
    ],
    'island' => 42,
    'islands' => [
        'Mahé' => [
            'id' => 43,
            'priority' => 0,
            'districts' => [
                'Anse aux Pins' => 165,
                'Anse Boileau' => 166,
                'Anse Etoile' => 167,
                'Au Cap' => 168,
                'Anse Royale' => 169,
                'Baie Lazare' => 170,
                'Beau Vallon' => 171,
                'Bel Air' => 172,
                'Bel Ombre' => 173,
                'Cascade' => 174,
                'Glacis' => 175,
                'Grand\'Anse' => 176,
                'English River' => 177,
                'Mont Buxton' => 178,
                'Mont Fleuri' => 179,
                'Plaisance' => 180,
                'Pointe La Rue' => 181,
                'Port Glaud' => 182,
                'Saint Louis' => 183,
                'Takamaka' => 184,
                'Les Mamelles' => 185,
                'Roche Caïman' => 186
            ]
        ],
        'La Digue' => [
            'id' => 44,
            'priority' => 2,
            'districts' => [
                'La Digue and Inner Islands' => 189
            ]
        ],
        'Praslin' => [
            'id' => 45,
            'priority' => 1,
            'districts' => [
                'Baie Sainte Anne' => 187,
                'Grand\'Anse Praslin' => 188
            ]
        ],
        'Cerf' => [
            'id' => 46,
            'priority' => 3,
            'districts' => [
                'Mont Fleuri' => 190
            ]
        ],
        'St. Anne' => [
            'id' => 47,
            'priority' => 4,
            'districts' => [
                'Mont Fleuri' => 191
            ]
        ]
    ],
    'name' => 48,
    'names' => [
        'device_name' => 49,
        'price_name' => 140
    ],
    'device_description' => 112,
    'device_descriptions' => [
        'short_description' => 113,
        'long_description' => 114
    ],
    'device_meta' => 50,
    'device_metas' => [
        'default_availability' => 51, // deprecated, can be reused
        'size_from' => 109,
        'size_to' => 110,
        'number' => 111
    ],
    'device_classification' => 115,
    'device_properties' => [
        'category' => 546,
        'categories' => [
            'settings' => [
                'id' => 497,
                'name' => 'Settings',
                'icon' => 'settings',
                'is_listable' => false,
                'items' => [
                    'strict_child_bed_policy' => [
                        'id' => 498,
                        'is_listable' => false,
                        'name' => 'Strict child bed policy',
                        'elements' => [
                            'enabled' => 500,
                            'disabled' => 501
                        ]
                    ]
                ],
                'metas' => [
                    'channel_manager_id' => [
                        'id' => 544,
                        'name' => 'Channel manager ID'
                    ],
                ]
            ],
            'view' => [
                'id' => 215,
                'name' => 'View',
                'icon' => 'weather-sunset'
            ],
            'beds' => [
                'id' => 216,
                'name' => 'Beds',
                'icon' => 'hotel'
            ],
            'relax' => [
                'id' => 217,
                'name' => 'Relax',
                'icon' => 'pine-tree',
                'items' => [
                    'private_pool' => [
                        'id' => 218,
                        'name' => 'private pool'
                    ]
                ]
            ],
            'food_and_drink' => [
                'id' => 219,
                'name' => 'Food & Drink',
                'icon' => 'bowl',
                'items' => [
                    'coffee_tea_maker' => [
                        'id' => 220,
                        'name' => 'coffee/tea maker'
                    ],
                    'minibar' => [
                        'id' => 221,
                        'name' => 'minibar'
                    ],
                    '24_hour_room_service' => [
                        'id' => 222,
                        'name' => '24-hour room service'
                    ],
                    'free_bottled_water' => [
                        'id' => 223,
                        'name' => 'free bottled water'
                    ]
                ]
            ],
            'dining_facilities' => [
                'id' => 235,
                'name' => 'Dining facilities',
                'icon' => 'food-fork-drink',
                'items' => [
                    'air-conditioned' => [
                        'id' => 236,
                        'name' => 'air-conditioned'
                    ],
                    'dining_area' => [
                        'id' => 237,
                        'name' => 'dining area'
                    ],
                    'seating_for_two' => [
                        'id' => 238,
                        'name' => 'seating for two'
                    ]
                ]
            ],
            'kitchen_facilities' => [
                'id' => 239,
                'name' => 'Kitchen facilities',
                'icon' => 'stove',
                'items' => [
                    'own_kitchen_corner' => [
                        'id' => 240,
                        'name' => 'own kitchen corner'
                    ],
                    '4_ring_stove' => [
                        'id' => 241,
                        'name' => '4 ring stove'
                    ],
                    'oven' => [
                        'id' => 242,
                        'name' => 'oven'
                    ],
                    'microwave' => [
                        'id' => 243,
                        'name' => 'microwave'
                    ],
                    'dishwasher' => [
                        'id' => 244,
                        'name' => 'dishwasher'
                    ],
                    'fridge' => [
                        'id' => 245,
                        'name' => 'fridge'
                    ],
                    'deep_freezer' => [
                        'id' => 246,
                        'name' => 'deep freezer'
                    ],
                    'toaster' => [
                        'id' => 247,
                        'name' => 'toaster'
                    ]
                ]
            ],
            'room_service' => [
                'id' => 248,
                'name' => 'Room service',
                'icon' => 'bell'
            ]
        ]
    ],
    'organization_meta' => 120,
    'organization_classification' => 52,
    'organization_properties' => [
        'category' => 156,
        'categories' => [
            'facilities' => [
                'id' => 199,
                'name' => 'Facilities',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Felszereltség'
                ],
                'items' => [
                    'pool_and_wellness' => [
                        'id' => 123,
                        'name' => 'Pool and wellness',
                        'elements' => [
                            'Beachfront' => 131,
                            'Fitness centre' => 136,
                            'Hammam' => 134,
                            'Hot spring bath' => 138,
                            'Hot tub' => 135,
                            'Indoor pool' => 124,
                            'Indoor pool (all year)' => 126,
                            'Indoor pool (seasonal)' => 125,
                            'Massage' => 139,
                            'Outdoor pool' => 127,
                            'Outdoor pool (all year)' => 129,
                            'Outdoor pool (seasonal)' => 128,
                            'Private beach area' => 130,
                            'Sauna' => 133,
                            'Solarium' => 137,
                            'Spa and wellness centre' => 132
                        ],
                        'is_searchable' => true
                    ],
                    'wireless_internet' => [
                        'id' => 316,
                        'name' => 'Wireless Internet',
                        'icon' => 'wifi',
                        'is_searchable' => true
                    ]
                ]
            ],
            'amenities' => [
                'id' => 200,
                'name' => 'Amenities',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Szolgáltatások'
                ]
            ],
            'checkinout' => [
                'id' => 201,
                'name' => 'Check In/Out',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Be- és kijelentkezés'
                ],
                'items' => [
                    'check_in' => [
                        'id' => 231,
                        'name' => 'Check In',
                        'elements' => [],
                        'priority' => 0
                    ],
                    'check_out' => [
                        'id' => 232,
                        'name' => 'Check Out',
                        'elements' => [],
                        'priority' => 1
                    ],
                    'early_check_in' => [
                        'id' => 233,
                        'name' => 'Early Check In',
                        'elements' => [],
                        'priority' => 2
                    ],
                    'late_check_out' => [
                        'id' => 234,
                        'name' => 'Late Check Out',
                        'elements' => [],
                        'priority' => 3
                    ]
                ]
            ],
            'policies' => [
                'id' => 202,
                'name' => 'Policies',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Szabályok'
                ]
            ],
            'conditions' => [
                'id' => 362,
                'name' => 'Conditions',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Feltételek'
                ],
                'metas' => [
                    'free_cancellation_in_days_before_checkin' => [
                        'id' => 363,
                        'name' => 'Free cancellation (in days before check-in)',
                        'priority' => 0
                    ],
                    'special_condition_for_price_block' => [
                        'id' => 562,
                        'name' => 'Special condition for price block',
                        'priority' => 1
                    ]
                ]
            ],
            'general' => [
                'id' => 203,
                'name' => 'General',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Általános'
                ],
                'items' => [
                    'accommodation_category' => [
                        'id' => 141,
                        'name' => 'Accommodation Category',
                        'is_required' => false,
                        'elements' => [
                            //'hotel' => 142,
                            //'guest house' => 144
                            'Hotel' => 225,
                            'Luxury Hotel' => 226,
                            'Apartment' => 227,
                            'Villa' => 228,
                            'Guest House' => 229,
                            'Private Room' => 230,
                            'Resort' => 143
                        ],
                        'is_searchable' => true
                    ]
                ],
                'metas' => [
                    'number_of_rooms' => [
                        'id' => 121,
                        'name' => 'Number of rooms'
                    ],
                    'built_in' => [
                        'id' => 122,
                        'name' => 'Built in'
                    ],
                    'renovation_year' => [
                        'id' => 145,
                        'name' => 'Renovation year'
                    ],
                    'distance_from_beach' => [
                        'id' => 561,
                        'name' => 'Distance from beach'
                    ]
                ]
            ],
            'contact' => [
                'id' => 204,
                'name' => 'Contact',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Elérhetőségek'
                ]
            ],
            'settings' => [
                'id' => 205,
                'name' => 'Settings',
                'is_listable' => false,
                'translations' => [
                    'hu' => 'Beállítások'
                ],
                'items' => [
                    'availability_mode' => [
                        'id' => 53,
                        'name' => 'Availability mode',
                        'elements' => [
                            'binary' => 54,
                            'exact' => 55
                        ]
                    ],
                    'stars' => [
                        'id' => 146,
                        'name' => 'Stars',
                        'elements' => [
                            "★" => 147,
                            "★★" => 148,
                            "★★★" => 149,
                            "★★★★" => 150,
                            "★★★★★" => 151
                        ],
                        'is_searchable' => true
                    ],
                    'price_level' => [
                        'id' => 158,
                        'name' => 'Price level',
                        'elements' => [
                            '$' => 159,
                            '$$' => 160,
                            '$$$' => 161,
                            '$$$$' => 162,
                            '$$$$$' => 163
                        ]
                    ],
                    'discount_calculations_base' => [
                        'id' => 319,
                        'name' => 'Discount calculations base',
                        'is_required' => true,
                        'elements' => [
                            'net prices' => 320,
                            'rack prices' => 321
                        ]
                    ],
                    'merged_free_nights' => [
                        'id' => 334,
                        'name' => 'Merged free nights',
                        'is_required' => true,
                        'elements' => [
                            'enabled' => 343,
                            'disabled' => 344
                        ]
                    ],
                    'strict_child_bed_policy' => [
                        'id' => 499,
                        'name' => 'Strict child bed policy',
                        'elements' => [
                            'enabled' => 502,
                            'disabled' => 503
                        ]
                    ],
                    'channel_manager' => [
                        'id' => 541,
                        'name' => 'Channel manager',
                        'elements' => [
                            'Hotel Link Solutions' => 542
                        ],
                    ]
                ],
                'metas' => [
                    'channel_manager_id' => [
                        'id' => 543,
                        'name' => 'Channel manager ID'
                    ],
                    'hotel_authentication_channel_key' => [
                        'id' => 545,
                        'name' => 'HLS HotelAuthenticationChannelKey'
                    ]
                ]
            ]
        ],
        'item' => 157,
    ],
    'organization_group_meta' => 376,
    'organization_group_classification' => 377,
    'organization_group_properties' => [
        'category' => 378,
        'categories' => [
            'general' => [
                'id' => 379,
                'name' => 'General',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Általános'
                ],
                'items' => [
                    'ship_group_category' => [
                        'id' => 380,
                        'name' => 'Ship Group Category',
                        'is_required' => true,
                        'elements' => [
                            'Catamaran' => 381,
                            'Monohull' => 382
                        ],
                        'is_searchable' => true
                    ],
                    'propulsion' => [
                        'id' => 470,
                        'name' => 'Propulsion',
                        'is_required' => true,
                        'elements' => [
                            'Motorboat' => 471,
                            'Sailing boat' => 472
                        ],
                        'is_searchable' => true
                    ]
                ],
                'metas' => [
                    'length' => [
                        'id' => 473,
                        'name' => 'Length',
                        'is_required' => true,
                        'is_searchable' => true
                    ],
                    'capacity' => [
                        'id' => 474,
                        'name' => 'Capacity',
                        'is_required' => true
                    ]
                ]
            ],
            'settings' => [
                'id' => 383,
                'name' => 'Settings',
                'is_listable' => false,
                'translations' => [
                    'hu' => 'Beállítások'
                ],
                'items' => [
                    'availability_mode' => [
                        'id' => 384,
                        'name' => 'Availability mode',
                        'elements' => [
                            'binary' => 385,
                            'exact' => 386
                        ]
                    ],
                    'price_level' => [
                        'id' => 387,
                        'name' => 'Price level',
                        'elements' => [
                            '$' => 388,
                            '$$' => 389,
                            '$$$' => 390,
                            '$$$$' => 391,
                            '$$$$$' => 392
                        ]
                    ]
                ]
            ],
            'options' => [
                'id' => 486,
                'name' => 'Options',
                'is_listable' => false,
                'translations' => [
                    'hu' => 'Lehetőségek'
                ],
                'items' => [],
                'metas' => [
                    'skipper_a' => [
                        'id' => 487,
                        'name' => 'Skipper in Period A',
                        'type' => 'int'
                    ],
                    'skipper_cook_bcd' => [
                        'id' => 488,
                        'name' => 'Skipper/Cook (1 person) in Period B, C, D',
                        'type' => 'int'
                    ],
                    'starter_pack' => [
                        'id' => 489,
                        'name' => 'Starter pack (bottle of rum, bottle of whiskey, 6-pack of Bud beer, 10 can of beans and 10 box of Puffin marmalade)',
                        'type' => 'int'
                    ]
                ]
            ],
        ],
        'item' => 399,
    ],
    'margin_type' => 56,
    'margin_types' => [
        'percentage' => 57,
        'value' => 58
    ],
    'product_type' => 59,
    'product_types' => [
        'accommodation' => 60,
        'price_modified_accommodation' => 299,
        'group_fee' => 372,
        'personal_fee' => 373,
    ],
    'date_range_type' => 61,
    'date_range_types' => [
        'open' => 62,
        'closed' => 63,
        'price_modifier' => 164
    ],
    'pricing_logic' => 64,
    'pricing_logics' => [
        'from_net_price' => 65,
        'from_rack_price' => 66
    ],
    'price_modifier_application_level' => 67,
    'price_modifier_application_levels' => [
        'room_request' => [
            'id' => 206,
            'price_modifier_condition_types' => [
                'long_stay' => [
                    'id' => 68,
                    'classification' => 77,
                    'classifications' => [
                        //'booking_dates_should_be_contained' => 75
                    ],
                    'meta' => 78,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 504,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 69,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 455,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 262,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 76,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ]
                    ]
                ],
                'early_bird' => [
                    'id' => 97,
                    'classification' => 98,
                    'classifications' => [
                        //'booking_dates_should_be_contained' => 311
                    ],
                    'meta' => 99,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 505,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 100,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 456,
                            'type' => 'int'
                        ],
                        'booking_prior_minimum_days' => [
                            'id' => 101,
                            'type' => 'int'
                        ],
                        'booking_prior_maximum_days' => [
                            'id' => 102,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 263,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 318,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ]
                    ]
                ],
                'early_bird_fixed_date' => [
                    'id' => 291,
                    'classification' => 292,
                    'classifications' => [
                    ],
                    'meta' => 293,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 506,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 294,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 457,
                            'type' => 'int'
                        ],
                        'booking_date_from' => [
                            'id' => 295,
                            'type' => 'date'
                        ],
                        'booking_date_to' => [
                            'id' => 296,
                            'type' => 'date'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 297,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 317,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ]
                    ]
                ],
                'anniversary' => [
                    'id' => 79,
                    'classification' => 80,
                    'classifications' => [
                        'only_once' => 152,
                        'anniversary_in_the_same_month_as_travel' => 276,
                        'anniversary_in_the_same_year_as_travel' => 322,
                        'wedding/anniversary_during_travel' => 281
                    ],
                    'meta' => 81,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 507,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 249,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 458,
                            'type' => 'int'
                        ],
                        'wedding_in_less_than_days' => [
                            'id' => 105,
                            'type' => 'int'
                        ],
                        'wedding_in_less_than_months' => [
                            'id' => 323,
                            'type' => 'int'
                        ],
                        'anniversary_in_range_days' => [
                            'id' => 106,
                            'type' => 'int'
                        ],
                        'anniversary_in_range_months' => [
                            'id' => 324,
                            'type' => 'int'
                        ],
                        'anniversary_year_period' => [
                            'id' => 107,
                            'type' => 'int'
                        ],
                        'anniversary_year_start_from' => [
                            'id' => 312,
                            'type' => 'int'
                        ],
                        'nth_room' => [
                            'id' => 282,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 264,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 309,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ],
                        'booking_prior_minimum_days' => [
                            'id' => 325,
                            'type' => 'int'
                        ],
                        'booking_prior_maximum_days' => [
                            'id' => 326,
                            'type' => 'int'
                        ],
                    ]
                ],
                'minimum_nights' => [
                    'id' => 272,
                    'classification' => 273,
                    'classifications' => [],
                    'meta' => 274,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 508,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 275,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 459,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 331,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 310,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ]
                    ]
                ],
                'room_sharing' => [
                    'id' => 283,
                    'classification' => 284,
                    'classifications' => [],
                    'meta' => 285,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 509,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 286,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 460,
                            'type' => 'int'
                        ],
                        'adult_headcount_minimum' => [
                            'id' => 287,
                            'type' => 'int'
                        ],
                        'adult_headcount_maximum' => [
                            'id' => 328,
                            'type' => 'int'
                        ],
                        'child_headcount_minimum' => [
                            'id' => 327,
                            'type' => 'int'
                        ],
                        'child_headcount_maximum' => [
                            'id' => 288,
                            'type' => 'int'
                        ],
                        'child_age_minimum' => [
                            'id' => 313,
                            'type' => 'int'
                        ],
                        'child_age_maximum' => [
                            'id' => 289,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 290,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 332,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ]
                    ]
                ],
                'returning_client' => [
                    'id' => 451,
                    'classification' => 452,
                    'classifications' => [],
                    'meta' => 453,
                    'metas' => []
                ]
            ]
        ],
        'full_request' => [
            'id' => 207,
            'price_modifier_condition_types' => [
                'child_room' => [
                    'id' => 209,
                    'classification' => 210,
                    'classifications' => [],
                    'meta' => 211,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 510,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        //'minimum_nights' => 250,
                        'minimum_nights' => [
                            'id' => 224,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 461,
                            'type' => 'int'
                        ],
                        'room_headcount_minimum' => [
                            'id' => 315,
                            'type' => 'int'
                        ],
                        'room_headcount_maximum' => [
                            'id' => 212,
                            'type' => 'int'
                        ],
                        'room_age_minimum' => [
                            'id' => 314,
                            'type' => 'int'
                        ],
                        'room_age_maximum' => [
                            'id' => 213,
                            'type' => 'int'
                        ],
                        'nth_child_room' => [
                            'id' => 214,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 265,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 333,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ]
                    ]
                ]
            ]
        ],
        'cart' => [
            'id' => 208,
            'price_modifier_condition_types' => [
                'long_stay_in_chain' => [
                    'id' => 267,
                    'classification' => 268,
                    'classifications' => [],
                    'meta' => 269,
                    'metas' => [
                        'minimum_nights_in_chain' => [
                            'id' => 270,
                            'type' => 'int'
                        ],
                        'minimum_nights_in_accommodation' => [
                            'id' => 329,
                            'type' => 'int'
                        ],
                        'participating_organization_ids' => [
                            'id' => 271,
                            'type' => 'int',
                            'relation' => 'App\Relations\OrganizationsRelation'
                        ]
                    ]
                ],
                'family_room_combo' => [
                    'id' => 345,
                    'classification' => 346,
                    'classifications' => [],
                    'meta' => 347,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 511,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 348,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 462,
                            'type' => 'int'
                        ],
                        'restricted_to_meal_plan_ids' => [
                            'id' => 349,
                            'type' => 'int',
                            'relation' => 'App\Relations\MealPlansRelation'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 350,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ],
                        'adult_age_minimum' => [
                            'id' => 351,
                            'type' => 'int'
                        ],
                        'child_age_minimum' => [
                            'id' => 352,
                            'type' => 'int'
                        ],
                        'child_age_maximum' => [
                            'id' => 353,
                            'type' => 'int'
                        ],
                    ]
                ],
                'suite_reservation' => [
                    'id' => 354,
                    'classification' => 355,
                    'classifications' => [],
                    'meta' => 356,
                    'metas' => [
                        'minimum_nights_checking_level' => [
                            'id' => 512,
                            'type' => 'int',
                            'relation' => 'App\Relations\MinimumNightsCheckingLevelRelation'
                        ],
                        'minimum_nights' => [
                            'id' => 357,
                            'type' => 'int'
                        ],
                        'maximum_nights' => [
                            'id' => 463,
                            'type' => 'int'
                        ],
                        'restricted_to_device_ids' => [
                            'id' => 358,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesRelation'
                        ],
                        'suite_component_rooms' => [
                            'id' => 359,
                            'type' => 'string',
                            'relation' => 'App\Relations\DevicesJSONRelation'
                        ]
                    ]
                ],
                'group_price_modifier' => [
                    'id' => 446,
                    'classification' => 447,
                    'classifications' => [],
                    'meta' => 448,
                    'metas' => [
                        'group_headcount_minimum' => [
                            'id' => 449,
                            'type' => 'int',
                            'is_required' => false
                        ],
                        'group_headcount_maximum' => [
                            'id' => 450,
                            'type' => 'int'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'price_modifier_offer' => 82,
    'price_modifier_offers' => [
        'percentage' => [
            'id' => 83,
            'classification' => 87,
            'classifications' => [
                'use_mandatory_logic_for_deduction_base_prices' => 494,
                'do_not_apply_previous_price_modifiers' => 517
            ],
            'meta' => 88,
            'metas' => [
                'modifier_percentage' => [
                    'id' => 95,
                    'type' => 'int'
                ],
                'deduction_base_meal_plan_id' => [
                    'id' => 96,
                    'type' => 'int',
                    'relation' => 'App\Relations\MealPlanRelation'
                ],
                'deduction_base_prices' => [
                    'id' => 103,
                    'type' => 'string',
                    'relation' => 'App\Relations\PriceTaxonomyRelation'
                ]
            ]
        ],
        'free_nights' => [
            'id' => 84,
            'classification' => 89,
            'classifications' => [
                'use_last_consecutive_night' => 72,
                'use_mandatory_logic_for_deduction_base_prices' => 495
            ],
            'meta' => 90,
            'metas' => [
                'discounted_nights' => [
                    'id' => 70,
                    'type' => 'int'
                ],
                'cumulation_frequency' => [
                    'id' => 73,
                    'type' => 'int'
                ],
                'cumulation_maximum' => [
                    'id' => 74,
                    'type' => 'int'
                ],
                'deduction_base_meal_plan_id' => [
                    'id' => 71,
                    'type' => 'int',
                    'relation' => 'App\Relations\MealPlanRelation'
                ],
                'deduction_base_prices' => [
                    'id' => 104,
                    'type' => 'string',
                    'relation' => 'App\Relations\PriceTaxonomyRelation'
                ]
            ]
        ],
        'textual' => [
            'id' => 85,
            'classification' => 91,
            'classifications' => [],
            'meta' => 92,
            'metas' => []
        ],
        'price_row' => [
            'id' => 86,
            'classification' => 93,
            'classifications' => [
                'use_mandatory_logic_for_deduction_base_prices' => 496
            ],
            'meta' => 94,
            'metas' => [
                'recalculate_using_meal_plan' => [
                    'id' => 266,
                    'type' => 'int',
                    'relation' => 'App\Relations\MealPlanRelation'
                ],
                'recalculate_using_products' => [
                    'id' => 298,
                    'type' => 'int',
                    'relation' => 'App\Relations\DiscountedAccommodationProductsRelation'
                ],
                'deduction_base_prices' => [
                    'id' => 330,
                    'type' => 'string',
                    'relation' => 'App\Relations\PriceTaxonomyRelation'
                ]
            ]
        ],
        'fixed_price' => [
            'id' => 277,
            'classification' => 278,
            'classifications' => [],
            'meta' => 279,
            'metas' => [
                'modifier_value' => [
                    'id' => 280,
                    'type' => 'string',
                    'relation' => 'App\Relations\AgeRangeRelation'
                ]
            ]
        ],
        'tiered_price' => [
            'id' => 464,
            'classification' => 465,
            'classifications' => [],
            'meta' => 466,
            'metas' => [
                'fixed_value' => 467,
                'pax_value_from_headcount' => [
                    'id' => 468,
                    'type' => 'int'
                ],
                'pax_value' => 469
            ]
        ]
    ],
    'price_modifier_type' => 490,
    'price_modifier_types' => [
        'discount' => [
            'id' => 491,
            'name' => 'Discount (non-mandatory, visible)'
        ],
        'rule' => [
            'id' => 492,
            'name' => 'Rule (mandatory, visible)'
        ],
        'switch' => [
            'id' => 493,
            'name' => 'Switch (mandatory, invisible)'
        ]
    ],
    'charge' => 153,
    'charges' => [
        'surcharged' => 154,
        'free' => 155
    ],
    'file_type' => 192,
    'file_types' => [
        'image' => 193,
        'video' => 194,
        'document' => 198
    ],
    'gallery_role' => 195,
    'gallery_roles' => [
        'logo' => 196,
        'frontend_gallery' => 197
    ],
    'content_status' => 251,
    'content_statuses' => [
        'draft' => 252,
        'published' => 253
    ],
    'content_category' => 254,
    'content_categories' => [
        'category1' => [
            'id' => 255,
            'name' => 'Category1',
            'translations' => [
                'hu' => 'Kategória 1'
            ]
        ],
        'category2' => [
            'id' => 256,
            'name' => 'Category2',
            'translations' => [
                'hu' => 'Kategória 2'
            ]
        ],
        'category3' => [
            'id' => 257,
            'name' => 'Type of travellers',
            'translations' => [
                'hu' => 'Utazási típusok'
            ]
        ],
        'category4' => [
            'id' => 258,
            'name' => 'Type of holidays',
            'translations' => [
                'hu' => 'Üdülési típusok'
            ]
        ]
    ],
    'media_role' => 259,
    'media_roles' => [
        'lead_image' => 260,
        'content_image' => 261
    ],
    'relativetime_precision' => 335,
    'relativetime_precisions' => [
        'day' => [
            'id' => 336,
            'priority' => 1
        ],
        'time_of_day' => [
            'id' => 337,
            'priority' => 2
        ],
        'hour' => [
            'id' => 338,
            'priority' => 3
        ],
        'time' => [
            'id' => 339,
            'priority' => 4
        ]
    ],
    'relativetime_time_of_day' => 340,
    'relativetime_time_of_days' => [
        'AM' => [
            'id' => 341,
            'priority' => 1,
            'translations' => [
                'en' => 'AM',
                'de' => 'Morgen',
                'hu' => 'Délelőtt',
                'ru' => 'утро'
            ]
        ],
        'PM' => [
            'id' => 342,
            'priority' => 2,
            'translations' => [
                'en' => 'PM',
                'de' => 'Nachmittag',
                'hu' => 'Délután',
                'ru' => 'после полудня'
            ]
        ]
    ],
    'program_type' => 423,
    'program_types' => [
        'itinerary' => 424,
        'activity' => 425
    ],
    'program_description' => 360,
    'program_descriptions' => [
        'long_description' => 361
    ],
    'program_meta' => 364,
    'program_classification' => 365,
    'program_properties' => [
        'category' => 366,
        'categories' => [
            'general' => [
                'id' => 367,
                'name' => 'General',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Általános'
                ],
                'items' => [
                    'activity_level' => [
                        'id' => 368,
                        'name' => 'Activity level',
                        'is_required' => false,
                        'elements' => [
                            'Heavy' => 369,
                            'Medium' => 370,
                            'Low' => 371
                        ],
                        'is_searchable' => false
                    ]
                ],
            ]
        ]
    ],
    'cruise_description' => 404,
    'cruise_descriptions' => [
        'long_description' => 405
    ],
    'cruise_meta' => 406,
    'cruise_classification' => 407,
    'cruise_properties' => [
        'category' => 408,
        'categories' => [
            'general' => [
                'id' => 409,
                'name' => 'General',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Általános'
                ],
                'items' => []
            ],
            'settings' => [
                'id' => 410,
                'name' => 'Settings',
                'is_listable' => false,
                'translations' => [
                    'hu' => 'Beállítások'
                ],
                'items' => [
                    'price_level' => [
                        'id' => 411,
                        'name' => 'Price level',
                        'elements' => [
                            '$' => 412,
                            '$$' => 413,
                            '$$$' => 414,
                            '$$$$' => 415,
                            '$$$$$' => 416
                        ]
                    ],
                    'discount_calculations_base' => [
                        'id' => 417,
                        'name' => 'Discount calculations base',
                        'is_required' => true,
                        'elements' => [
                            'net prices' => 418,
                            'rack prices' => 419
                        ]
                    ],
                    'merged_free_nights' => [
                        'id' => 420,
                        'name' => 'Merged free nights',
                        'is_required' => true,
                        'elements' => [
                            'enabled' => 421,
                            'disabled' => 422
                        ]
                    ]
                ]
            ],
            'facilities' => [
                'id' => 483,
                'name' => 'Facilities',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Felszereltség'
                ]
            ],
            'conditions' => [
                'id' => 484,
                'name' => 'Conditions',
                'is_listable' => true,
                'translations' => [
                    'hu' => 'Feltételek'
                ],
                'metas' => [
                    'free_cancellation_in_days_before_checkin' => [
                        'id' => 485,
                        'name' => 'Free cancellation (in days before check-in)',
                        'priority' => 0
                    ]
                ]
            ]
        ]
    ],
    'schedule_frequency' => 434,
    'schedule_frequencies' => [
        'once' => 435,
        'weekly' => 436
    ],
    'embarkation_type' => 437,
    'embarkation_types' => [
        'none' => 438,
        'financial' => 439,
        'technical' => 440
    ],
    'embarkation_direction' => 441,
    'embarkation_directions' => [
        'none' => 442,
        'embark' => 443,
        'disembark' => 444,
        '2-way' => 445
    ],
    'poi_type' => 429,
    'poi_types' => [
        'port' => [
            'id' => 430,
            'name' => 'Port'
        ]
    ],
    'organization_group_poi_type' => 431,
    'organization_group_poi_types' => [
        'home_port' => [
            'id' => 432,
            'name' => 'Home Port'
        ]
    ],
    'predefined_filter' => 475,
    'predefined_filters' => [
        'ship_length' => [
            'id' => 476,
            'name' => 'Ship length',
            'translations' => [
                'hu' => 'Hajó hossza'
            ],
            'elements' => [
                '0,30' => [
                    'id' => 477,
                    'name' => '< 30 feet',
                    'translations' => [
                        'hu' => '< 30 láb'
                    ]
                ],
                '30,60' => [
                    'id' => 478,
                    'name' => '30 to 60 feet',
                    'translations' => [
                        'hu' => '30 - 60 láb'
                    ]
                ],
                '60,999' => [
                    'id' => 479,
                    'name' => '> 60 feet',
                    'translations' => [
                        'hu' => '> 60 láb'
                    ]
                ]
            ]
        ],
        'nights' => [
            'id' => 480,
            'name' => 'Nights',
            'translations' => [
                'hu' => 'Éjszakák'
            ],
            'elements' => [
                '6' => [
                    'id' => 481,
                    'name' => '6-night cruise',
                    'translations' => [
                        'hu' => '6 éjszakás hajóút'
                    ]
                ],
                '7' => [
                    'id' => 482,
                    'name' => '7-night cruise',
                    'translations' => [
                        'hu' => '7 éjszakás hajóút'
                    ]
                ]
            ]
        ]
    ],
    'minimum_nights_checking_level' => 513,
    'minimum_nights_checking_levels' => [
        'booking_dates_should_be_contained' => [
            'id' => 514,
            'name' => 'Booking dates should be contained'
        ],
        'minimum_nights_of_holiday' => [
            'id' => 515,
            'name' => 'Minimum nights of holiday'
        ],
        'minimum_nights_in_discount_period' => [
            'id' => 516,
            'name' => 'Minimum nights in discount period'
        ]
    ],
    'user_setting' => 518,
    'user_settings' => [
        'calendar_start_day' => [
            'id' => 519,
            'name' => 'Calendar start day',
            'items' => [
                'Mon' => 520,
                'Sat' => 521
            ]
        ]
    ],
    'order_status' => 524,
    'order_statuses' => [
        'new_order' => [
            'id' => 525,
            'name' => 'New order',
        ],
        'waiting_for_offer' => [
            'id' => 526,
            'name' => 'Waiting for offer',
        ],
        'offer_under_processing' => [
            'id' => 527,
            'name' => 'Offer under processing',
        ],
        'confirmed' => [
            'id' => 528,
            'name' => 'Confirmed',
        ],
        'closed' => [
            'id' => 529,
            'name' => 'Closed',
        ],
        'new_unique_product_order' => [
            'id' => 551,
            'name' => 'New unique product order'
        ],
        'paying' => [
            'id' => 530,
            'name' => 'Paying',
        ],
        'payment_success' => [
            'id' => 531,
            'name' => 'Payment success',
        ],
        'payment_failed' => [
            'id' => 532,
            'name' => 'Payment failed',
        ]
    ],
    'email_template' => 533,
    'email_templates' => [
        'user_offer_request_confirmation' => [
            'id' => 534,
            'name' => 'User / Offer request confirmation'
        ],
        'user_back_to_cart' => [
            'id' => 535,
            'name' => 'User / Back to cart'
        ],
        'user_back_to_unique_products_cart' => [
            'id' => 550,
            'name' => 'User / Back to unique products cart'
        ],
        'user_process_finished' => [
            'id' => 536,
            'name' => 'User / Process finished'
        ],
        'user_payment_success' => [
            'id' => 537,
            'name' => 'User / Payment success'
        ],
        'user_payment_failed' => [
            'id' => 539,
            'name' => 'User / Payment failed'
        ],
        'advisor_new_offer_received' => [
            'id' => 538,
            'name' => 'Advisor / New offer received'
        ],
        'admin_new_offer_received' => [
            'id' => 558,
            'name' => 'Admin / New offer received'
        ],
        'admin_payment_success' => [
            'id' => 559,
            'name' => 'Admin / Payment success'
        ],
        'admin_payment_failed' => [
            'id' => 560,
            'name' => 'Admin / Payment failed'
        ]
    ],
    'cart_status' => 547,
    'cart_statuses' => [
        'draft' => 548,
        'sent' => 549
    ],
    'order_type' => 552,
    'order_types' => [
        'normal' => 553,
        'uniqueproducts' => 554
    ],
    'billing_type' => 555,
    'billing_types' => [
        'individual' => 556,
        'company' => 557
    ]
];
