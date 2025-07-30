<?php

namespace Modules\Stylersauth\Tests\Functional;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Config;
use Modules\Stylersauth\Entities\User;
use Tests\TestCase;

class AuthTest extends TestCase {

    /**
     * @test
     */
    public function user_can_login() {
        $user = new User();
        $user->email = $email = $this->faker->email;
        $user->name = $this->faker->name;
        $user->password = $password = $this->faker->word;
        $this->assertTrue($user->save());
        
        $response = $this->call('POST','/stylersauth/authenticate', [
                'email' => $email,
                'password' => $password
        ]);

        $response->assertStatus(200);
        
        $responseData = json_decode($response->getContent());
        $this->assertNotEmpty($responseData);
        $this->assertNotEmpty($responseData->data->token);
    }
    
    /**
     * @test
     */
    public function admin_has_access()
    {
        $user = new User();
        $user->email = $email = $this->faker->email;
        $user->name = $name = $this->faker->name;
        $user->password = $password = $this->faker->word;
        $this->assertTrue($user->save());
        
        $loginResponse = $this->call('POST','/stylersauth/authenticate', [
                'email' => $email,
                'password' => $password
        ]);
        $loginResponse->assertStatus(200);
        $loginResponseData = json_decode($loginResponse->getContent());
        $token = $loginResponseData->data->token;
        
        
        $failResponse = $this->get('/stylersauth/admin', [
                "Authorization" => "Bearer $token"
        ]);
        $failResponse->assertStatus(403);
        //$failResponseData = json_decode($failResponse->getContent());
        //$this->assertNotEquals('Hello admin.', $failResponseData->message);
        
        $user->attachRole(Config::get('stylersauth.role_admin'));
        
        $response = $this->get('/stylersauth/admin', [
                "Authorization" => "Bearer $token"
        ]);
        $responseData = json_decode($response->getContent());
        $this->assertEquals('Hello admin.', $responseData->message);
    }
    
    /**
     * @test
     */
    public function user_can_logout()
    {
        $user = new User();
        $user->email = $email = $this->faker->email;
        $user->name = $name = $this->faker->name;
        $user->password = $password = $this->faker->word;
        $this->assertTrue($user->save());
        
        $response = $this->call('POST','/stylersauth/authenticate', [
                'email' => $email,
                'password' => $password
        ]);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent());
        $token = $responseData->data->token;
        
        $response2 = $this->get( '/stylersauth/user', [
                "Authorization" => "Bearer $token"
        ]);
        $responseData2 = json_decode($response2->getContent());
        $this->assertEquals($email, $responseData2->data->email);
        $this->assertEquals($name, $responseData2->data->name);
        
        $response3 = $this->get( '/stylersauth/logout', [
                "Authorization" => "Bearer $token"
        ]);
        $response3->assertStatus(200);
       
        $response4 = $this->get('/stylersauth/user', [
                "Authorization" => "Bearer $token"
        ]);
        $response4->assertStatus(401);
        
    }
    
    /**
     * @test
     */
    public function user_is_available_after_login()
    {
        $user = new User();
        $user->email = $email = $this->faker->email;
        $user->name = $name = $this->faker->name;
        $user->password = $password = $this->faker->word;
        $this->assertTrue($user->save());
        
        $response = $this->call('POST','/stylersauth/authenticate', [
                'email' => $email,
                'password' => $password
        ]);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent());
        
        $response = $this->get('/stylersauth/user', [
                "Authorization" => "Bearer {$responseData->data->token}"
        ]);
        $responseData = json_decode($response->getContent());
        
        $this->assertEquals($email, $responseData->data->email);
        $this->assertEquals($name, $responseData->data->name);
    }

    /**
     * @test
     */
    public function user_can_login_with_case_INsensitive_email() {
        $user = new User();
        $user->email = $email = strtolower($this->faker->email);
        $user->name = $this->faker->name;
        $user->password = $password = $this->faker->word;
        $this->assertTrue($user->save());

        $response = $this->call('POST','/stylersauth/authenticate', [
            'email' => strtoupper($email),
            'password' => $password
        ]);

        $response->assertStatus(200);

        $responseData = json_decode($response->getContent());
        $this->assertNotEmpty($responseData);
        $this->assertNotEmpty($responseData->data->token);
    }
}
