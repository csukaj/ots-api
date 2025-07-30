<?php
namespace Tests\Procedure\Ots\PriceModifier;

use Tests\Procedure\ProcedureTestCase;

class PriceModifierCombinerTest extends ProcedureTestCase {
    
    static public $setupMode = self::SETUPMODE_NEVER;
    static private $imports = "from ots.price_modifier.price_modifier_combiner import PriceModifierCombiner\n";
    
    private function prepare($priceModifiers, $combinations) {
        $priceModifiersJson = \json_encode($priceModifiers);
        $combinationsJson = \json_encode($combinations);
        return [$priceModifiersJson, $combinationsJson];
    }
    
    private function compose($priceModifiers, $combinations) {
        list($priceModifiersJson, $combinationsJson) = $this->prepare($priceModifiers, $combinations);
        $script = self::$imports . "print(PriceModifierCombiner().combine({$priceModifiersJson}, {$combinationsJson}))";
        $result = $this->runPythonScript($script);
        $resultJson = \json_decode($result);
        return is_null($resultJson) ? $result : $resultJson;
    }
    
    /**
     * @test
     */
    public function it_works_for_one_priceModifier() {
        $result = $this->compose([1], []);
        $this->assertUnorderedMultidimensionalSetsEquals([[1]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_for_two_combinable_price_modifiers() {
        $result = $this->compose([1, 2], [[1, 2]]);
        $this->assertUnorderedMultidimensionalSetsEquals([[1, 2]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_for_two_non_combinable_price_modifiers() {
        $result = $this->compose([1, 2], []);
        $this->assertUnorderedMultidimensionalSetsEquals([[1], [2]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_for_three_partly_combinable_price_modifiers() {
        $result = $this->compose([1, 2, 3], [[1, 2]]);
        $this->assertUnorderedMultidimensionalSetsEquals([[1, 2], [3]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_for_three_mixed_combinable_price_modifiers() {
        $result = $this->compose([1, 2, 3], [[1, 2], [2, 3]]);
        $this->assertUnorderedMultidimensionalSetsEquals([[1, 2], [2, 3]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_for_three_combinable_price_modifiers() {
        $result = $this->compose([1, 2, 3], [[1, 2], [1, 3], [2, 3]]);
        $this->assertUnorderedMultidimensionalSetsEquals([[1, 2, 3]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_for_a_complex_combination() {
        $result = $this->compose([1, 2, 3, 4, 5, 6], [[1, 2], [1, 3], [2, 3], [4, 5]]);
        $this->assertUnorderedMultidimensionalSetsEquals([[1, 2, 3], [4, 5], [6]], $result);
    }
    
    /**
     * @test
     */
    public function it_works_when_a_price_modifier_is_missing() {
        $result = $this->compose([584, 583, 582], [[584, 582], [585, 582], [585, 584]]);
        $this->assertUnorderedMultidimensionalSetsEquals([[584, 582], [583]], $result);
    }
}