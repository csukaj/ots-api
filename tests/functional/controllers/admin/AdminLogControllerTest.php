<?php

namespace Tests\Functional\Controllers\Admin;

use App\AdminLog;
use App\Device;
use App\Entities\AdminLogEntity;
use App\Facades\Config;
use App\Organization;
use Tests\TestCase;

class AdminLogControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;
    private $token;
    private $user;
    private $device;


    /**
     * @throws \Exception
     */
    private function prepareSomeLog()
    {
        list($this->token, $this->user) = $this->login([Config::getOrFail('stylersauth.role_admin')]);

        $this->assertSuccessfulHttpApiRequest('/stylersauth/user', 'GET', $this->token);

        $this->device = factory(Device::class, 'room')->create();


        $this->assertSuccessfulHttpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $this->device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 1, 'amount' => 2]
                ]
            ]
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_list_log_for_an_user()
    {
        $this->prepareSomeLog();
        $lastLoginRecord = AdminLog
            ::forUser($this->user->id)
            ->forRoute('stylersauth/user')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/log/' . $this->user->id, 'GET', $token, [], true);

        $this->assertEquals((new AdminLogEntity($lastLoginRecord))->getFrontendData()['created_at'], $responseData['lastLogin']);


        $availabilityLog = AdminLog
            ::forUser($this->user->id)
            ->forRoute('extranet/availability')
            ->orderBy('id', 'desc')
            ->paginate(10);
        $availabilityLog->setCollection(collect(AdminLogEntity::getCollection($availabilityLog->getCollection())));
        $expectedData = \json_decode(\json_encode($availabilityLog->toArray()), true);

        $this->assertNotEmpty($responseData['data']['data']);
        $this->assertEquals($expectedData, $responseData['data']);
    }

}
