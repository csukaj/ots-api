<?php

namespace Tests\Functional\Controllers\Admin;

use App\Email;
use App\Entities\EmailEntity;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class EmailControllerTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepare_models_and_entity() {
        $email = Email::all()->first();
        return [$email, (new EmailEntity($email))];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_emails() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/email', 'GET', $token, [], true);

        $allEmail = EmailEntity::getCollection(Email::all());

        $this->assertEquals(count($allEmail), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allEmail[$i];
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_an_email() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        list($email, $emailEntity) = $this->prepare_models_and_entity();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/email/{$email->id}", 'GET', $token, [], true);

        $this->assertEquals(
            $emailEntity->getFrontendData(),
            $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_content() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        list($email, ) = $this->prepare_models_and_entity();

        $data = [
            "id" => $email->id,
            "subject" => [
                "en" => $this->faker->text, "hu" => $this->faker->text, "de" => $this->faker->text, "ru" => $this->faker->text
            ],
            "content" => [
                "en" => $this->faker->text, "hu" => $this->faker->text, "de" => $this->faker->text, "ru" => $this->faker->text
            ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/email/{$email->id}", 'PUT', $token, $data);

        $this->assertEquals($email->id, $responseData->data->id);
        $this->assertEquals($data['subject']['en'], $responseData->data->subject->en);
        $this->assertEquals($data['content']['en'], $responseData->data->content->en);
    }
}
