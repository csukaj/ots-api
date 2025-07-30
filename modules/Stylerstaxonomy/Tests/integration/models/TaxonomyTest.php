<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Models;

use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class TaxonomyTest extends TestCase {

    /**
     * @test
     */
    function it_can_be_created() {
        $tx = new Taxonomy();
        $tx->name = 'Test node';
        $this->assertTrue($tx->save());
        $this->assertGreaterThanOrEqual(10000, $tx->id);
    }

    /**
     * @test
     */
    function it_can_be_nested() {
        $tx1 = new Taxonomy();
        $tx1->name = 'Test node 1';
        $this->assertTrue($tx1->save());

        $tx2 = new Taxonomy();
        $tx2->name = 'Test node 2';
        $this->assertTrue($tx2->save());

        $this->assertFalse($tx2->isDescendantOf($tx1));

        $tx2->makeChildOf($tx1);

        $this->assertTrue($tx2->isDescendantOf($tx1));
        $this->assertTrue($tx1->isAncestorOf($tx2));
    }

    /**
     * @test
     */
    function it_can_be_sorted() {
        $tx0 = new Taxonomy(['name' => 'Test node 0']);
        $this->assertTrue($tx0->save());

        $tx2 = new Taxonomy(['name' => 'Test node 2', 'priority' => 2]);
        $this->assertTrue($tx2->save());
        $tx2->makeChildOf($tx0);

        $tx1 = new Taxonomy(['name' => 'Test node 1', 'priority' => 1]);
        $this->assertTrue($tx1->save());
        $tx1->makeChildOf($tx0);

        $children = $tx0->getDescendants();
        $this->assertEquals('Test node 1', $children->toArray()[0]['name']);
    }

    /**
     * @test
     */
    function it_can_be_active() {
        $tx = new Taxonomy(['name' => 'Test node', 'is_active' => 1]);
        $this->assertTrue($tx->save());
        $this->assertEquals(1, $tx->is_active);
    }

    /**
     * @test
     */
    function it_can_be_required() {
        $tx = new Taxonomy(['name' => 'Test node', 'is_required' => 1]);
        $this->assertTrue($tx->save());
        $this->assertEquals(1, $tx->is_required);
    }

    /**
     * @test
     */
    function it_can_be_merchantable() {
        $tx = new Taxonomy(['name' => 'Test node', 'is_merchantable' => 1]);
        $this->assertTrue($tx->save());
        $this->assertEquals(1, $tx->is_merchantable);
    }

    /**
     * @test
     */
    function it_have_a_type() {
        $tx = new Taxonomy();
        $tx->name = 'Test node';
        $tx->type = Config::get('stylerstaxonomy.type_meta');
        $this->assertTrue($tx->save());

        $this->assertEquals(Config::get('stylerstaxonomy.type_meta'), $tx->type);
    }

    /**
     * @test
     */
    function it_can_not_set_is_readonly() {
        // is_readonly is guarded
        $tx = new Taxonomy(['name' => 'Test node', 'is_readonly' => true]);
        $this->assertTrue($tx->save());
        $this->assertArrayHasKey('is_readonly', $tx->getAttributes());
        $this->assertFalse($tx->is_readonly);
    }
    
    /**
     * @test
     */
    function it_can_set_searchable() {
        $tx = new Taxonomy();
        $tx->name = 'Test node';
        $this->assertTrue($tx->save());
        $this->assertEquals(false, $tx->is_searchable);
        
        $txSearchable = new Taxonomy();
        $txSearchable->name = 'Test node Searchable';
        $txSearchable->is_searchable = true;
        $this->assertTrue($txSearchable->save());
        $this->assertEquals(true, $txSearchable->is_searchable);
        
        $txSearchable->is_searchable = false;
        $this->assertTrue($txSearchable->save());
        $this->assertEquals(false, $txSearchable->is_searchable);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_get_or_create() {

        //get when exists
        $firstTx = Taxonomy::whereNotNull('parent_id')->first();
        $actual = Taxonomy::getOrCreateTaxonomy($firstTx->name, $firstTx->parent_id);
        $this->assertEquals($firstTx->id, $actual->id);

        //create when not exists
        $name = $this->faker->word;
        $actual = Taxonomy::getOrCreateTaxonomy($name, $firstTx->id);
        $this->assertGreaterThan(10000, $actual->id);
        $this->assertEquals($name, $actual->name);

    }

}
