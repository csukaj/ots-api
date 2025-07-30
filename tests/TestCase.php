<?php

namespace Tests;

use App\Entities\Search\AccommodationSearchEntity;
use App\Entities\Search\CruiseSearchEntity;
use App\Organization;
use App\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


class TestCase extends IlluminateTestCase
{
    use CreatesApplication;

    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    //use \Illuminate\Foundation\Testing\DatabaseMigrations;
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Set up faker object and seeders before tests
     *
     * @var string
     */
    protected $faker;

    /**
     * Set up mode for database setup/teardown before/after every test or only once per class
     */
    const SETUPMODE_ALWAYS = 'always';
    const SETUPMODE_ONCE = 'once';
    const SETUPMODE_NEVER = 'never';

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * Set up mode for database setup/teardown before/after every test or only once per class
     */
    const TESTMODE_CONTROLLER_WRITE = 'controller';
    const TESTMODE_OTHER = 'other';

    static public $testMode = self::TESTMODE_OTHER;

    /**
     * Setup before every test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    /**
     * Sends a HTTP request to the test API
     * UPDATE: we not use test host but tests run in tests api
     *
     * @param string $url
     * @param string $verb
     * @param array|string $headersOrToken
     * @param array|\stdObject $body
     * @param bool $jsonDecodeAssoc
     * @param array $file
     * @return array|\stdObject
     */
    protected function httpApiRequest($url, $verb, $headersOrToken = array(), $body = array(), $jsonDecodeAssoc = false, $file = [])
    {
        $headers = is_array($headersOrToken) ? $headersOrToken : ['Authorization' => "Bearer {$headersOrToken}"];
        $server = $this->transformHeadersToServerVars($headers);

        // this is the magic here: this uses internal laravel request, so transactions are still valid
        $response = $this->call($verb, $url, $body, [], $file, $server);

        $responseBody = json_decode($response->getContent(), $jsonDecodeAssoc);
        return [$response->getStatusCode(), $responseBody, $response];
    }

    /**
     * Sends a HTTP request to the test API and check for successful response
     * UPDATE: we not use test host but tests run in tests api
     *
     * @param string $url
     * @param string $verb
     * @param array|string $headersOrToken
     * @param array|\stdObject $body
     * @param bool $jsonDecodeAssoc
     * @param array $file
     * @return array|\stdObject
     */
    protected function assertSuccessfulHttpApiRequest($url, $verb, $headersOrToken = array(), $body = array(), $jsonDecodeAssoc = false, $file = [])
    {
        $headers = is_array($headersOrToken) ? $headersOrToken : ['Authorization' => "Bearer {$headersOrToken}"];
        $server = $this->transformHeadersToServerVars($headers);

        // this is the magic here: this uses internal laravel request, so transactions are still valid
        $response = $this->call($verb, $url, $body, [], $file, $server);
        if($response->getStatusCode() >= 400){
            var_dump($response->getContent());
        }
        $response->assertStatus(200);

        $responseBody = $jsonDecodeAssoc ? $response->json() : \json_decode($response->getContent());
        $this->assertNotEmpty($responseBody);
        $this->assertTrue($jsonDecodeAssoc ? $responseBody['success'] : $responseBody->success);
        return $responseBody;
    }

    /**
     * Creates and logs in a test user
     *
     * @param array $roles
     * @return array
     */
    protected function login(array $roles = array())
    {
        switch ($roles[0]) {
            case Config::get('stylersauth.role_admin'):
                $user = User::find(1);
                break;
            case Config::get('stylersauth.role_user'):
                $user = User::find(2);
                break;
            case Config::get('stylersauth.role_manager'):
                $user = User::find(3);
                break;
            case Config::get('stylersauth.role_advisor'):
                $user = User::find(4);
                break;
        }

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/stylersauth/authenticate',
            'POST',
            [],
            ['email' => $user->email, 'password' => 'sdakfg8756HKSDGF']
        );

        $this->assertNotEmpty($responseData->data->token);

