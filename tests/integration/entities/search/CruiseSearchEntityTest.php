<?php

namespace Tests\Integration\Entities\Search;

use App\Cruise;
use App\Entities\Search\CruiseSearchEntity;
use App\Exceptions\UserException;
use Tests\TestCase;

class CruiseSearchEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * @throws UserException
     */
    function it_can_set_parameters() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $actual = $cruiseSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]]);
        $this->assertInstanceOf(CruiseSearchEntity::class,$actual);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_non_numeric_amount() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $this->expectException(UserException::class);
        $cruiseSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 'fakeAmount']]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_zero_amount() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $this->expectException(UserException::class);
        $cruiseSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 0]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_negative_amount() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $this->expectException(UserException::class);
        $cruiseSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => -1]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_fractional_amount() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $this->expectException(UserException::class);
        $cruiseSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1.2]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_invalid_age_range() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $this->expectException(UserException::class);
        $cruiseSearchEntity->setParameters(['usages' => [['usage' => [['age' => 'fakeAgeRange', 'amount' => 1]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_validate_exception_if_date_from_not_set() {
        $this->expectException(UserException::class);
        $cruiseSearchEntity = new CruiseSearchEntity();
        $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_to' => '2016-06-28'
            ],
            'usages' => [
                    [
                    'usage' => [
                            ['age' => 21, 'amount' => 1]
                    ]
                ]
        ]])->getFrontendData();
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_validate_exception_if_date_to_not_set() {
        $this->expectException(UserException::class);
        $cruiseSearchEntity = new CruiseSearchEntity();
        $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2016-06-28'
            ],
            'usages' => [
                    [
                    'usage' => [
                            ['age' => 21, 'amount' => 1]
                    ]
                ]
        ]])->getFrontendData();
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_validate_exception_if_date_from_not_valid() {
        $this->expectException(UserException::class);
        $cruiseSearchEntity = new CruiseSearchEntity();
        $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2016-06-2b',
                'date_to' => '2016-06-28'
            ],
            'usages' => [
                    [
                    'usage' => [
                            ['age' => 21, 'amount' => 1]
                    ]
                ]
        ]])->getFrontendData();
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_validate_exception_from_date_is_bigger_than_to_date() {
        $this->expectException(UserException::class);
        $cruiseSearchEntity = new CruiseSearchEntity();
        $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2016-06-30',
                'date_to' => '2016-06-28'
            ],
            'usages' => [
                    [
                    'usage' => [
                            ['age' => 21, 'amount' => 1]
                    ]
                ]
        ]])->getFrontendData();
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_validate_exception_for_expired_date_from() {
        $this->expectException(UserException::class);
        $cruiseSearchEntity = new CruiseSearchEntity();
        $this->expectException(UserException::class);
        $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2016-01-01',
                'date_to' => '2016-06-28'
            ],
            'usages' => [
                    [
                    'usage' => [
                            ['age' => 21, 'amount' => 1]
                    ]
                ]
        ]])->getFrontendData();
    }


    /**
     * @test
     * @throws UserException
     * @throws \Throwable
     */
    function it_can_search_for_inactive_if_showInactive_parameter_set()
    {
        $expectedCount = Cruise::count();

        $frontendData = (new CruiseSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->getFrontendData();
        $this->assertCount($expectedCount, $frontendData);


        $shipGroup = Cruise::first();
        $shipGroup->is_active = false;
        $shipGroup->saveOrFail();

        $frontendData = (new CruiseSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->getFrontendData();
        $this->assertCount($expectedCount - 1, $frontendData);


        $frontendData = (new CruiseSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->setShowInactive(true)
            ->getFrontendData();
        $this->assertCount($expectedCount, $frontendData);
    }

}