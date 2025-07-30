<?php
namespace Tests\Procedure\Stylers\DateHelpers;

use Tests\Procedure\ProcedureTestCase;

class MonthAndDayInRangeTest extends ProcedureTestCase {
    
    static public $setupMode = self::SETUPMODE_NEVER;
    static private $imports = "from stylers.date_helpers import month_and_day_in_range\nfrom datetime import datetime\n";
    
    /**
     * @test
     */
    public function it_can_accept_various_input_formats() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-06-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range(datetime.strptime('2026-06-15 00:00:00','%Y-%m-%d %H:%M:%S'), {'from_time':datetime.strptime('2026-06-01 00:00:00','%Y-%m-%d %H:%M:%S'),'to_time':datetime.strptime('2026-06-30 23:59:59','%Y-%m-%d %H:%M:%S')}))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range(datetime.strptime('2026-06-15 00:00:00','%Y-%m-%d %H:%M:%S').date(), {'from_time':datetime.strptime('2026-06-01 00:00:00','%Y-%m-%d %H:%M:%S').date(),'to_time':datetime.strptime('2026-06-30 23:59:59','%Y-%m-%d %H:%M:%S').date()}))");
        $this->assertEquals('True', $result);
    }

    /**
     * @test
     */
    public function it_can_find_out_of_range_situation() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-05-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('False', $result);
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-07-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('False', $result);

        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2027-05-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('False', $result);
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2027-07-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('False', $result);
    }

    /**
     * @test
     */
    public function it_can_find_normal_inclusion_in_same_year() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-06-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);
    }
    
    /**
     * @test
     */
    public function it_can_find_inclusion_on_border_in_same_year() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-06-01', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-06-30', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);
    }
    
    /**
     * @test
     */
    public function it_can_find_inclusion_when_range_has_year_leap_in_same_year() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2026-12-30', {'from_time':'2026-12-20 00:00:00','to_time':'2027-01-05 23:59:59'}))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2027-01-02', {'from_time':'2026-12-20 00:00:00','to_time':'2027-01-05 23:59:59'}))");
        $this->assertEquals('True', $result);
    }

    /**
     * @test
     */
    public function it_can_find_normal_inclusion_in_different_year() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2028-06-15', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);
    }

    /**
     * @test
     */
    public function it_can_find_inclusion_on_border_in_different_year() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2028-06-01', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2028-06-30', {'from_time':'2026-06-01 00:00:00','to_time':'2026-06-30 23:59:59'}))");
        $this->assertEquals('True', $result);
    }

    /**
     * @test
     */
    public function it_can_find_inclusion_when_range_has_year_leap_in_different_year() {
        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2028-12-30', {'from_time':'2026-12-20 00:00:00','to_time':'2027-01-05 23:59:59'}))");
        $this->assertEquals('True', $result);

        $result = $this->runPythonScript(self::$imports . "print(month_and_day_in_range('2028-01-02', {'from_time':'2026-12-20 00:00:00','to_time':'2027-01-05 23:59:59'}))");
        $this->assertEquals('True', $result);
    }
    

}