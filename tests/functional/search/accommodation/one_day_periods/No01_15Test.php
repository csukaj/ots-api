<?php

namespace Tests\Functional\Search\Accommodation\OneDayPeriods;

use Tests\TestCase;

class No01_15Test extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval)
    {
        return $this->prepareAccommodationSearchResult($interval, 'Hotel Of One Day Periods',
            [['usage' => [['age' => 21, 'amount' => 2]]]]);
    }

    private function commonTests(float $originalPrice, float $priceModifierValue, array $frontendData)
    {
        $actual = $frontendData['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Standard Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals($originalPrice, $actual['original_price']);
        $this->assertEquals(
            ['value' => $priceModifierValue, 'percentage' => round($priceModifierValue / $originalPrice * 100,2)],
            $actual['total_discount']
        );
        $this->assertEquals($originalPrice - $priceModifierValue, $actual['discounted_price']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function No01Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-01-01', 'date_to' => '2026-01-10']);
        $this->commonTests(720, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No02Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-01-09', 'date_to' => '2026-01-11']);
        $this->commonTests(160, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No03Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-01-09', 'date_to' => '2026-01-13']);
        $this->commonTests(320, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No04Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-01-11', 'date_to' => '2026-01-13']);
        $this->commonTests(160, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No05Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-01-01', 'date_to' => '2026-01-20']);
        $this->commonTests(1520, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No06Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-01', 'date_to' => '2026-02-28']);
        $this->commonTests(2160, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No07Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-01', 'date_to' => '2026-02-11']);
        $this->commonTests(800, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No08Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-01', 'date_to' => '2026-02-12']);
        $this->commonTests(880, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No09Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-01', 'date_to' => '2026-02-13']);
        $this->commonTests(960, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No10Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-10', 'date_to' => '2026-02-13']);
        $this->commonTests(240, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No11Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-11', 'date_to' => '2026-02-13']);
        $this->commonTests(160, 0, $frontendData);;
    }

    /**
     * @test
     */
    public function No12Test()
    {
        $frontendData = $this->prepare(['date_from' => '2026-02-12', 'date_to' => '2026-02-16']);
        $this->commonTests(320, 0, $frontendData);
    }

    /**
     * @test
     */
    public function No13Test()
    {
        $frontendData = $this->prepare(['date_from' => '2027-01-01', 'date_to' => '2027-01-12']);
        $this->commonTests(880, 88, $frontendData);
    }

    /**
     * @test
     */
    public function No14Test()
    {
        $frontendData = $this->prepare(['date_from' => '2027-02-11', 'date_to' => '2027-02-28']);
        $this->commonTests(1360, 168, $frontendData); //1xpercentage (1day period)+ 1x free night
    }

    /**
     * @test
     */
    public function No15Test()
    {
        $frontendData = $this->prepare(['date_from' => '2027-03-20', 'date_to' => '2027-03-28']);
        $this->commonTests(640, 48, $frontendData);//1xpercentage (1day period)+ 1x free night
    }

}
