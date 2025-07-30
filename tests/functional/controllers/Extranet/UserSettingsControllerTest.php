<?php

namespace Tests\Functional\Controllers\Extranet;

use App\Facades\Config;
use App\UserSetting;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class UserSettingsControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;


    /**
     * @test
     */
    public function it_can_list_user_settings()
    {
        $this->markTestIncomplete('need to implement');
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);

        $usTx = Taxonomy::find(array_values(Config::getOrFail('taxonomies.user_settings'))[0]['id']);
        $userSetting = new UserSetting([
            'user_id' => $user->id,
            'setting_taxonomy_id' => $usTx->id,
            'value_taxonomy_id' => $usTx->getChildren()->first()->id
        ]);
        $userSetting->saveOrFail();

        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/user-settings', 'GET', $token, [], true);

        $expected = [
            'success' => true,
            'data' =>
                [
                    'user' =>
                        [
                            'id' => 1,
                            'email' => 'root@example.com',
                            'name' => 'root',
                        ],
                    'settings' =>
                        [
                            [
                                'id' => 11,
                                'setting' => 'Calendar start day',
                                'value' => 'Mon',
                            ]
                        ],
                    'options' =>
                        [
                            'id' => 518,
                            'parent_id' => null,
                            'name' => 'user_setting',
                            'priority' => null,
                            'is_active' => true,
                            'is_required' => false,
                            'is_readonly' => true,
                            'is_merchantable' => false,
                            'is_searchable' => false,
                            'type' => 'unknown',
                            'icon' => null,
                            'descendants' =>
                                [
                                    [
                                        'id' => 519,
                                        'parent_id' => 518,
                                        'name' => 'Calendar start day',
                                        'priority' => 0,
                                        'is_active' => true,
                                        'is_required' => false,
                                        'is_readonly' => true,
                                        'is_merchantable' => false,
                                        'is_searchable' => false,
                                        'type' => 'unknown',
                                        'icon' => null,
                                        'descendants' =>
                                            [
                                                [
                                                    'id' => 520,
                                                    'parent_id' => 519,
                                                    'name' => 'Mon',
                                                    'priority' => 0,
                                                    'is_active' => true,
                                                    'is_required' => false,
                                                    'is_readonly' => true,
                                                    'is_merchantable' => false,
                                                    'is_searchable' => false,
                                                    'type' => 'unknown',
                                                    'icon' => null,
                                                    'descendants' =>
                                                        [],
                                                ],

                                                [
                                                    'id' => 521,
                                                    'parent_id' => 519,
                                                    'name' => 'Sat',
                                                    'priority' => 1,
                                                    'is_active' => true,
                                                    'is_required' => false,
                                                    'is_readonly' => true,
                                                    'is_merchantable' => false,
                                                    'is_searchable' => false,
                                                    'type' => 'unknown',
                                                    'icon' => null,
                                                    'descendants' =>
                                                        [],
                                                ],
                                            ],
                                    ],
                                ],
                        ],
                ],
        ];
        $this->assertEquals($expected, $responseData);
    }


    /**
     * @test
     */
    public function it_can_store_user_settings()
    {
        $this->markTestIncomplete('need to implement');
        list($token,) = $this->login([Config::get('stylersauth.role_manager')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/user-settings', 'POST', $token, [
            'settings'=>[

            ]
        ], true);

        $this->assertEquals(['success' => true, 'data' => []], $responseData);
    }

}
