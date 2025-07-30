<?php

namespace Tests\Integration\Relations;

use App\Organization;
use App\Relations\PriceTaxonomyRelation;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class PriceTaxonomyRelationTest extends TestCase {

    /**
     * @test
     */
    function it_can_list_options() {
        $relation = new PriceTaxonomyRelation(new Taxonomy(), Organization::findOrFail(1));
        $data = $relation->getFrontendData();
        $this->assertCount(4, $data['options']);
    }
}