        return [$responseData->data->token, $user];
    }

    /**
     * Asserts if two one-dimensional arrays are the same, even with different item orders
     * @param array $expected
     * @param array $actual
     */
    protected function assertEqualArrayContents($expected, $actual)
    {
        sort($actual);
        $this->assertTrue(
            count(array_diff(array_merge($expected, $actual), array_intersect($expected, $actual))) === 0,
            "Failed asserting that " . json_encode($actual, true) . " matches expected " . json_encode($expected,
                true) . "."
        );
    }

    /**
     * returns a unique string from a multidimensional array
     * @param mixed $a
     * @return String
     */
    private function sortThenString($a) {
  
        if(is_array($a)) {
          foreach($a as $key => $i) {
              $a[$key] = $this->sortThenString($i);
          }
          sort($a);
        }
        
        return json_encode($a);
    }


    /**
     * Compares unordered multidimensional arraies
     * @param mixed $a
     * @param mixed $b
     */
    protected function assertUnorderedMultidimensionalSetsEquals($expected, $actual) {
        $this->assertEquals(
            $this->sortThenString($expected), 
            $this->sortThenString($actual)
        );
    }
    
    /**
     * Asserts if two structures of array/stdClass are equal regardless of types
     * @param mixed $expected
     * @param mixed $actual
     */
    protected function assertEqualStructures($expected, $actual)
    {
        $expected = (array)$expected;
        $actual = (array)$actual;
        $this->assertEquals(count($expected), count($actual));
        foreach ($actual as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $this->assertEqualStructures($expected[$key], $item);
            } else {
                $this->assertEquals($expected[$key], $item);
            }
        }
    }

    /**
     * Asserts if two structures of array are equal regardless of IDs
     * @param array $expected
     * @param array $actual
     */
    protected function assertEqualSamples(array $expected, array $actual)
    {
        $this->assertCount(count($expected), $actual);
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $actual)) {
                throw new \Exception("Missing key: `{$key}`");
            }
            if (is_array($value)) {
                $this->assertEqualSamples($expected[$key], $actual[$key]);
            } elseif (!is_null($value) && is_string($key) && ($key == 'id' || preg_match('/\\_id$/', $key))) {
                $this->assertGreaterThan(0, $actual[$key]);
            } else {
                $this->assertEquals($value, $actual[$key],
                    "Failed asserting that {$actual[$key]} matches expected {$value} for `{$key}`.");
            }
        }
    }

    protected function assertEqualsJSONFile($expectedFile, $actualInput)
    {
        $expected = file_get_contents($expectedFile);
        $actual = (is_string($actualInput)) ? $actualInput : json_encode($actualInput);
        $expected = preg_replace('/"(name_description_id)"\s*:\s*(\d+)/', '"$1": 0', $expected);
        $actual = preg_replace('/"(name_description_id)"\s*:\s*(\d+)/', '"$1": 0', $actual);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    protected function prepareAccommodationSearchResult(
        $interval,
        $accommodation = null,
        $usages = null,
        $wedding = null,
        $booking = null
    )
    {
        $parameters = ['interval' => $interval];
        if ($accommodation) {
            if (is_string($accommodation)) {
                $organizationId = self::getOrganizationsByName($accommodation)[0]->id;
            } else {
                $organizationId = intval($accommodation);
            }
            $parameters['organizations'] = [$organizationId];
        }
        if ($usages) {
            $parameters['usages'] = $usages;
        }
        if ($wedding) {
            $parameters['selectedOccasion'] = "honeymoon";
            $parameters['wedding_date'] = $wedding;
        }
        $entity = (new AccommodationSearchEntity())->setParameters($parameters);
        if ($booking) {
            $entity->setBookingDateForTests($booking);
        }
        $frontendData = $entity->getFrontendData();
        return $accommodation && isset($frontendData[$organizationId]) ? $frontendData[$organizationId] : $frontendData;
    }

    protected function prepareCruiseSearchResult(
        $interval,
        $cruiseId = null,
        $usages = null,
        $wedding = null
    )
    {
        $parameters = ['interval' => $interval];
        if ($usages) {
            $parameters['usages'] = $usages;
        }
        if ($wedding) {
            $parameters['selectedOccasion'] = 'honeymoon';
            $parameters['wedding_date'] = $wedding;
        }
        $frontendData = (new CruiseSearchEntity())->setParameters($parameters)->getFrontendData();

        if ($cruiseId) {
            $result = [];
            foreach ($frontendData as $row) {
                if ($row['info']['id'] == $cruiseId) {
                    $result[] = $row;
                }
            }
        } else {
            $result = $frontendData;
        }

        return $result;
    }

    /**
     * Get organizations By name (on default language)
     *
     * @param string $name
     * @return Collection
     * @static
     */
    static public function getOrganizationsByName(string $name)
    {
        return Organization
            ::select(DB::raw('organizations.*'))
            ->join('descriptions', 'descriptions.id', '=', 'organizations.name_description_id')
            ->where('descriptions.description', '=', $name)
            ->orderBy('organizations.id')
            ->get();
    }

}
