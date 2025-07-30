<?php

namespace Tests\Integration\Entities;

use App\Entities\UserSettingEntity;
use App\Facades\Config;
use App\User;
use App\UserSetting;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class UserSettingEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity()
    {
        $user = User::all()->last();
        $usTx = Taxonomy::find(array_values(Config::getOrFail('taxonomies.user_settings'))[0]['id']);
        $userSetting = new UserSetting([
            'user_id' => $user->id,
            'setting_taxonomy_id' => $usTx->id,
            'value_taxonomy_id' => $usTx->getChildren()->first()->id
        ]);
        $userSetting->saveOrFail();
        return [$userSetting, (new UserSettingEntity($userSetting))];
    }

    /**
     * @test
     */
    function it_can_present_user_setting_data()
    {
        list($userSetting, $userSettingEntity) = $this->prepare_model_and_entity();


        $frontendData = $userSettingEntity->getFrontendData();

        $this->assertNotEmpty($frontendData['id']);
        $this->assertEquals($userSetting->setting->name, $frontendData['setting']);
        $this->assertEquals($userSetting->value->name, $frontendData['value']);

    }

}
