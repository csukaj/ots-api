<?php

namespace Tests\Integration\Relations;

use App\Organization;
use App\Relations\AgeRangeRelation;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class AgeRangeRelationTest extends TestCase {

    /**
     * @test
     */
    function it_can_list_options() {
        $relation = new AgeRangeRelation(new Taxonomy(), Organization::findOrFail(1));
        $data = $relation->getFrontendData();
        $this->assertCount(3, $data['options']);
    }
}
