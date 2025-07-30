<?php

namespace Tests\Integration\Manipulators;

use App\Exceptions\UserException;
use App\Manipulators\RelativeTimeSetter;
use App\RelativeTime;
use Tests\TestCase;

class RelativeTimeSetterTest extends TestCase {

    private function prepare() {
        return [
            'day' => 1,
            'precision' => 'time_of_day',
            'time_of_day' => 'am',
            'hour' => '12',
            'time' => '11:12'
        ];
    }

    /**
     * @test
     */
    function it_cannot_create_with_string_day() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        $data['day'] = 'asd';
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_cannot_create_without_day() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        unset($data['day']);
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_cannot_create_without_precision() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        unset($data['precision']);
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_cannot_create_hour_without_hour() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        $data['precision'] = 'hour';
        unset($data['hour']);
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_cannot_create_hour_with_string_hour() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        $data['precision'] = 'hour';
        $data['hour'] = 'asd';
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_cannot_create_hour_wrong_hour() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        $data['precision'] = 'hour';
        $data['hour'] = 24;
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_cannot_create_hour_without_time() {
        $this->expectException(UserException::class);
        $data = $this->prepare();
        $data['precision'] = 'time';
        unset($data['time']);
        $relativetimeSetter = new RelativeTimeSetter($data, true);
    }

    /**
     * @test
     */
    function it_can_create() {
        $data = $this->prepare();

        $relativeTime = (new RelativeTimeSetter($data, true))->set();

        $this->assertTrue(!!$relativeTime->id);
        $this->assertEquals(config('taxonomies.relativetime_time_of_day'), $relativeTime->timeOfDayTaxonomy->parent_id);
        $this->assertEquals($data['time_of_day'], $relativeTime->timeOfDayTaxonomy->name);
    }

    /**
     * @test
     */
    function it_can_update() {
        $data = [
            'id' => RelativeTime::first()->id,
            'day' => 1,
            'precision' => 'hour',
            'hour' => 12,
            'time' => '11:12'
        ];
        $relativeTime = (new RelativeTimeSetter($data, true))->set();

        $this->assertEquals($data['day'], $relativeTime->day);
        $this->assertEquals($data['hour'], substr($relativeTime->time, 0, 2));
    }

    /**
     * @test
     */
    function it_can_create_new_time_of_day_taxonomy() {
        $data = $this->prepare();
        $data['time_of_day'] = 'newTX';
        $relativeTime = (new RelativeTimeSetter($data, true))->set();

        $this->assertTrue(!!$relativeTime->id);
        $this->assertEquals(config('taxonomies.relativetime_time_of_day'), $relativeTime->timeOfDayTaxonomy->parent_id);
        $this->assertEquals($data['time_of_day'], $relativeTime->timeOfDayTaxonomy->name);
    }

}
