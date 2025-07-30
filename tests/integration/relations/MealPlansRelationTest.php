<?php

namespace Tests\Integration\Relations;

use App\Relations\MealPlansRelation;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class MealPlansRelationTest extends TestCase {


    /**
     * @test
     */
    function it_can_list_options() {
        $relation = new MealPlansRelation(new Taxonomy());
        $data = $relation->getFrontendData();
        $this->assertEquals(5, count($data['options']));
    }
}
