<?php

namespace Tests\Integration\Listeners;

use Tests\Integration\Mails\MailsTest;
use Tests\TestCase;

class NotificationSenderTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * Test event listener
     */
    public function testHandle()
    {
        $this->markTestSkipped('Implemented in ' . MailsTest::class . '. Check if needed further testing');
    }
}