<?php

namespace Tests\Functional\Controllers\Extranet;

use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    public function it_can_reset_users_password()
    {

        $this->markTestIncomplete('need to implement');
        $this->markTestIncomplete('need to implement the case insensitivity check too');
    }

    /**
     * @test
     */
    public function it_cant_reset_password_using_bad_request()
    {
        $this->markTestIncomplete('need to implement');
    }


}
