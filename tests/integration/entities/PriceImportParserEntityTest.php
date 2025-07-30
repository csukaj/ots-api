<?php

namespace Tests\Integration\Entities;

use App\Entities\PriceImportParserEntity;
use Tests\TestCase;

class PriceImportParserEntityTest extends TestCase
{
    /**
     * @test
     */
    function test_constructor_with_param() {
        $testFilePath = $this->getTestFile();
        $priceImportParserEntity = new PriceImportParserEntity($testFilePath);
        $this->assertInstanceOf('App\Entities\PriceImportParserEntity', $priceImportParserEntity);
        $this->assertEquals($this->getTestImportedArray(), $priceImportParserEntity->getImportedArray());
        $this->deleteTestFile($testFilePath);
    }

    /**
     * @test
     */
    function test_constructor_without_param() {
        $priceImportParserEntity = new PriceImportParserEntity();
        $this->assertInstanceOf('App\Entities\PriceImportParserEntity', $priceImportParserEntity);
        $this->assertEquals([], $priceImportParserEntity->getImportedArray());
    }

    /**
     * @test
     */
    function test_parseCsvToArray() {
        $testFilePath = $this->getTestFile();
        $arr = $this->getTestImportedArray();
        $importedArr = PriceImportParserEntity::parseCsvToArray($testFilePath);
        $this->assertEquals($arr, $importedArr);
        $this->deleteTestFile($testFilePath);
    }

    /**
     * @test
     */
    function test_parseDateRanges() {
        $arr = $this->getTestDateRangesArray();
        $dateRangesArr = PriceImportParserEntity::parseDateRanges($this->getTestImportedArray());
        $this->assertEquals($arr, $dateRangesArr);
    }

    /**
     * @test
     */
    function test_parseMealPlans() {
        $arr = $this->getTestMealPlansArray();
        $dateRangesArr = PriceImportParserEntity::parseMealPlans($this->getTestImportedArray());
        $this->assertEquals($arr, $dateRangesArr);
    }

    /**
     * @test
     */
    function  test_parsePriceIds() {
        $arr = $this->getTestPriceIdsArray();
        $priceIdsArr = PriceImportParserEntity::parsePriceIds($this->getTestImportedArray());
        $this->assertEquals($arr, $priceIdsArr);
    }

    /**
     * @test
     */
    function  test_parsePriceElements() {
        $arr = $this->getTestPriceElementsArray();
        $priceElementsArr = PriceImportParserEntity::parsePriceElements($this->getTestImportedArray());
        $this->assertEquals($arr, $priceElementsArr);
    }

    /**
     * @test
     */
    function test_getDateRangeId() {
        $dateRangesArr = PriceImportParserEntity::parseDateRanges($this->getTestImportedArray());
        $dateRangeId = PriceImportParserEntity::getDateRangeId($dateRangesArr);
        $this->assertEquals(13, $dateRangeId);
    }

    /**
     * @test
     */
    function test_setIsFromNetPrice() {
        $isFromNetPrice = PriceImportParserEntity::setIsFromNetPrice(13);
        $this->assertTrue($isFromNetPrice);
    }

    function test_setMarginTypeTaxonomyId() {
        $marginTypeTaxonomyId = PriceImportParserEntity::setMarginTypeTaxonomyId(13);
        $this->assertEquals(57, $marginTypeTaxonomyId);
    }

    /**
     * @test
     */
    function test_run() {
        $priceImport = new PriceImportParserEntity($this->getTestFile());
        $priceImport->run();
        $this->assertEquals($this->getTestDateRangesArray(), $priceImport->getDateRanges());
        $this->assertEquals($this->getTestMealPlansArray(), $priceImport->getMealPlans());
        $this->assertEquals($this->getTestPriceIdsArray(), $priceImport->getPriceIds());
        $this->assertEquals($this->getTestPriceElementsArray(), $priceImport->getPriceElements());
    }

    /**
     * @test
     */
    function test_createPriceList() {
        $path = $this->getTestFile();
        $priceImport = new PriceImportParserEntity($path);
        $priceImport->run()->createPriceList();
        $this->assertEquals($this->getTestPriceList(), $priceImport->getPriceList());
        $this->deleteTestFile($path);
    }

    private function getTestFile() {
        $path = base_path()."/tests/functional/controllers/admin/PriceImportControllerTestData/test-data.csv";
        if ( !is_file($path) ) {
            file_put_contents($path, $this->getTestCsvContent());
        }
        return $path;
    }

