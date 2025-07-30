<?php

namespace Tests\Functional\Controllers\Extranet;

use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    public function it_can_send_reset_email_to_valid_user()
    {
        $data = ['email' => 'root@example.com'];
        $responseData = $this->assertSuccessfulHttpApiRequest('/password/email', 'POST',
            ['Accept' => 'application/json, text/plain, */*'], $data);

        $this->assertEquals("An email has been sent to your address to change your password.", $responseData->message->status);
    }

    /**
     * @test
     */
    public function it_cant_send_email_using_bad_request()
    {
        $data = ['invalid_data' => 'invalid_data'];
        list(, $responseData, $response) = $this->httpApiRequest('/password/email', 'POST',
            ['Accept' => 'application/json, text/plain, */*'], $data);

        $response->assertStatus(400);
        $this->assertFalse($responseData->success);
        $this->assertEquals("The given data failed to pass validation.", $responseData->error);
    }

    /**
     * @test
     */
    public function it_cant_send_email_link_to_invalid_mail()
    {
        $data = ['email' => 'not-exists@example.com'];
        list(, $responseData, $response) = $this->httpApiRequest('/password/email', 'POST',
            ['Accept' => 'application/json, text/plain, */*'], $data);

        $response->assertStatus(202);
        $this->assertFalse($responseData->success);
        $this->assertEquals(
            "The provided email address is not correct. Please use the email address associated with your account",
            $responseData->message->status
        );
    }

    /**
     * @test
     */
    public function it_can_send_reset_email_to_valid_user_when_email_case_INsensitive()
    {
        $data = ['email' => 'ROOT@example.com'];
        $responseData = $this->assertSuccessfulHttpApiRequest('/password/email', 'POST',
            ['Accept' => 'application/json, text/plain, */*'], $data);

        $this->assertEquals("An email has been sent to your address to change your password.", $responseData->message->status);
    }

}
