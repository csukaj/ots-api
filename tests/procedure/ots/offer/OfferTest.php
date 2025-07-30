<?php

namespace Tests\Procedure\Ots\Offer;

use App\Device;
use Tests\Procedure\ProcedureTestCase;

class OfferTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.offer.offer import Offer' . PHP_EOL .
    'from ots.pricing.room_price_search import RoomPriceSearch' . PHP_EOL;


    private function prepareAndRun($script)
    {
        $organization_id = 1;
        $device = Device::where('deviceable_id', $organization_id)->orderBy('id')->firstOrFail();
        $device_id = $device->id;
        $from = '2027-06-01';
        $to = '2027-06-10';
        $priceSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $this->scriptContainer("factory.datetime('{$from}')"),
            'to_time' => $this->scriptContainer("factory.datetime('{$to}')"),
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
            $device_id,
            null,
            $this->scriptContainer("factory.open_date_ranges({$organization_id})")
        ]);
        $config = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $this->scriptContainer("factory.datetime('{$from}')"),
            'to_time' => $this->scriptContainer("factory.datetime('{$to}')"),
            'date_ranges' => $this->scriptContainer('factory.date_ranges(1)'),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => $organization_id,
            'price_modifier' => $this->scriptContainer('factory.price_modifier(abstract_search=room_search)'),
            'room_offer' => $this->scriptContainer('result["prices"][0]'),
            'device_id' => $device_id,
            'abstract_search' => $this->scriptContainer('room_search'),
            'available_devices' => $this->scriptContainer("factory.available_devices('App\\\\Organization', {$organization_id})"),
            'age_resolver' => $this->scriptContainer("factory.age_resolver('App\\\\Organization', {$organization_id})")
        ]);

        $scripthead = self::$imports . <<<"EOF"
room_search = factory.room_search()
search = RoomPriceSearch({$priceSearchConfig})
result = search.find({$findConfig})
offer = Offer({$config})
EOF;

        return $this->runPythonScript($scripthead . PHP_EOL . $script);
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $this->assertEquals('', $this->prepareAndRun(''));
    }

    /**
     * @test
     */
    public function it_can_load_classification()
    {
        $this->assertEquals("['use_last_consecutive_night']",
            $this->prepareAndRun('' . PHP_EOL . 'print offer.get_classification()'));
    }

    /**
     * @test
     */
    public function it_can_load_meta()
    {
        $this->assertEquals(['discounted_nights'=> 1],
            $this->jsonDecode($this->prepareAndRun('' . PHP_EOL . 'print offer.get_meta()'),true));
    }

    /**
     * @test
     */
    public function it_can_get_nights()
    {
        //TODO: find a better configuration to get reasonable expected value
        $this->assertEquals('9.0', $this->prepareAndRun('print offer.get_nights()'));
    }

    /**
     * @test
     */
    public function it_can_get_max_from()
    {
        $this->assertEquals('2027-06-01 00:00:00', $this->prepareAndRun('print offer.get_max_from()'));
    }

    /**
     * @test
     */
    public function it_can_get_min_to()
    {
        $this->assertEquals('2027-06-10 00:00:00', $this->prepareAndRun('print offer.get_min_to()'));
    }

    /**
     * @test
     */
    public function it_can_calculate()
    {
        $this->assertEquals('None', $this->prepareAndRun("print offer.calculate(1, 'App\\\\Device', 2, 'App\\\\Device', 2, False)"));
    }
}