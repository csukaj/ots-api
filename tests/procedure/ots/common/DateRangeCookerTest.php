<?php

namespace Tests\Procedure\Ots\Common;

use Tests\Procedure\ProcedureTestCase;

class DateRangeCookerTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.common.date_range_cooker import DateRangeCooker' . PHP_EOL;

    const DATE_RANGE_TYPE_OPEN = 62;
    const DATE_RANGE_TYPE_CLOSED = 63;
    const DATE_RANGE_TYPE_PRICE_MODIFIER = 164;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepareDateRangeCooker($organizationId = 1, $fromDate = null, $toDate = null)
    {
        $config = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'date_rangeable_type' => 'App\\\\Organization',
            'date_rangeable_id' => $organizationId,
            'from_time' => $this->scriptContainer("factory.datetime('{$fromDate}')"),
            'to_time' => $this->scriptContainer("factory.datetime('{$toDate}')"),
            'date_ranges' => $this->scriptContainer('{' . self::DATE_RANGE_TYPE_OPEN . ": factory.open_date_ranges({$organizationId})}")
        ]);
        return self::$imports . PHP_EOL . "cooker = DateRangeCooker({$config})" . PHP_EOL;
    }

    private function isCovered($organizationId, $fromDate, $toDate)
    {
        $script = $this->prepareDateRangeCooker($organizationId, $fromDate, $toDate);
        $return = $this->runPythonAndDecodeJSON($script . 'print cooker.is_covered()', true, true);
        return $return;
    }

    /**
     * @test
     */
    public function it_can_check_coverage()
    {
        // full coverage
        $this->assertTrue($this->isCovered(1, '2026-06-01', '2026-06-04'));

        // under minimum nights
        $this->assertFalse($this->isCovered(1, '2026-06-01', '2026-06-03'));

        // partial coverage - it adds new date range between two existing ones
        $this->assertTrue($this->isCovered(1, '2026-05-30', '2026-06-04'));

        // partial coverage - it finds coverage from even many years back
        $this->assertTrue($this->isCovered(1, '2027-09-20', '2027-10-10'));

        // no coverage - it adds new date range to cover
        $this->assertTrue($this->isCovered(1, '2028-09-10', '2028-09-20'));

        // partial coverage - it finds coverage from different years
        $this->assertTrue($this->isCovered(1, '2028-09-20', '2028-10-10'));

        // no coverage - it finds coverage from even many years back
        $this->assertTrue($this->isCovered(1, '2028-10-20', '2028-10-30'));

        // full coverage - it finds coverage at from border
        $this->assertTrue($this->isCovered(2, '2028-06-01', '2028-06-10'));

        // full coverage - it finds coverage at to border
        $this->assertTrue($this->isCovered(2, '2028-08-30', '2028-09-02'));

        // partial coverage - it does not find coverage from even many years back
        $this->assertFalse($this->isCovered(2, '2028-08-30', '2028-09-03'));

        // no coverage - it does not find coverage from even many years back
        $this->assertFalse($this->isCovered(2, '2028-09-04', '2028-09-05'));
    }

    /**
     * @test
     */
    public function it_can_get_uncovered_nights()
    {
        // partial match - it adds new date range
        $script = $this->prepareDateRangeCooker(1, '2026-05-30', '2026-06-04');
        $actual = $this->runPythonAndDecodeJSON($script . 'print cooker._get_uncovered_nights()');
        $expected = 0;
        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function it_can_get_last_open_date_ranges()
    {
        $script = $this->prepareDateRangeCooker(1, '2026-05-30', '2026-06-04');
        $script .= "date_ranges = cooker._get_last_open_date_ranges(factory.datetime('2026-01-01'), factory.datetime('2026-12-31'))" . PHP_EOL;
        $script .= "print jsonpickle.encode(date_ranges, unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, true);

        $expected = [
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => 3,
                'date_rangeable_id' => 1,
                'type_taxonomy_id' => 62,
                'from_time' => '2015-01-01 00:00:00',
                'to_time' => '2015-08-01 23:59:59',
                'margin_value' => 3.0
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => 3,
                'date_rangeable_id' => 1,
                'type_taxonomy_id' => 62,
                'from_time' => '2026-06-01 00:00:00',
                'to_time' => '2026-09-01 23:59:59',
                'margin_value' => 3.0,
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => 3,
                'date_rangeable_id' => 1,
                'type_taxonomy_id' => 62,
                'from_time' => '2015-08-02 00:00:00',
                'to_time' => '2015-12-31 23:59:59',
                'margin_value' => 3.0,
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => 3,
                'date_rangeable_id' => 1,
                'type_taxonomy_id' => 62,
                'from_time' => '2026-09-02 00:00:00',
                'to_time' => '2026-10-01 23:59:59',
                'margin_value' => 3.0,
            ]
        ];
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals([], array_diff_assoc($expected[$i], $actual[$i][0]));
        }
    }

    /**
     * @test
     */
    public function it_can_get_last_open_date_ranges_on_year_leap()
    {
        $script = $this->prepareDateRangeCooker(19, '2026-12-24', '2027-01-04');
        $script .= "date_ranges = cooker._get_last_open_date_ranges(factory.datetime('2026-12-25'), factory.datetime('2027-01-04'))" . PHP_EOL;
        $script .= "print jsonpickle.encode(date_ranges, unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, true);
        $expected = [
            [
                'margin_type_taxonomy_id' => 58,
                'date_rangeable_type' => 'App\\Organization',
                'minimum_nights' => 3,
                'from_time' => '2026-12-24 00:00:00',
                'type_taxonomy_id' => 62,
                'to_time' => '2026-12-26 23:59:59',
                'margin_value' => 3.0,
                'date_rangeable_id' => 19,
            ],
            [
                'margin_type_taxonomy_id' => 58,
                'date_rangeable_type' => 'App\\Organization',
                'minimum_nights' => 3,
                'from_time' => '2026-12-27 00:00:00',
                'type_taxonomy_id' => 62,
                'to_time' => '2026-12-30 23:59:59',
                'margin_value' => 3.0,
                'date_rangeable_id' => 19,
            ],
            [
                'margin_type_taxonomy_id' => 58,
                'date_rangeable_type' => 'App\\Organization',
                'minimum_nights' => 3,
                'from_time' => '2026-12-31 00:00:00',
                'type_taxonomy_id' => 62,
                'to_time' => '2027-01-01 23:59:59',
                'margin_value' => 3.0,
                'date_rangeable_id' => 19,
            ]
        ];
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals([], array_diff_assoc($expected[$i], $actual[$i][0]));
        }
    }

    /**
     * @test
     */
    public function it_can_get_last_open_date_ranges_on_period_year_leap()
    {
        $script = $this->prepareDateRangeCooker(11, '2028-12-31', '2029-01-04');
        $script .= "date_ranges = cooker._get_last_open_date_ranges(factory.datetime('2028-12-31'), factory.datetime('2028-01-03'))" . PHP_EOL;
        $script .= "print jsonpickle.encode(date_ranges, unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, true);
        $expected = [
            [
                'margin_type_taxonomy_id' => 57,
                'name_description_id' => 204,
                'date_rangeable_type' => 'App\\Organization',
                'minimum_nights' => 3,
                'from_time' => '2027-12-31 00:00:00',
                'type_taxonomy_id' => 62,
                'to_time' => '2028-01-05 23:59:59',
                'deleted_at' => null,
                'margin_value' => 3.0,
                'id' => 60,
                'date_rangeable_id' => 11,
            ]
        ];
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            unset($actual[$i][0]['created_at'],$actual[$i][0]['updated_at']);
            $this->assertEquals($expected[$i], $actual[$i][0]);
        }
    }

    /**
     * @test
     */
    public function it_can_generate_holiday_nights()
    {
        // valid dates
        $script = $this->prepareDateRangeCooker(1, '2026-06-02', '2026-06-08');
        $actual = $this->runPythonAndDecodeJSON($script . 'print dumps(cooker._generate_holiday_nights())');
        $this->assertEquals(["2026-06-02", "2026-06-03", "2026-06-04", "2026-06-05", "2026-06-06", "2026-06-07"],
            $actual);

        // invalid dates
        $script = $this->prepareDateRangeCooker(1, '2026-06-08', '2026-06-02');
        $actual = $this->runPythonAndDecodeJSON($script . 'print dumps(cooker._generate_holiday_nights())');
        $this->assertEquals([], $actual);
    }

    /**
     * @test
     */
    public function it_can_check_open_nights()
    {
        // full match
        $script = $this->prepareDateRangeCooker(1, '2026-06-02', '2026-06-08');
        $script .= "holiday_nights = cooker._generate_holiday_nights()" . PHP_EOL;
        $script .= "print dumps(cooker._check_open_nights(holiday_nights))" . PHP_EOL;
        $this->assertEquals([], $this->runPythonAndDecodeJSON($script));

        // no match
        $script = $this->prepareDateRangeCooker(1, '2028-06-02', '2028-06-04');
        $script .= "holiday_nights = cooker._generate_holiday_nights()" . PHP_EOL;
        $script .= "print dumps(cooker._check_open_nights(holiday_nights))" . PHP_EOL;
        $this->assertEquals(['2028-06-02', '2028-06-03'], $this->runPythonAndDecodeJSON($script));

        // partial match
        $script = $this->prepareDateRangeCooker(1, '2026-05-30', '2026-06-04');
        $script .= "holiday_nights = cooker._generate_holiday_nights()" . PHP_EOL;
        $script .= "print dumps(cooker._check_open_nights(holiday_nights))" . PHP_EOL;
        $this->assertEquals(['2026-05-30', '2026-05-31'], $this->runPythonAndDecodeJSON($script));
    }

    /**
     * @test
     */
    public function it_can_create_date_range_from_day_list()
    {
        // valid dates
        $script = $this->prepareDateRangeCooker(1, '2026-05-30', '2026-06-04');
        $script .= "holiday_nights = cooker._generate_holiday_nights()" . PHP_EOL;
        $script .= "print jsonpickle.encode(cooker._create_date_range_from_day_list(holiday_nights), unpicklable=False)" . PHP_EOL;
        $this->assertEquals(['from_time' => '2026-05-30 00:00:00', 'to_time' => '2026-06-03 23:59:59',],
            $this->runPythonAndDecodeJSON($script));

        // invalid dates
        $script = $this->prepareDateRangeCooker(1, '2026-06-04', '2026-05-30');
        $script .= "holiday_nights = cooker._generate_holiday_nights()" . PHP_EOL;
        $script .= "print jsonpickle.encode(cooker._create_date_range_from_day_list(holiday_nights), unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $this->assertNull($actual);
    }

}