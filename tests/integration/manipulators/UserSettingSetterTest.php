<?php

namespace Tests\Integration\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\UserSettingSetter;
use App\User;
use App\UserSetting;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserSettingSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare()
    {
        $user = User::get()->last();
        Auth::login($user);
        return $user;
    }

    /**
     * @test
     */
    function it_can_save_user_setting()
    {
        $user = $this->prepare();

        $data = [
            'user_id' => $user->id,
            'setting' => Config::getOrFail('taxonomies.user_settings.calendar_start_day.name'),
            'value' => 'Sat'
        ];

        $userSetting = (new UserSettingSetter($data))->set();
        $this->assertInstanceOf(UserSetting::class, $userSetting);
        $this->assertEquals($data['setting'], $userSetting->setting->name);
        $this->assertEquals($data['value'], $userSetting->value->name);
    }

    /**
     * @test
     */
    function it_cant_save_user_setting_with_invalid_data()
    {

        $user = $this->prepare();

        $data = [
            'user_id' => $user->id,
            'value' => 'Sat'
        ];

        $this->expectException(UserException::class);
        (new UserSettingSetter($data))->set();
    }


    /**
     * @test
     */
    function it_can_update_user_setting()
    {
        $user = $this->prepare();

        $data = [
            'user_id' => $user->id,
            'setting' => Config::getOrFail('taxonomies.user_settings.calendar_start_day.name'),
            'value' => 'Sat'
        ];

        $setting = (new UserSettingSetter($data))->set();
        $this->assertInstanceOf(UserSetting::class, $setting);


        $update = [
            'user_id' => $user->id,
            'setting' => Config::getOrFail('taxonomies.user_settings.calendar_start_day.name'),
            'value' => 'Sat'
        ];

        $userSetting = (new UserSettingSetter($update))->set();
        $this->assertInstanceOf(UserSetting::class, $userSetting);
        $this->assertEquals($setting->id, $userSetting->id);
        $this->assertEquals($update['value'], $userSetting->value->name);
    }
}
