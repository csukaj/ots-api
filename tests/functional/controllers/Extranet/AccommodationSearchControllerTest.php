<?php

namespace Tests\Functional\Controllers\Extranet;

use Illuminate\Support\Facades\Config;
use Tests\functional\controllers\AccommodationSearchControllerTest as SearchController;

class AccommodationSearchControllerTest extends SearchController
{
    protected $_url = '/extranet/accommodation-search';
    protected $token = []; // Token is empty for normal search, extranet is protected, so we must get a token

    public function setUp()
    {
        parent::setUp();
        [$this->token] = $this->login([Config::get('stylersauth.role_admin')]);
    }

    /**
     * @test
     */
    public function it_can_not_list_accommodations_when_not_logged_in()
    {
        [$this->token] = $this->login([Config::get('stylersauth.role_user')]);

        [, , $response] = $this->httpApiRequest($this->_url, 'POST');
        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

}
