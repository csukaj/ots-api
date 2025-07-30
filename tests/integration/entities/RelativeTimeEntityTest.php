<?php

namespace Tests\Integration\Entities;

use App\Entities\RelativeTimeEntity;
use App\Manipulators\RelativeTimeSetter;
use App\RelativeTime;
use Tests\TestCase;

class RelativeTimeEntityTest extends TestCase
{

    private function prepare_model_and_entity()
    {
        $data = [
            'day' => 1,
            'precision' => 'time_of_day',
            'time_of_day' => 'am',
            'hour' => '12',
            'time' => '11:12'
        ];
        (new RelativeTimeSetter($data, true))->set();

        $relativeTime = RelativeTime::all()->first();
        return [$relativeTime, (new RelativeTimeEntity($relativeTime))];
    }

    /**
     * @test
     */
    function it_can_present_content_data_for_frontend()
    {
        list($relativeTime, $relativeTimeEntity) = $this->prepare_model_and_entity();
        /** @var RelativeTimeEntity $relativeTimeEntity */
        $frontendData = $relativeTimeEntity->getFrontendData();

        $this->assertEquals($relativeTime->id, $frontendData['id']);
    }
}
