<?php

namespace Tests\Integration\Entities;

use App\AdminLog;
use App\Device;
use App\Entities\AdminLogEntity;
use App\Facades\Config;
use Tests\TestCase;

class AdminLogEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    private $token;
    private $user;

    /**
     * @throws \Exception
     */
    private function prepareSomeLog()
    {
        list($this->token, $this->user) = $this->login([Config::getOrFail('stylersauth.role_admin')]);

        $this->assertSuccessfulHttpApiRequest('/stylersauth/user', 'GET', $this->token);

        $device = factory(Device::class, 'room')->create();


        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 1, 'amount' => 2]
                ]
            ]
        );
        $this->assertTrue($responseData->success);
    }


    /**
     * @test
     * @throws \Exception
     */
    function it_can_present_admin_log_data()
    {
        $this->prepareSomeLog();
        $logEntry = AdminLog::get()->last();

        $expected = $logEntry->attributesToArray();
        $expected['request'] = \json_decode($logEntry->request);
        $expected['response'] = \json_decode($logEntry->response);
        $expected['created_at'] = $logEntry->created_at->toIso8601ZuluString();
        $expected['updated_at'] = $logEntry->updated_at->toIso8601ZuluString();

        $actual = (new AdminLogEntity($logEntry))->getFrontendData();

        $this->assertEquals($expected, $actual);

    }

}
