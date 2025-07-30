<?php

namespace Tests\Procedure\Ots\PriceModifier;

use App\Organization;
use Tests\Procedure\ProcedureTestCase;

class MergedFreeNightsPriceModifierTest extends ProcedureTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.room_search import RoomSearch' . PHP_EOL.
            'from ots.common.config import Config' . PHP_EOL;

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
    
    private function prepare($organizationId) {
        $this->organization = Organization::findOrFail($organizationId);
        if (!isset($this->device)) {
            $this->device = $this->organization->devices[0];
        }
        $this->today = date('Y-m-d');
    }
    
    protected function prepareRoomSearch($organizationId = 1, $request = [], $fromDate = null, $toDate = null, $bookingDate = null, $weddingDate = null)
    {
        $roomSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'params' => json_encode([
                'request' => $request,
                'interval' => [
                    'date_from' => $fromDate ?: $this->dateFrom,
                    'date_to' => $toDate ?: $this->dateTo
                ],
                'booking_date' => $bookingDate,
                'wedding_date' => $weddingDate
            ])
        ]);
        return self::$imports . PHP_EOL . "room_search = RoomSearch({$roomSearchConfig})" . PHP_EOL;
    }

    private function runAndDecode($script) {
        return $this->jsonDecode($this->runPythonScript($script), true);
    }
    
    private function getRooms($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate = null) {
        $script = $this->prepareRoomSearch($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate);
        $script .= "print room_search.get_rooms()" . PHP_EOL;
        return $this->runAndDecode($script);
    }

    /**
     * @test
     */
    function it_can_calculate_merged_price_modifiers_with_one_overlap_variation() {
        $this->prepare(20);
        $actualDiscounts = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2027-05-25',
            '2027-06-06',
            $this->today,
            null
        )['results'][0][0]['prices'][0]['discounts'];
        $actualFreeNights = array_filter($actualDiscounts, function($discount){return $discount['offer']=='merged_free_nights';});
        $this->assertEquals(
            [
                'name' => [
                    'en' => 'Merged Free Nights: 5=4, 7=6'
                ],
                'discount_percentage' => -15.38,
                'offer' => 'merged_free_nights',
                'discount_value' => -200,
                'modifier_type' => 491,
                'condition' => 'merged_free_nights',
                'description' => [
                    'en' => ''
                ]

            ],
            array_pop($actualFreeNights)
        );
    }
    
    /**
     * @test
     * OTS-942 1. foglalás
     * "A" 7 éj + "B" 5 éj = 12 éj
     * első 5 nap 5=4: 1 éjszaka, EUR100 áron (1. eset)
     * második 7 nap 7=6: 1 éjszaka, EUR100 áron (2. eset)
     * kedvezmény összesen: 2 éjszaka, EUR200
     */
    public function seven_nights_in_first_period_and_five_nights_in_second() {
        $this->prepare(20);
        $actualDiscounts = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2027-05-25',
            '2027-06-06',
            $this->today,
            null
        )['results'][0][0]['prices'][0]['discounts'];
        $actualFreeNights = array_filter($actualDiscounts, function($discount){return $discount['offer']=='merged_free_nights';});
        $this->assertEquals(
            [
                'name' => [
                    'en' => 'Merged Free Nights: 5=4, 7=6'
                ],
                'discount_percentage' => -15.38,
                'offer' => 'merged_free_nights',
                'discount_value' => -200,
                'modifier_type' => 491,
                'condition' => 'merged_free_nights',
                'description' => [
                    'en' => ''
                ]
            ],
            array_pop($actualFreeNights)
        );
    }
    
    /**
     * @test
     * OTS-942 2. foglalás
     * "A" 3 éj + "B" 9 éj = 12 éj
     * első 5 nap nincs kedvezmény: a kevésbé szigorúbb feltétel nem alkalmazható a szigorúbb időszakban!
     * második 7 nap 7=6: 1 éjszaka, EUR120 áron (1. eset)
     * kedvezmény összesen: 1 éjszaka, EUR120
     * 
     * De a másik periódusban EUR100 és az olcsóbbat kell megadni ... 
     */
    public function three_nights_in_first_period_and_nine_nights_in_second() {
        $this->prepare(20);
        $actual = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2027-05-29',
            '2027-06-10',
            $this->today,
            null
        )['results'][0][0]['prices'][0]['discounts'];
        
        $this->assertEquals(
            [
                'name' => [
                    'en' => '7=6'
                ],
                'discount_percentage' => -7.25,
                'offer' => 'free_nights',
                'discount_value' => -100.0,
                'modifier_type' => 491,
                'condition' => 'minimum_nights',
                'description' => null
            ],
            $actual[0]
        );
    }

    /**
     * @test
     * OTS-942 3. foglalás
     * "A" 3 éj + "B" 4 éj = 7 éj
     * 7 nap 7=6: 1 éjszaka, EUR100 áron (2. eset)
     * kedvezmény összesen: 1 éjszaka, EUR100
     */
    public function three_nights_in_first_period_and_four_nights_in_second() {
        $this->prepare(20);
        $actual = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2027-05-29',
            '2027-06-05',
            $this->today,
            null
        )['results'][0][0]['prices'][0]['discounts'];
        
        $this->assertEquals(
            [
                'name' => [
                    'en' => 'Merged Free Nights: 5=4, 7=6'
                ],
                'discount_percentage' => -12.82,
                'offer' => 'merged_free_nights',
                'discount_value' => -100,
                'modifier_type' => 491,
                'condition' => 'merged_free_nights',
                'description' => [
                    'en' => ''
                ]
            ],
            $actual[0]
        );
    }

    /**
     * @test
     * OTS-942 4. foglalás
     * "A" 4 éj + "B" 1 éj = 5 éj
     * nincs kedvezmény: a kevésbé szigorúbb feltétel nem alkalmazható a szigorúbb időszakban!
     * kedvezmény összesen: 0 éjszaka, EUR0
     */
    public function four_nights_in_first_period_and_one_nights_in_second() {
        $this->prepare(20);
        $actual = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2027-05-28',
            '2027-06-01',
            $this->today,
            null
        )['results'][0][0]['prices'][0]['discounts'];

        $this->assertCount(1, $actual);
        $this->assertNotEquals(substr($actual[0]['name']['en'], 0, 15), 'Merged Free Nights');
    }
    
    /**
     * @test
     * OTS-942 5. foglalás
     * "B" 2 éj + "C" 7 éj = 9 éj
     * az ingyen éjszaka a drágább időszakban van, "use last consecutive night" beállítás miatt
     * kedvezmény összesen: 1 éjszaka, EUR140
     */
    public function it_can_use_last_consecutive_night() {
        $this->prepare(20);
        $result = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2027-12-29',
            '2028-01-07',
            $this->today,
            null
        );
        
        $actualDiscounts = $result['results'][0][0]['prices'][0]['discounts'];
        $actualFreeNights = array_filter($actualDiscounts, function($discount){return $discount['offer']=='merged_free_nights';});
        
        $this->assertEquals(
            [
                'name' => [
                    'en' => 'Merged Free Nights: 7=6, 9=8'
                ],
                'discount_percentage' => -11.67,
                'offer' => 'merged_free_nights',
                'discount_value' => -140,
                'modifier_type' => 491,
                'condition' => 'merged_free_nights',
                'description' => [
                    'en' => ''
                ]
            ],
           array_pop($actualFreeNights)
        );
    }
    
    /**
     * @test
     * OTS-942 6. foglalás
     * "D" 7 éj + "E" 8 éj + "F" 10 éj = 25 éj
     * az időszakokban marad 2-2-3 éjszaka szabadon, ami összevonható egy 7=6 kedvezménybe
     * kedvezmény összesen: 4 éjszaka, EUR400
     */
    public function it_can_calculate_price_modifier_with_days_merged_from_three_periods() {
        $this->prepare(20);
        $actual = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [ 
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2028-06-05',
            '2028-07-06',
            $this->today,
            null
        )['results'][0][0]['prices'][0];
        
        $this->assertEquals(
            [
                'name' => [
                    'en' => 'Merged Free Nights: 5=4, 6=5, 7=6'
                ],
                'discount_percentage' => -12.9,
                'offer' => 'merged_free_nights',
                'discount_value' => -400,
                'modifier_type' => 491,
                'condition' => 'merged_free_nights',
                'description' => [
                    'en' => ''
                ]
            ],
            $actual['discounts'][0]
        );
    }
    
    /**
     * @test
     * 7. foglalás
     * "D" 7 éj + "E" 8 éj + "F" 10 éj + "G" 6 éj = 31 éj
     * az időszakokban marad 2-2-3-1 éjszaka szabadon, ami összevonható egy 7=6 kedvezménybe
     * kedvezmény összesen: 5 éjszaka, EUR500
     */
    public function it_can_calculate_price_modifier_with_days_merged_from_four_periods() {
        $this->prepare(20);
        $actual = $this->getRooms(
            $this->organization->id,
            [
                ['usage' => [ 
                    ['age' => 21, 'amount' => 1]
                ]]
            ],
            '2028-06-05',
            '2028-07-12',
            $this->today,
            null
        )['results'][0][0]['prices'][0];
        
        $this->assertEquals(
            [
                'name' => [
                    'en' => 'Merged Free Nights: 5=4, 6=5, 7=6'
                ],
                'discount_percentage' => -13.51,
                'offer' => 'merged_free_nights',
                'discount_value' => -500.0,
                'modifier_type' => 491,
                'condition' => 'merged_free_nights',
                'description' => [
                    'en' => ''
                ]
            ],
            $actual['discounts'][0]
        );
    }
    
}