    private function deleteTestFile($path) {
        unlink($path);
    }

    private function getTestCsvContent() {
        return "Date Ranges ID;;;;#13|#13;#12|#12;#9|#9;|#9;#10|#10;|#10;#11|#11;|#11;#14|#14
Name;;;;;;;;;;;;
Date Ranges;;;;2027-03-15 - 2027-03-23;2027-03-24 - 2027-03-31;2027-04-01 - 2027-05-01;;2027-06-01 - 2027-09-01;;2027-09-02 - 2027-10-01;;2027-10-02 - 2027-11-01
;Device;Product;Price row;;;b/b;h/b;b/b;h/b;b/b;h/b;
#4;Single Room;10 nights or more;Single;;;80;90;0;;80;81;
#5;Single Room;10 nights or more;Extra Child;;;4.1;50;40;130;40;41;
#6;Single Room;10 nights or more;Extra Baby;;;30;11;;;10;111;
#11;Deluxe Room;10 nights or more;Single;;;9.1;90;100;;100;100;";
    }

    private function getTestImportedArray() {
        return array (
            0 =>
                array (
                    0 => 'Date Ranges ID',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '#13|#13',
                    5 => '#12|#12',
                    6 => '#9|#9',
                    7 => '|#9',
                    8 => '#10|#10',
                    9 => '|#10',
                    10 => '#11|#11',
                    11 => '|#11',
                    12 => '#14|#14',
                ),
            1 =>
                array (
                    0 => 'Name',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    7 => '',
                    8 => '',
                    9 => '',
                    10 => '',
                    11 => '',
                    12 => '',
                ),
            2 =>
                array (
                    0 => 'Date Ranges',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '2027-03-15 - 2027-03-23',
                    5 => '2027-03-24 - 2027-03-31',
                    6 => '2027-04-01 - 2027-05-01',
                    7 => '',
                    8 => '2027-06-01 - 2027-09-01',
                    9 => '',
                    10 => '2027-09-02 - 2027-10-01',
                    11 => '',
                    12 => '2027-10-02 - 2027-11-01',
                ),
            3 =>
                array (
                    0 => '',
                    1 => 'Device',
                    2 => 'Product',
                    3 => 'Price row',
                    4 => '',
                    5 => '',
                    6 => 'b/b',
                    7 => 'h/b',
                    8 => 'b/b',
                    9 => 'h/b',
                    10 => 'b/b',
                    11 => 'h/b',
                    12 => '',
                ),
            4 =>
                array (
                    0 => '#4',
                    1 => 'Single Room',
                    2 => '10 nights or more',
                    3 => 'Single',
                    4 => '',
                    5 => '',
                    6 => '80',
                    7 => '90',
                    8 => '0',
                    9 => '',
                    10 => '80',
                    11 => '81',
                    12 => '',
                ),
            5 =>
                array (
                    0 => '#5',
                    1 => 'Single Room',
                    2 => '10 nights or more',
                    3 => 'Extra Child',
                    4 => '',
                    5 => '',
                    6 => '4.1',
                    7 => '50',
                    8 => '40',
                    9 => '130',
                    10 => '40',
                    11 => '41',
                    12 => '',
                ),
            6 =>
                array (
                    0 => '#6',
                    1 => 'Single Room',
                    2 => '10 nights or more',
                    3 => 'Extra Baby',
                    4 => '',
                    5 => '',
                    6 => '30',
                    7 => '11',
                    8 => '',
                    9 => '',
                    10 => '10',
                    11 => '111',
                    12 => '',
                ),
            7 =>
                array (
                    0 => '#11',
                    1 => 'Deluxe Room',
                    2 => '10 nights or more',
                    3 => 'Single',
                    4 => '',
                    5 => '',
                    6 => '9.1',
                    7 => '90',
                    8 => '100',
                    9 => '',
                    10 => '100',
                    11 => '100',
                    12 => '',
                ),
        );
    }

    private function getTestDateRangesArray() {
        return array (
            13 => '2027-03-15 - 2027-03-23',
            12 => '2027-03-24 - 2027-03-31',
            9 => '2027-04-01 - 2027-05-01',
            10 => '2027-06-01 - 2027-09-01',
            11 => '2027-09-02 - 2027-10-01',
            14 => '2027-10-02 - 2027-11-01',
        );
    }

