<?php

namespace Tests\Integration\Entities\Search;

use App\Entities\Search\CharterSearchEntity;
use App\Exceptions\UserException;
use App\ShipGroup;
use Tests\TestCase;

class CharterSearchEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * @throws UserException
     */
    function it_can_set_parameters()
    {
        $charterSearchEntity = new CharterSearchEntity();
        $actual = $charterSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]]);
        $this->assertInstanceOf(CharterSearchEntity::class,$actual);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_non_numeric_amount()
    {
        $charterSearchEntity = new CharterSearchEntity();
        $this->expectException(UserException::class);
        $charterSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 'fakeAmount']]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_zero_amount()
    {
        $charterSearchEntity = new CharterSearchEntity();
        $this->expectException(UserException::class);
        $charterSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 0]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_negative_amount()
    {
        $charterSearchEntity = new CharterSearchEntity();
        $this->expectException(UserException::class);
        $charterSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => -1]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_fractional_amount()
    {
        $charterSearchEntity = new CharterSearchEntity();
        $this->expectException(UserException::class);
        $charterSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1.2]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_invalid_age_range()
    {
        $charterSearchEntity = new CharterSearchEntity();
        $this->expectException(UserException::class);
        $charterSearchEntity->setParameters(['usages' => [['usage' => [['age' => 'fakeAgeRange', 'amount' => 1]]]]]);
    }

    /**
     * @test
     * @throws UserException
     * @throws \Exception
     */
    function it_throws_validate_exception_if_date_from_not_set()
    {
        $this->expectException(UserException::class);
        $charterSearchEntity = new CharterSearchEntity();
        $charterSearchEntity->setParameters([
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
     * @throws \Exception
     */
    function it_throws_validate_exception_if_date_to_not_set()
    {
        $this->expectException(UserException::class);
        $charterSearchEntity = new CharterSearchEntity();
        $charterSearchEntity->setParameters([
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
     * @throws \Exception
     */
    function it_throws_validate_exception_if_date_from_not_valid()
    {
        $this->expectException(UserException::class);
        $charterSearchEntity = new CharterSearchEntity();
        $charterSearchEntity->setParameters([
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
     * @throws \Exception
     */
    function it_throws_validate_exception_from_date_is_bigger_than_to_date()
    {
        $this->expectException(UserException::class);
        $charterSearchEntity = new CharterSearchEntity();
        $charterSearchEntity->setParameters([
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
     * @throws \Exception
     */
    function it_throws_validate_exception_for_expired_date_from()
    {
        $this->expectException(UserException::class);
        $charterSearchEntity = new CharterSearchEntity();
        $this->expectException(UserException::class);
        $charterSearchEntity->setParameters([
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
        $expectedCount = ShipGroup::count();

        $frontendData = (new CharterSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->getFrontendData();
        $this->assertCount($expectedCount, $frontendData);


        $shipGroup = ShipGroup::first();
        $shipGroup->is_active = false;
        $shipGroup->saveOrFail();

        $frontendData = (new CharterSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->getFrontendData();
        $this->assertCount($expectedCount - 1, $frontendData);


        $frontendData = (new CharterSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->setShowInactive(true)
            ->getFrontendData();
        $this->assertCount($expectedCount, $frontendData);
    }

}