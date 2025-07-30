<?php

namespace Tests\Procedure\Ots\Offer;

class FreeNightsOfferTest extends OfferTestCase
{
    protected $priceModifierName = 'Free Nights Offer';
    protected $organizationId = 1;
    
    private function prepareObjects($fromDate, $toDate, $device, $priceModifierJson) {
        $priceSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $this->scriptContainer("factory.datetime('$fromDate')"),
            'to_time' => $this->scriptContainer("factory.datetime('$toDate')"),
            'booking_time' => null,
            'wedding_time' => null,
            'remove_request' => $this->scriptContainer('False'),
            'settings' => $this->scriptContainer(json_encode([
                'discount_calculations_base' => 'rack prices',
                'merged_free_nights' => 'enabled'
            ])),
            'abstract_search' => $this->scriptContainer('room_search'),
        ]);
        $findConfig = $this->composeParams([
            0,
            $this->scriptContainer("{'usage': [{'age': 21, 'amount': 1}]}"),
            $device->id,
            null,
            $this->scriptContainer("factory.open_date_ranges({$this->organizationId})")
        ]);
        $priceModifierConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'request' => $this->scriptContainer("{'usage': [{'age': 21, 'amount': 1}]}"),
            'properties' => $this->scriptContainer($priceModifierJson),
            'from_time' => $this->scriptContainer("factory.datetime('{$fromDate} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'to_time' => $this->scriptContainer("factory.datetime('{$toDate} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'request_from_time' => $this->scriptContainer("factory.datetime('{$fromDate} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'request_to_time' => $this->scriptContainer("factory.datetime('{$toDate} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'date_ranges' => $this->scriptContainer("factory.date_ranges({$this->organizationId})"),
            'price_modifiable_type' => 'App\\Organization',
            'price_modifiable_id' => $this->organizationId,
            'abstract_search' => $this->scriptContainer('room_search'),
            'available_devices' => $this->scriptContainer("factory.available_devices('App\\\\Organization', {$this->organizationId})"),
            'age_resolver' => $this->scriptContainer("factory.age_resolver('App\\\\Organization', {$this->organizationId})")
        ]);
        $roomSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $this->organizationId,
            'params'=>json_encode([
                'request' => json_decode('[{"usage": [{"age": 21, "amount": 1}]}]'),
                'interval'=>[
                    'date_from' => $fromDate,
                    'date_to' => $toDate
                ],
                'booking_date' => null,
                'wedding_date' => null
             ])
        ]);
        $script = $this->imports . <<<"EOF"
room_search = RoomSearch({$roomSearchConfig})
devices = plpy_mocker.execute("""SELECT * FROM "devices" WHERE "deviceable_id" = {$this->organizationId} AND deleted_at IS NULL""")
for device in devices:
    room_search.room_matcher.devices_data[device['id']] = device   
search = RoomPriceSearch({$priceSearchConfig})
result = search.find({$findConfig})
discount = PriceModifier({$priceModifierConfig})
offer = discount.offer.__class__
meal_plan_idx = 0 if result['prices'][0]['meal_plan_id'] == 2 else 1
EOF;
        return $script . PHP_EOL;
    }

    /**
     * @test
     */
    function it_can_get_last_price() {
        $fromDate = '2027-07-05';
        $toDate = '2027-07-10';
        $bookingDate = '2027-01-01';
        $request = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 1]
                ]
            ]
        ];

        list(, , $organization ) = $this->prepareTests();
        $resultRaw = $this->getRooms($organization->id, $request, $fromDate, $toDate, $bookingDate);
        $result = \json_decode($resultRaw,true);
        $actual = $result['results'][0][0]['prices'][1]['discounts'][0]['discount_value'];
        $this->assertEquals(-113.3, $actual);
    }

    /**
     * @test
     */
    function it_can_get_nights() {
        $fromDate = '2027-07-05';
        $toDate = '2027-07-10';

        list(, $priceModifierJson, , $device, ) = $this->prepareTests();
        $offerConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $this->scriptContainer("factory.datetime('$fromDate')"),
            'to_time' => $this->scriptContainer("factory.datetime('$toDate')"),
            'date_ranges' => $this->scriptContainer("factory.date_ranges({$this->organizationId})"),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => $this->organizationId,
            'price_modifier' => $this->scriptContainer('discount'),
            'room_offer' => $this->scriptContainer('result["prices"][0]'),
            'device_id' => $device->id,
            'abstract_search' => $this->scriptContainer('room_search'),
            'available_devices' => $this->scriptContainer("factory.available_devices('App\\\\Organization', {$this->organizationId})"),
            'age_resolver' => $this->scriptContainer("factory.age_resolver('App\\\\Organization', {$this->organizationId})")
        ]);
        $script = $this->prepareObjects($fromDate, $toDate, $device, $priceModifierJson);
        $script .= "print(offer({$offerConfig}).get_nights())";
        $actual = $this->runPythonScript($script);
        $this->assertEquals(5, $actual);
    }

    /**
     * @test
     */
    function it_can_get_modified_nights() {
        $fromDate = '2027-07-05';
        $toDate = '2027-07-10';

        list(, $priceModifierJson, , $device, ) = $this->prepareTests();
        $offerConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $this->scriptContainer("factory.datetime('$fromDate')"),
            'to_time' => $this->scriptContainer("factory.datetime('$toDate')"),
            'date_ranges' => $this->scriptContainer("factory.date_ranges({$this->organizationId})"),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => $this->organizationId,
            'price_modifier' => $this->scriptContainer('discount'),
            'room_offer' => $this->scriptContainer('result["prices"][0]'),
            'device_id' => $device->id,
            'abstract_search' => $this->scriptContainer('room_search'),
            'available_devices' => $this->scriptContainer("factory.available_devices('App\\\\Organization', {$this->organizationId})"),
            'age_resolver' => $this->scriptContainer("factory.age_resolver('App\\\\Organization', {$this->organizationId})")
        ]);
        $script = $this->prepareObjects($fromDate, $toDate, $device, $priceModifierJson);
        $script .= "print(offer({$offerConfig}).get_modified_nights())";
        $actual = $this->runPythonScript($script);
        $this->assertEquals(1, $actual);
    }

    /**
     * @test
     */
    function it_can_calculate_price() {
        $fromDate = '2027-07-05';
        $toDate = '2027-07-10';
        $bookingDate = '2027-01-01';
        $request = [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ];

        list(, , $organization) = $this->prepareTests();
        $result = $this->jsonDecode($this->getRooms($organization->id, $request, $fromDate, $toDate, $bookingDate), true);
        $actual = $result['results'][0][0]['prices'][1]['discounts'][0]['discount_value'];
        $this->assertEquals(-113.3, $actual);
    }
}