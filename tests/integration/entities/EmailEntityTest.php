<?php

namespace Tests\Integration\Entities;

use App\Email;
use App\Entities\EmailEntity;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class EmailEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $email = Email::all()->first();
        return [$email, (new EmailEntity($email))];
    }

    private function getDescTranslations(Description $description) {
        return (new DescriptionEntity($description))->getFrontendData();
    }

    /**
     * @test
     */
    function it_can_present_content_data() {
        list($email, $emailEntity) = $this->prepare_model_and_entity();
        /** @var EmailEntity $emailEntity */
        $frontendData = $emailEntity->getFrontendData();

        $this->assertEquals($email->id, $frontendData['id']);
        $this->assertEquals($this->getDescTranslations($email->subject), $frontendData['subject']);
        $this->assertEquals($this->getDescTranslations($email->content), $frontendData['content']);
    }
}
