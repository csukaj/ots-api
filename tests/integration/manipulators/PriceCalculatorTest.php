<?php

namespace Tests\Integration\Manipulators;

use App\Exceptions\UserException;
use App\Manipulators\PriceCalculator;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PriceCalculatorTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_NEVER;

    /**
     * Test values for percentage-based calculations:
     * [net price, margin percentage, rack price, expected margin value, expected rounded rack value for 7 nights]
     * @var array
     */
    protected $percentageTests = [
        [27, 34, 36.18, 9.18, 253],
        [53, 13, 59.89, 6.89, 419],
        [121, 17, 141.57, 20.57, 991],
        [121, -17, 100.43, -20.57, 703],
        [22500, 10, 24750, 2250, 173250],
        [37993, -9, 34573.63, -3419.37, 242015],
        [50244, 47, 73858.68, 23614.68, 517011],
        [142800, 13, 161364, 18564, 1129548],
        [178834, -21, 141278.86, -37555.14, 988952],
        [301468, -10, 271321.2, -30146.8, 1899248]
    ];
    
    /**
     * @test
     */
    function it_can_calculate_rack_price_from_net_price_and_margin_percentage() {
        $calc = new PriceCalculator();
        foreach ($this->percentageTests as $test) {
            $calc->initWithNetPrice($test[0], $test[1], Config::get('taxonomies.margin_types.percentage'));
            $this->assertEquals($test[2], $calc->getRackPrice());
            $this->assertEquals($test[3], $calc->getMarginValue());
            $this->assertEquals($test[4], $calc->getRoundedRackPrice(7));
        }
    }
    
    /**
     * @test
     */
    function it_can_calculate_net_price_from_rack_price_and_margin_percentage() {
        $calc = new PriceCalculator();
        foreach ($this->percentageTests as $test) {
            $calc->initWithRackPrice($test[2], $test[1], Config::get('taxonomies.margin_types.percentage'));
            $this->assertEquals($test[0], $calc->getNetPrice());
            $this->assertEquals($test[3], $calc->getMarginValue());
            $this->assertEquals($test[4], $calc->getRoundedRackPrice(7));
        }
    }
    
    /**
     * @test
     */
    function it_cannot_calculate_net_price_from_rack_price_less_then_minus_hundred_percent() {
        $this->expectException(UserException::class);
        $calc = new PriceCalculator();
        $calc->initWithRackPrice(100, -110, Config::get('taxonomies.margin_types.percentage'));
    }
    
    /**
     * Test values for value-based calculations:
     * [net price, margin value, rack price, expected margin percentage, expected rounded rack value for 7 nights]
     * @var array
     */
    protected $valueTests = [
        [35, 7, 42, 20, 294],
        [79, 6, 85, 7.59, 595],
        [121, 20, 141, 16.53, 987],
        [1223, -22, 1201, -1.8, 8407],
        [45056, -9035, 36021, -20.05, 252147],
        [76522, -16855, 59667, -22.03, 417669],
        [84125, 5875, 90000, 6.98, 630000],
        [146396, -21396, 125000, -14.62, 875000],
        [173795, -79595, 94200, -45.8, 659400],
        [678699, -193399, 485300, -28.5, 3397100]
    ];

    /**
     * @test
     */
    function it_can_calculate_rack_price_from_net_price_and_margin_value() {
        $calc = new PriceCalculator();
        foreach ($this->valueTests as $test) {
            $calc->initWithNetPrice($test[0], $test[1], Config::get('taxonomies.margin_types.value'));
            $this->assertEquals($test[2], $calc->getRackPrice());
            $this->assertEquals($test[3], $calc->getMarginPercentage());
            $this->assertEquals($test[4], $calc->getRoundedRackPrice(7));
        }
    }
    
    /**
     * @test
     */
    function it_can_calculate_net_price_from_rack_price_and_margin_value() {
        $calc = new PriceCalculator();
        foreach ($this->valueTests as $test) {
            $calc->initWithRackPrice($test[2], $test[1], Config::get('taxonomies.margin_types.value'));
            $this->assertEquals($test[0], $calc->getNetPrice());
            $this->assertEquals($test[3], $calc->getMarginPercentage());
            $this->assertEquals($test[4], $calc->getRoundedRackPrice(7));
        }
    }
    
    
}
