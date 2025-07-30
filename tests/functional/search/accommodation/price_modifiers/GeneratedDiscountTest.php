<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

/**
 * Class NewNoDiscountsTest
 * @see https://docs.google.com/spreadsheets/d/1N3G3oyaqNeMeaipDyHD6r8DBlGIobd6jA_jIwfoJpr0/edit
 */
class GeneratedDiscountTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    public $testData = null;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->testData = json_decode(file_get_contents(__DIR__ . '/data/GeneratedDiscountTest.json'), true);
    }

    private function prepare($interval, $name = 'Hotel A', $rooms = [], $wedding = null)
    {
        return $this->prepareAccommodationSearchResult($interval, $name, $rooms, $wedding, '2017-11-01');
    }

    private function getTestData($id)
    {
        $data = $this->testData[$id];

        $interval = [
            'date_from' => str_replace('.', '-', $data['Tól']),
            'date_to' => str_replace('.', '-', $data['Ig'])
        ];
        $name = $data['Hotel'];
        $rooms = [];
        foreach (['Szoba 1', 'Szoba 2', 'Szoba 3'] as $room) {
            if (!empty($data[$room])) {
                $usages = [];
                if (preg_match('/(\d+)\s+felnőtt/', $data[$room], $m)) {
                    $usages[] = ['age' => 21, 'amount' => intval($m[1])];
                }
                if (preg_match('/(\d+)\s+gyerek/', $data[$room], $m)) {
                    for ($i = 0; $i < $m[1]; $i++) {
                        $usages[] = ['age' => 16, 'amount' => 1];
                    }
                }
                if (preg_match('/(\d+)\s+baba/', $data[$room], $m)) {
                    for ($i = 0; $i < $m[1]; $i++) {
                        $usages[] = ['age' => 1, 'amount' => 1];
                    }
                }
                $rooms[] = ['usage' => $usages];
            }
        }

        $testdata = ['interval' => $interval, 'hotel' => $name, 'usages' => $rooms];
        $testdata['wedding_date'] = (!empty($data['anniversary'])) ? str_replace('.', '-', $data['anniversary']) : null;

        $expected = [
            'is_result_empty' => ('N/A' == $data['Ár/éj/szoba']),
            'original_price' => $data['Ár összesen'],
            'discounted_price' => $data['Ár kedvezményesen összesen'],
            'discounts' => [
                ['name' => ['en' => $data['Kedvezmény']]]
            ],
            'total_discount' => [
                'percentage' => $data['Ár kedvezmény %'],
            ],
            'meal_plan' => strtolower(substr($data['Ellátás (legkedvezőbb)'], 0,
                    1) . '/' . substr($data['Ellátás (legkedvezőbb)'], 1, 1))
        ];


        return ['testdata' => $testdata, 'expected' => $expected];
    }

    public function commonTests($testSet)
    {
        $data = $this->getTestData($testSet);
        $testdata = $data['testdata'];
        $expected = $data['expected'];

        $actual = $this->prepare($testdata['interval'], $testdata['hotel'], $testdata['usages'],
            $testdata['wedding_date']);

        if ($expected['is_result_empty']) {
            $this->assertEmpty($actual);
            return;
        }

        $this->assertNotEmpty($actual);
        $this->assertEquals($expected['original_price'], $actual['best_price']['original_price']);
        $this->assertEquals($expected['discounted_price'], $actual['best_price']['discounted_price']);
        $this->assertEquals($expected['total_discount']['percentage'],
            $actual['best_price']['total_discount']['percentage']);
        $this->assertEquals($expected['meal_plan'], $actual['best_price']['meal_plan']);
        foreach ($actual['results'][0][0]['prices'] as $price) {
            if ($price['meal_plan'] == $expected['meal_plan']) {
                $actualPrice = $price;
            }
        }
        $this->assertNotEmpty($actualPrice);
        $this->assertNotEmpty($actualPrice['discounts']);
        $this->assertEquals($expected['discounts'][0]['name']['en'], $actualPrice['discounts'][0]['name']['en']);
    }

    /**
     * @test
     */
    public function No01Test()
    {
        $this->commonTests(1);
    }

    /**
     * @test
     */
    public function No02Test()
    {
        $this->commonTests(2);
    }

    /**
     * @test
     */
    public function No03Test()
    {
        $this->commonTests(3);
    }

    /**
     * @test
     */
    public function No04Test()
    {
        $this->commonTests(4);
    }

    /**
     * @test
     */
    public function No05Test()
    {
        $this->commonTests(5);
    }

    /**
     * @test
     */
    public function No06Test()
    {
        $this->commonTests(6);
    }

    /**
     * @test
     */
    public function No07Test()
    {
        $this->commonTests(7);
    }

    /**
     * @test
     */
    public function No08Test()
    {
        $this->commonTests(8);
    }

    /**
     * @test
     */
    public function No09Test()
    {
        $this->commonTests(9);
    }

    /**
     * @test
     */
    public function No10Test()
    {
        $this->commonTests(10);
    }

    /**
     * @test
     */
    public function No11Test()
    {
        $this->commonTests(11);
    }

    /**
     * @test
     */
    public function No12Test()
    {
        $this->commonTests(12);
    }

    /**
     * @test
     */
    public function No13Test()
    {
        $this->commonTests(13);
    }

    /**
     * @test
     */
    public function No14Test()
    {
        $this->commonTests(14);
    }

    /**
     * @test
     */
    public function No15Test()
    {
        $this->commonTests(15);
    }

    /**
     * @test
     */
    public function No16Test()
    {
        $this->commonTests(16);
    }

    /**
     * @test
     */
    public function No17Test()
    {
        $this->commonTests(17);
    }

    /**
     * @test
     */
    public function No18Test()
    {
        $this->commonTests(18);
    }

    /**
     * @test
     */
    public function No23Test()
    {
        $this->commonTests(23);
    }

    /**
     * @test
     */
    public function No29Test()
    {
        $this->commonTests(29);
    }

    /**
     * @test
     */
    public function No31Test()
    {
        $this->commonTests(31);
    }

}
