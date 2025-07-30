<?php

namespace Tests\Procedure\Ots\Pricing;

use Tests\Procedure\ProcedureTestCase;

class PriceModifierCalculator extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.pricing.price_modifier_calculator import PriceModifierCalculator' . PHP_EOL . 
    'from json import dumps' . PHP_EOL .
    'from ots.repository.model.price_row_collection_model import PriceRowCollectionModel' . PHP_EOL ;

    private function prepareAndRun($script)
    {
        $config = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'subject_type' => 'App\\\\Device',
            'subject_id' => $this->scriptContainer('factory.device("App\\Organization", 1)'),
            'productable_type' => 'App\\\\Device',
            'productable_id' => $this->scriptContainer('factory.device("App\\Organization", 1)'),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => 1,
            'order_itemable_index' => 0,
            'combination_wrapper' => $this->scriptContainer('None'),
            'settings' => $this->scriptContainer(json_encode([
                'discount_calculations_base' => 'rack prices',
                'merged_free_nights' => 'enabled'
            ])),
            'abstract_search' => $this->scriptContainer('factory.room_search()'),
            'date_ranges' => $this->scriptContainer('factory.date_ranges(1)'),
            'meal_plans' => $this->scriptContainer('factory.meal_plans("App\\Organization", 1)')
        ]);
        return $this->runPythonScript(self::$imports . "price_modifier_calculator = PriceModifierCalculator({$config})" . PHP_EOL . $script);
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
    public function it_can_get_offer()
    {
        $params = $this->composeParams([
            1, // meal_plan_id
            $this->scriptContainer('PriceRowCollectionModel(price_rows={})'), // meal_offer
            $this->scriptContainer('factory.room_request()'), // room_request
        ]);
        $actual = $this->jsonDecode($this->prepareAndRun("print dumps(price_modifier_calculator.get_offer({$params}))"), true);
            
        unset($actual['switches']);
        unset($actual['meal_offer']);
        $expected = [
            'original_price' => 0,
            'meal_plan_id' => 1,
            'discounted_price' => 0,
            'order_itemable_index' => 0,
            'meal_plan' => null,
            'period' => [
                'date_from' => '',
                'date_to' => ''
            ],
            'discounts' => [],
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ]
                ]
            ],
            'has_merged_free_nights' => false,
            'margin' => 0
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_load_meal_plans()
    {
        $actual = $this->jsonDecode($this->prepareAndRun(
            "print dumps(price_modifier_calculator.meal_plans)" . PHP_EOL
        ), true);
        $this->assertEquals([2 => 'b/b', 3 => 'h/b'], $actual);
    }


    /**
     * @test
     */
    public function it_can_show_if_is_applicable()
    {
        $params = $this->composeParams([
            1, // meal_plan_id
            $this->scriptContainer('PriceRowCollectionModel(price_rows={})'), // meal_offer
            $this->scriptContainer('factory.room_request()'), // room_request
        ]);
        $actual = $this->jsonDecode($this->prepareAndRun(
            "price_modifier_calculator.get_offer({$params})" . PHP_EOL .
            "discount_applicable_rooms = [{'device_id': price_modifier_calculator.subject_id, 'usage_pairs': [price_modifier_calculator.order_itemable_index]}]" . PHP_EOL .
            "device_and_index_matches = price_modifier_calculator._is_applicable(discount_applicable_rooms)" . PHP_EOL .
            "discount_applicable_rooms = [{'device_id': price_modifier_calculator.subject_id, 'usage_pairs': [999]}]" . PHP_EOL .
            "device_matches_index_not = price_modifier_calculator._is_applicable(discount_applicable_rooms)" . PHP_EOL .
            "discount_applicable_rooms = [{'device_id': 0, 'usage_pairs': [price_modifier_calculator.order_itemable_index]}]" . PHP_EOL .
            "device_not_matches = price_modifier_calculator._is_applicable(discount_applicable_rooms)" . PHP_EOL .
            "print [device_and_index_matches, device_matches_index_not, device_not_matches]"
        ), true);
        $this->assertTrue($actual[0]);
        $this->assertFalse($actual[1]);
        $this->assertFalse($actual[2]);
    }

}