    private function getTestMealPlansArray() {
        return array (
            6 =>
                array (
                    'id' => '9',
                    'name' => 'b/b',
                ),
            7 =>
                array (
                    'id' => '9',
                    'name' => 'h/b',
                ),
            8 =>
                array (
                    'id' => '10',
                    'name' => 'b/b',
                ),
            9 =>
                array (
                    'id' => '10',
                    'name' => 'h/b',
                ),
            10 =>
                array (
                    'id' => '11',
                    'name' => 'b/b',
                ),
            11 =>
                array (
                    'id' => '11',
                    'name' => 'h/b',
                ),
        );
    }

    private function getTestPriceIdsArray() {
        return array (
            4 =>
                array (
                    'id' => '4',
                    'name' => 'Single',
                ),
            5 =>
                array (
                    'id' => '5',
                    'name' => 'Extra Child',
                ),
            6 =>
                array (
                    'id' => '6',
                    'name' => 'Extra Baby',
                ),
            7 =>
                array (
                    'id' => '11',
                    'name' => 'Single',
                ),
        );
    }

    private function getTestPriceElementsArray() {
        return array (
            4 =>
                array (
                    4 => '',
                    5 => '',
                    6 => '80',
                    7 => '90',
                    8 => '0',
                    9 => '',
                    10 => '80',
                    11 => '81',
                    12 => '',
                ),
            5 =>
                array (
                    4 => '',
                    5 => '',
                    6 => '4.1',
                    7 => '50',
                    8 => '40',
                    9 => '130',
                    10 => '40',
                    11 => '41',
                    12 => '',
                ),
            6 =>
                array (
                    4 => '',
                    5 => '',
                    6 => '30',
                    7 => '11',
                    8 => '',
                    9 => '',
                    10 => '10',
                    11 => '111',
                    12 => '',
                ),
            7 =>
                array (
                    4 => '',
                    5 => '',
                    6 => '9.1',
                    7 => '90',
                    8 => '100',
                    9 => '',
                    10 => '100',
                    11 => '100',
                    12 => '',
                ),
        );
    }

    private function getTestPriceList() {
        return array (
            0 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"4",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"14",
                    "net_price"=>"80",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            1 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"4",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"15",
                    "net_price"=>"90",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            2 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>true,
                    "price_id"=>"4",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"16",
                    "net_price"=>"0",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            3 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>false,
                    "price_id"=>"4",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"22",
                    "net_price"=>"",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            4 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"4",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"17",
                    "net_price"=>"80",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            5 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"4",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"18",
                    "net_price"=>"81",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            6 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"5",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"14",
                    "net_price"=>"4.1",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            7 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"5",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"15",
                    "net_price"=>"50",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            8 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>true,
                    "price_id"=>"5",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"16",
                    "net_price"=>"40",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            9 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>true,
                    "price_id"=>"5",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"22",
                    "net_price"=>"130",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            10 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"5",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>17,
                    "net_price"=>"40",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            11 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"5",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"18",
                    "net_price"=>"41",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            12 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"6",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"14",
                    "net_price"=>"30",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            13 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"6",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"15",
                    "net_price"=>"11",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            14 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>false,
                    "price_id"=>"6",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"16",
                    "net_price"=>"",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            15 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>false,
                    "price_id"=>"6",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"22",
                    "net_price"=>"",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            16 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"6",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"17",
                    "net_price"=>"10",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            17 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"6",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"18",
                    "net_price"=>"111",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            18 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"11",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"14",
                    "net_price"=>"9.1",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            19 =>
                array (
                    "date_range_id"=>"9",
                    "enabled"=>true,
                    "price_id"=>"11",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"15",
                    "net_price"=>"90",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            20 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>true,
                    "price_id"=>"11",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"16",
                    "net_price"=>"100",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            21 =>
                array (
                    "date_range_id"=>"10",
                    "enabled"=>false,
                    "price_id"=> "11",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"22",
                    "net_price"=>"",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            22 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"11",
                    "meal_plan"=>"b/b",
                    "model_meal_plan_id"=>"17",
                    "net_price"=>"100",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                ),
            23 =>
                array (
                    "date_range_id"=>"11",
                    "enabled"=>true,
                    "price_id"=>"11",
                    "meal_plan"=>"h/b",
                    "model_meal_plan_id"=>"18",
                    "net_price"=>"100",
                    "rack_price"=>null,
                    "margin_type_taxonomy_id"=>"57",
                )
        );
    }
}
