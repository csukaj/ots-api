<?php
namespace Tests\Procedure\Stylers\DateHelpers;

use Tests\Procedure\ProcedureTestCase;

class SegmentedNightsTest extends ProcedureTestCase {
    
    static public $setupMode = self::SETUPMODE_NEVER;
    static private $imports = "from stylers.date_helpers import segmented_nights\n";
    
    /**
     * @test
     */
    public function it_can_find_a_non_overlap() {
        $result = $this->runPythonScript(self::$imports . "print(segmented_nights('2026-06-30', '2026-07-01', '2026-06-29', '2026-06-30'))");
        $this->assertEquals(0, $result);
    }
    
    /**
     * @test
     */
    public function it_can_find_inclusive_overlap() {
        $result = $this->runPythonScript(self::$imports . "print(segmented_nights('2026-06-25', '2026-07-01', '2026-06-26', '2026-06-29'))");
        $this->assertEquals(3, $result);
    }
    
    /**
     * @test
     */
    public function it_can_find_partial_overlap() {
        $result = $this->runPythonScript(self::$imports . "print(segmented_nights('2026-06-26', '2026-06-29', '2026-06-25', '2026-07-01'))");
        $this->assertEquals(3, $result);
    }
    
    /**
     * @test
     */
    public function it_can_find_overlap_at_beginning() {
        $result = $this->runPythonScript(self::$imports . "print(segmented_nights('2026-06-25', '2026-07-01', '2026-06-26', '2026-07-15'))");
        $this->assertEquals(5, $result);
    }
    
    /**
     * @test
     */
    public function it_can_find_overlap_at_end() {
        $result = $this->runPythonScript(self::$imports . "print(segmented_nights('2026-06-26', '2026-07-15', '2026-06-25', '2026-07-01'))");
        $this->assertEquals(5, $result);
    }
    
    /**
     * @test
     */
    public function it_works_when_first_range_has_open_ending() {
        $result = $this->runPythonScript(
            self::$imports . "print(segmented_nights('2026-06-26', '2026-06-26', '2026-06-25', '2026-07-01', first_range_has_open_ending=True))"
        );
        $this->assertEquals(1, $result);
        
        $result = $this->runPythonScript(
            self::$imports . "print(segmented_nights('2026-06-25', '2026-07-01', '2026-06-26', '2026-06-26', first_range_has_open_ending=True))"
        );
        $this->assertEquals(0, $result);
    }
    
    /**
     * @test
     */
    public function it_works_when_second_range_has_open_ending() {
        $result = $this->runPythonScript(
            self::$imports . "print(segmented_nights('2026-06-26', '2026-06-26', '2026-06-25', '2026-07-01', second_range_has_open_ending=True))"
        );
        $this->assertEquals(0, $result);
        
        $result = $this->runPythonScript(
            self::$imports . "print(segmented_nights('2026-06-25', '2026-07-01', '2026-06-26', '2026-06-26', second_range_has_open_ending=True))"
        );
        $this->assertEquals(1, $result);
    }
    
    /**
     * @test
     */
    public function it_works_when_both_ranges_have_open_endings() {
        $result = $this->runPythonScript(
            self::$imports . "print(segmented_nights('2026-06-26', '2026-06-26', '2026-06-25', '2026-07-01', first_range_has_open_ending=True, second_range_has_open_ending=True))"
        );
        $this->assertEquals(1, $result);
        
        $result = $this->runPythonScript(
            self::$imports . "print(segmented_nights('2026-06-25', '2026-07-01', '2026-06-26', '2026-06-26', first_range_has_open_ending=True, second_range_has_open_ending=True))"
        );
        $this->assertEquals(1, $result);
    }
}