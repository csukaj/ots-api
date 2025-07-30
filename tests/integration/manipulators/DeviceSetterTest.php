<?php

namespace Tests\Integration\Manipulators;

use App\AgeRange;
use App\Device;
use App\DeviceUsage;
use App\DeviceUsageElement;
use App\Entities\DeviceEntity;
use App\Exceptions\UserException;
use App\Manipulators\DeviceSetter;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeviceSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_create_device() {
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create1'
            ]
        ];

        $devSetter = new DeviceSetter($data);
        $devObj = $devSetter->set();
        $this->assertInstanceOf(Device::class, $devObj);
        $this->assertEquals(Config::get('taxonomies.devices.room'), $devObj->type_taxonomy_id);
        $this->assertEquals($data['name']['en'], $devObj->name->name);
        $this->assertEquals($data['amount'], $devObj->amount);
    }

    /**
     * @test
     */
    function it_can_update_device() {
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create2'
            ]
        ];

        $devSetter = new DeviceSetter($data);
        $devObj = $devSetter->set();
        $this->assertInstanceOf(Device::class, $devObj);
        $this->assertEquals(Config::get('taxonomies.devices.room'), $devObj->type_taxonomy_id);
        $this->assertEquals($data['name']['en'], $devObj->name->name);
        $this->assertEquals($data['amount'], $devObj->amount);

        $update = [
            'id' => $devObj->id,
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 32,
            'name' => [
                'en' => 'Hotel Test Update'
            ]
        ];

        $devSetter = new DeviceSetter($update);
        $devUpObj = $devSetter->set();
        $this->assertInstanceOf(Device::class, $devObj);
        $this->assertEquals($devObj->id, $devUpObj->id);
        $this->assertEquals(Config::get('taxonomies.devices.room'), $devUpObj->type_taxonomy_id);
        $this->assertEquals($update['name']['en'], $devUpObj->name->name);
        $this->assertEquals($update['amount'], $devUpObj->amount);
    }

    /**
     * @test
     */
    function it_can_set_short_description() {
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create7'
            ],
            'short_description' => [
                'en' => 'Lorem ipsum'
            ]
        ];

        $devSetter = new DeviceSetter($data);
        $devObj = $devSetter->set();
        $this->assertInstanceOf(Device::class, $devObj);
        $devEntity = new DeviceEntity($devObj);
        $frontend = $devEntity->getFrontendData(['descriptions']);
        $this->assertEquals($data['short_description'], $frontend['descriptions']['short_description']);
    }

    /**
     * @test
     */
    function it_can_set_long_description_too() {
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create8'
            ],
            'short_description' => [
                'en' => 'Lorem ipsum'
            ],
            'long_description' => [
                'en' => 'Lorem ipsum long'
            ]
        ];

        $devSetter = new DeviceSetter($data);
        $devObj = $devSetter->set();
        $this->assertInstanceOf(Device::class, $devObj);
        $devEntity = new DeviceEntity($devObj);
        $frontend = $devEntity->getFrontendData(['descriptions']);
        $this->assertEquals($data['short_description'], $frontend['descriptions']['short_description']);
        $this->assertEquals($data['long_description'], $frontend['descriptions']['long_description']);
    }

    /**
     * @test
     */
    function it_can_delete_descriptions()
    {
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create8'
            ],
            'short_description' => [
                'en' => 'Lorem ipsum'
            ],
            'long_description' => [
                'en' => 'Lorem ipsum long'
            ]
        ];

        $device = (new DeviceSetter($data))->set();
        $this->assertInstanceOf(Device::class, $device);

        $data['id'] = $device->id;
        $data['short_description'] = null;
        $data['long_description'] = null;

        $deviceUpd = (new DeviceSetter($data))->set();
        $this->assertInstanceOf(Device::class, $deviceUpd);
        $frontend = (new DeviceEntity($deviceUpd))->getFrontendData(['descriptions']);
        $this->assertEmpty($frontend['descriptions']);
    }


    /**
     * @test
     */
    function it_can_set_usages() {
        $AdultAgeRangeId = AgeRange::getAgeRangeId(Organization::class, 1, 'adult');
        $babyAgeRangeId = AgeRange::getAgeRangeId(Organization::class, 1, 'baby');
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create9'
            ],
            'usages' => [
                [
                    "name" => [],
                    "elements" => [
                        [
                            "amount" => 2,
                            "age_range" => [
                                "id" => $AdultAgeRangeId,
                                "age_rangeable_id" => 1,
                                "from_age" => 5,
                                "to_age" => null,
                                "name_taxonomy" => "adult"
                            ]
                        ]
                    ],
                ],
                [
                    "name" => [],
                    "elements" => [
                        [
                            "amount" => 2,
                            "age_range" => [
                                "id" => $AdultAgeRangeId,
                                "age_rangeable_id" => 1,
                                "from_age" => 5,
                                "to_age" => null,
                                "name_taxonomy" => "adult"
                            ]
                        ],
                        [
                            "amount" => 1,
                            "age_range" => [
                                "id" => $babyAgeRangeId,
                                "age_rangeable_id" => 1,
                                "from_age" => 0,
                                "to_age" => 4,
                                "name_taxonomy" => "baby"
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $devSetter = new DeviceSetter($data);
        $devObj = $devSetter->set();
        $this->assertInstanceOf(Device::class, $devObj);
        $frontend = (new DeviceEntity($devObj))->getFrontendData(['usages']);
        $this->assertCount(count($data['usages']), $frontend['usages']);
    }

    /**
     * @test
     */
    function it_cannot_set_device_with_already_used_name() {
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create X'
            ]
        ];

        $devSetter = new DeviceSetter($data);
        $devObj = $devSetter->set();
        $this->expectException(UserException::class);
        $devSetter2 = new DeviceSetter($data);
        $devObj2 = $devSetter2->set();
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_delete_cascade_some_usages() {
        $AdultAgeRangeId = AgeRange::getAgeRangeId(Organization::class, 1, 'adult');
        $babyAgeRangeId = AgeRange::getAgeRangeId(Organization::class, 1, 'baby');
        $data = [
            'deviceable_id' => 1,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Hotel Test Create9'
            ],
            'usages' => [
                [
                    "name" => [],
                    "elements" => [
                        [
                            "amount" => 2,
                            "age_range" => [
                                "id" => $AdultAgeRangeId,
                                "age_rangeable_id" => 1,
                                "from_age" => 5,
                                "to_age" => null,
                                "name_taxonomy" => "adult"
                            ]
                        ]
                    ],
                ],
                [
                    "name" => [],
                    "elements" => [
                        [
                            "amount" => 2,
                            "age_range" => [
                                "id" => $AdultAgeRangeId,
                                "age_rangeable_id" => 1,
                                "from_age" => 5,
                                "to_age" => null,
                                "name_taxonomy" => "adult"
                            ]
                        ],
                        [
                            "amount" => 1,
                            "age_range" => [
                                "id" => $babyAgeRangeId,
                                "age_rangeable_id" => 1,
                                "from_age" => 0,
                                "to_age" => 4,
                                "name_taxonomy" => "baby"
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $device = (new DeviceSetter($data))->set();
        $this->assertInstanceOf(Device::class, $device);
        $frontend = (new DeviceEntity($device))->getFrontendData(['usages']);
        $this->assertCount(count($data['usages']), $frontend['usages']);

        $usageToDelete = $device->usages()->first();
        $elementToDelete = $usageToDelete->elements()->first();

        $data['id'] = $device->id;
        unset($data['usages'][0]);

        $device = (new DeviceSetter($data))->set();
        $this->assertInstanceOf(Device::class, $device);
        $frontend = (new DeviceEntity($device))->getFrontendData(['usages']);
        $this->assertCount(count($data['usages']), $frontend['usages']);
        $this->assertEmpty(DeviceUsage::find($usageToDelete->id));
        $this->assertNotEmpty(DeviceUsage::onlyTrashed()->find($usageToDelete->id));
        $this->assertNotEmpty(DeviceUsageElement::onlyTrashed()->find($elementToDelete->id));
    }

}
