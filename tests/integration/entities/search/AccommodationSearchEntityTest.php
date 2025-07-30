<?php

namespace Tests\Integration\Entities\Search;

use App\Accommodation;
use App\Availability;
use App\Device;
use App\Entities\Search\AccommodationSearchEntity;
use App\Exceptions\UserException;
use App\Manipulators\AvailabilitySetter;
use App\Organization;
use Tests\TestCase;

class AccommodationSearchEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * @throws UserException
     */
    function it_can_set_parameters()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $actual = $accommodationSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]]);
        $this->assertInstanceOf(AccommodationSearchEntity::class,$actual);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_non_numeric_amount()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->expectException(UserException::class);
        $accommodationSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 'fakeAmount']]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_zero_amount()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->expectException(UserException::class);
        $accommodationSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 0]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_negative_amount()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->expectException(UserException::class);
        $accommodationSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => -1]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_fractional_amount()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->expectException(UserException::class);
        $accommodationSearchEntity->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1.2]]]]]);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_throws_error_for_invalid_age_range()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->expectException(UserException::class);
        $accommodationSearchEntity->setParameters(['usages' => [['usage' => [['age' => 'fakeAgeRange', 'amount' => 1]]]]]);
    }

    /**
     * @test
     * @throws UserException
     * @throws \Exception
     */
    function it_throws_validate_exception_if_date_from_not_set()
    {
        $this->expectException(UserException::class);
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->expectException(UserException::class);
        $accommodationSearchEntity->setParameters([
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
        $expectedCount = Accommodation::count();

        $frontendData = (new AccommodationSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->getFrontendData();
        $this->assertCount($expectedCount, $frontendData);


        $accommodation = Accommodation::find(1);
        $accommodation->is_active = false;
        $accommodation->saveOrFail();

        $frontendData = (new AccommodationSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->getFrontendData();
        $this->assertCount($expectedCount - 1, $frontendData);


        $frontendData = (new AccommodationSearchEntity())
            ->setParameters(['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]])
            ->setShowInactive(true)
            ->getFrontendData();
        $this->assertCount($expectedCount, $frontendData);
    }

    /**
     * @test
     * @throws UserException
     * @throws \Throwable
     */
    function it_can_search_for_unavailable_if_showInactive_parameter_set()
    {
        $accommodationId=1;

        $deviceIds = Device::forDeviceable(Organization::class, $accommodationId)->get()->pluck('id')->toArray();
        $from = date('Y-m-d', time() + 2 * 24 * 3600);
        $to = date('Y-m-d', time() + 6 * 24 * 3600);
        foreach ($deviceIds as $deviceId) {
            (new AvailabilitySetter([
                'availableType' => Device::class,
                'availableId' => $deviceId,
                'fromDate' => $from,
                'toDate' => $to,
                'amount' => 0,
            ]))->set();
        }

        $params = [
            'organizations' => [$accommodationId],
            'interval' => [
                'date_from' => $from,
                'date_to' => $to
            ],
            'usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]];

        $frontendData = (new AccommodationSearchEntity())
            ->setParameters($params)
            ->getFrontendData();
        $this->assertEmpty($frontendData);


        $frontendData = (new AccommodationSearchEntity())
            ->setParameters($params)
            ->setShowInactive(true)
            ->getFrontendData();
        $this->assertNotEmpty($frontendData);
    }


}