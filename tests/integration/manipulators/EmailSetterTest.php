<?php

namespace Tests\Integration\Manipulators;

use App\Email;
use App\Manipulators\EmailSetter;
use Tests\TestCase;

class EmailSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    function it_can_update_email() {
        $email = Email::all()->first();

        $update = [
            'id' => $email->id,
            'subject' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'content' => [
                'en' => $this->faker->text, 'hu' => $this->faker->text, 'de' => $this->faker->text, 'ru' => $this->faker->text
            ],
        ];

        $updatedEmail = (new EmailSetter($update))->set();
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals($email->id, $updatedEmail->id);
        $this->assertEquals($update['subject']['en'], $updatedEmail->subject->description);
        $this->assertEquals($update['content']['en'], $updatedEmail->content->description);
    }

}
