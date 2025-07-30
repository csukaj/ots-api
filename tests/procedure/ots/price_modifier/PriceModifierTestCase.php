<?php

namespace Tests\Procedure\Ots\PriceModifier;

use App\PriceModifier;
use App\Organization;
use Tests\Procedure\ProcedureTestCase;

class PriceModifierTestCase extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static protected $imports = 'from datetime import datetime' . PHP_EOL .
    'from ots.price_modifier.price_modifier import PriceModifier' . PHP_EOL .
    'from ots.search.room_search import RoomSearch' . PHP_EOL .
    'from ots.pricing.room_price_search import RoomPriceSearch' . PHP_EOL;
    protected $priceModifierName = 'Free Nights Offer';
    protected $organizationId = 1;
    protected $models;
    protected $weddingDate = null;
    protected $bookingDate = '2027-06-01';
    protected $dateFrom = '2027-06-01';
    protected $dateTo = '2027-06-10';
    protected $invalidDateFrom = '2026-07-05';
    protected $invalidDateTo = '2026-07-10';
    protected $meta = ['minimum_nights' => '2', 'minimum_nights_checking_level' => '515'];
    protected $classification = [];
    protected $offer = 'FreeNightsOffer';
    protected $application_type = 'room_request';

    protected function prepareTests()
    {
        if (is_null($this->models)) {
            $priceModifier = $this->getExclusivePriceModifier($this->priceModifierName, $this->organizationId);

            $priceModifierAttributes = $priceModifier->getAttributes();
            $priceModifierAttributes['id'] = $priceModifier->id;
            array_walk($priceModifierAttributes, function (&$item, $key) {
                $item = is_null($item) ? 'None' : $item;
            });
            $priceModifierJson = json_encode($priceModifierAttributes);

            $organization = Organization::findOrFail(1);
            $device = $organization->devices[0];
            $deviceUsage = $device->usages[0];

            $this->models = [$priceModifier, $priceModifierJson, $organization, $device, $deviceUsage];
        }
        return $this->models;
    }

    protected function getExclusivePriceModifier($priceModifierName, $organizationId)
    {
        return PriceModifier::findByName($priceModifierName, Organization::class, $organizationId);
    }

    protected function setupExclusivePriceModifierPythonString($priceModifierName, $organizationId)
    {
        $priceModifier = $this->getExclusivePriceModifier($priceModifierName, $organizationId);
        return "plpy_mocker.cursor.execute('UPDATE price_modifiers SET deleted_at= NOW() WHERE id != " . $priceModifier->id . "')" . PHP_EOL;
    }

    protected function prepareRoomSearch(
        $organizationId = 1,
        $request = [],
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null
    ) {
        $roomSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'params' => json_encode([
                'request' => $request,
                'interval' => [
                    'date_from' => $fromDate ?: $this->dateFrom,
                    'date_to' => $toDate ?: $this->dateTo
                ],
                'booking_date' => $bookingDate ?: $this->bookingDate,
                'wedding_date' => $weddingDate ?: $this->weddingDate
            ])
        ]);
        return self::$imports . PHP_EOL . "room_search = RoomSearch({$roomSearchConfig})" . PHP_EOL;
    }

    protected function preparePriceModifier($config = [])
    {
        list(, , $organization, ,) = $this->prepareTests();
        $defaults = [
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'request' => $this->scriptContainer('factory.request()'),
            'properties' => $this->scriptContainer('factory.price_modifier_properties()'),
            'from_time' => $this->scriptContainer("datetime.strptime('{$this->dateFrom} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'to_time' => $this->scriptContainer("datetime.strptime('{$this->dateTo} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'date_ranges' => $this->scriptContainer("factory.date_ranges({$organization->id})"),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => $organization->id,
            'abstract_search' => $this->scriptContainer('room_search'),
            'request_from_time' => $this->scriptContainer("datetime.strptime('{$this->dateFrom} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'request_to_time' => $this->scriptContainer("datetime.strptime('{$this->dateTo} 12:00:00', '%Y-%m-%d %H:%M:%S')"),
            'available_devices' => $this->scriptContainer("factory.available_devices('App\\\\Organization', {$organization->id})")
        ];
        $params = $this->composeKeywordArguments(array_merge($defaults, $config));

        $script = $this->prepareRoomSearch($organization->id, [['usage' => [['age' => 21, 'amount' => 1]]]],
            $this->dateFrom, $this->dateTo);
        $script .= "discount = PriceModifier({$params})" . PHP_EOL;
        return $script;
    }

    protected function prepareAndRun($config = [], $script = '')
    {
        return $this->runPythonScript(
            self::$imports .
            $this->setupExclusivePriceModifierPythonString($this->priceModifierName, $this->organizationId) .
            $this->preparePriceModifier($config) . $script . PHP_EOL .
            'plpy_mocker.connection.rollback()' . PHP_EOL
        );
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $this->assertEquals('', $this->prepareAndRun());
    }

    /**
     * @test
     */
    public function it_can_load_classification()
    {
        $script = 'discount._load_classification()' . PHP_EOL;
        $script .= 'print dumps(discount.classification)' . PHP_EOL;
        $this->assertEquals(json_encode($this->classification), $this->prepareAndRun([], $script));
    }

    /**
     * @test
     */
    public function it_can_load_meta()
    {
        $script = 'discount._load_meta()' . PHP_EOL;
        $script .= 'print dumps(discount.meta)' . PHP_EOL;
        $this->assertEquals($this->meta, json_decode($this->prepareAndRun([], $script), true));
    }

    /**
     * @test
     */
    public function it_can_load_offer()
    {
        $script = 'discount._load_offer()' . PHP_EOL;
        $script .= 'print discount.offer.__class__.__name__' . PHP_EOL;
        $this->assertEquals($this->offer, $this->prepareAndRun([], $script));
    }

    /**
     * @test
     */
    public function it_can_get_name()
    {
        $script = 'print discount.get_name()' . PHP_EOL;
        $this->assertEquals(['en' => $this->priceModifierName],
            $this->jsonDecode($this->prepareAndRun([], $script), true));
    }

    /**
     * @test
     */
    public function it_can_get_description()
    {
        $script = 'print discount.get_description()' . PHP_EOL;
        $this->assertNull($this->jsonDecode($this->prepareAndRun([], $script), true));
    }

    /**
     * @test
     */
    public function it_can_get_application_type()
    {
        $script = 'print discount.get_application_type()' . PHP_EOL;
        $this->assertEquals($this->application_type, $this->prepareAndRun([], $script));
    }

    /**
     * @test
     */
    public function it_can_calculate()
    {
        $script = "print discount.calculate(factory.room_offer(), 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id'], 'App\\\\Device', factory.device('App\\Organization', {$this->organizationId})['id']).price" . PHP_EOL;
        $this->assertTrue(is_numeric($this->prepareAndRun([], $script)));
    }
}
