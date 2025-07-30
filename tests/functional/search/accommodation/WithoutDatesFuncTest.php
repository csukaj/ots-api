<?php

namespace Tests\Functional\Search\Accommodation;

use App\Entities\Search\AccommodationSearchEntity;
use Tests\TestCase;

class WithoutDatesFuncTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function search($params){
        $frontendData = (new AccommodationSearchEntity())->setParameters($params)->getFrontendData();
        $actual = array_keys($frontendData);
        sort($actual);
        return $actual;
    }
    
    /**
     * @test
     */
    function it_can_be_queried_for_1_adult_in_1_room() {
        $this->assertEquals(
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],
            $this->search(
                ['usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]]
            )
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_in_1_room() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
            $this->search(
                ['usages' => [['usage' => [['age' => 21, 'amount' => 2]]]]]
            )
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_3_adults_in_1_room() {

        $this->assertEqualArrayContents(
            [3, 6, 12, 16, 17, 18], $this->search(
                ['usages' => [['usage' => [['age' => 21, 'amount' => 3]]]]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_10_adults_in_1_room() {

        $this->assertEqualArrayContents(
            [3], $this->search(
                ['usages' => [['usage' => [['age' => 21, 'amount' => 10]]]]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_20_adults_in_1_room() {

        $this->assertEqualArrayContents(
            [], $this->search(
                ['usages' => [['usage' => [['age' => 21, 'amount' => 20]]]]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_21_adults_in_1_room() {

        $this->assertEqualArrayContents(
            [], $this->search(
                ['usages' => [['usage' => [['age' => 21, 'amount' => 21]]]]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_1_plus_1_adults_in_2_rooms() {

        $this->assertEquals(
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_plus_1_adults_in_2_rooms() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_3_plus_1_adults_in_2_rooms() {

        $this->assertEqualArrayContents(
            [3, 6, 12, 16, 17, 18], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 3]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_plus_2_adults_in_2_rooms() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 21, 'amount' => 2]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_4_plus_1_adults_in_2_rooms() {

        $this->assertEqualArrayContents(
            [3, 6, 12], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 4]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_1_plus_3_adults_in_2_rooms() {

        $this->assertEqualArrayContents(
            [3, 6, 12, 16, 17, 18], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 3]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_10_plus_10_adults() {

        $this->assertEqualArrayContents(
            [3], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 10]]],
                ['usage' => [['age' => 21, 'amount' => 10]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_1_plus_1_plus_1_adults_in_3_rooms() {

        $this->assertEquals(
            [1, 2, 8, 9, 10, 11, 12, 13, 15, 16, 17, 18, 19, 20, 21], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_1_plus_2_plus_2_adults_in_3_rooms() {

        $this->assertEqualArrayContents(
            [1, 2, 8, 9, 10, 11, 12, 13, 15, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 21, 'amount' => 2]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_plus_4_plus_1_adults_in_3_rooms() {

        $this->assertEqualArrayContents(
            [12], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 21, 'amount' => 4]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_5_plus_1_plus_10_adults() {

        $this->assertEqualArrayContents(
            [], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 5]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 10]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_plus_1_plus_3_plus_2_plus_2_adults() {

        $this->assertEqualArrayContents(
            [], $this->search(['usages' => [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 21, 'amount' => 1]]],
                ['usage' => [['age' => 21, 'amount' => 3]]],
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 21, 'amount' => 2]]],
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_and_1_5_months_child_in_1_room() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 6, 7, 8, 10, 11, 12, 13, 14, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 0, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_and_1_4_year_old_child_in_1_room() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 6, 8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 4, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_and_1_17_year_old_child_in_1_room() {

        $this->assertEqualArrayContents(
            [3, 6, 9, 12, 15, 16, 17, 18], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 17, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_and_1_18_year_old_child_in_2_rooms() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 2]
                ]],
                ['usage' => [
                    ['age' => 18, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_and_2_10_and_12_year_old_children_in_2_rooms() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 2]
                ]],
                ['usage' => [
                    ['age' => 10, 'amount' => 1],
                    ['age' => 12, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_2_adults_and_3_1_and_8_and_11_year_old_children_in_2_rooms() {

        $this->assertEqualArrayContents(
            [1, 2, 3, 6, 7, 8, 10, 11, 12, 13, 14, 16, 17, 18, 19], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 1, 'amount' => 1]
                ]],
                ['usage' => [
                    ['age' => 8, 'amount' => 1],
                    ['age' => 11, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_4_adults_and_6_2_and_6_and_9_and_13_and_15_and_18_year_old_children_in_1_room() {

        $this->assertEqualArrayContents(
            [3], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 4],
                    ['age' => 2, 'amount' => 1],
                    ['age' => 6, 'amount' => 1],
                    ['age' => 9, 'amount' => 1],
                    ['age' => 13, 'amount' => 1],
                    ['age' => 15, 'amount' => 1],
                    ['age' => 18, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_3_adults_and_18_children_in_3_rooms() {

        $this->assertEqualArrayContents(
            [], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 1],
                    ['age' => 2, 'amount' => 1],
                    ['age' => 6, 'amount' => 1],
                    ['age' => 9, 'amount' => 1],
                    ['age' => 13, 'amount' => 1],
                    ['age' => 15, 'amount' => 1],
                    ['age' => 17, 'amount' => 1]
                ]],
                ['usage' => [
                    ['age' => 21, 'amount' => 1],
                    ['age' => 2, 'amount' => 1],
                    ['age' => 6, 'amount' => 1],
                    ['age' => 9, 'amount' => 1],
                    ['age' => 13, 'amount' => 1],
                    ['age' => 15, 'amount' => 1],
                    ['age' => 17, 'amount' => 1]
                ]],
                ['usage' => [
                    ['age' => 21, 'amount' => 1],
                    ['age' => 2, 'amount' => 1],
                    ['age' => 6, 'amount' => 1],
                    ['age' => 9, 'amount' => 1],
                    ['age' => 13, 'amount' => 1],
                    ['age' => 15, 'amount' => 1],
                    ['age' => 17, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_10_adults_and_3_children_in_1_room() {

        $this->assertEqualArrayContents(
            [], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 10],
                    ['age' => 2, 'amount' => 1],
                    ['age' => 6, 'amount' => 1],
                    ['age' => 9, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_8_adults_and_2_2_and_4_years_old_children_in_1_room() {

        $this->assertEqualArrayContents(
            [3], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 8],
                    ['age' => 2, 'amount' => 1],
                    ['age' => 4, 'amount' => 1]
                ]]
            ]])
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_for_8_adults_and_2_6_and_8_years_old_children_in_1_room() {

        $this->assertEqualArrayContents(
            [3], $this->search(['usages' => [
                ['usage' => [
                    ['age' => 21, 'amount' => 8],
                    ['age' => 6, 'amount' => 1],
                    ['age' => 8, 'amount' => 1]
                ]]
            ]])
        );
    }

}
