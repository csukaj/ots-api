<?php
namespace Tests\Procedure\Stylers;

use Tests\Procedure\ProcedureTestCase;

class UnderscoreToCamelcaseTest extends ProcedureTestCase {
    
    static public $setupMode = self::SETUPMODE_NEVER;
    static private $imports = 'from stylers.utils import underscore_to_camelcase' . PHP_EOL;
    
    /**
     * @test
     */
    public function it_can_convert_a_multiword_string() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('this_is_a_test'))");
        $this->assertEquals('ThisIsATest', $result);
    }
    
    /**
     * @test
     */
    public function it_can_convert_a_single_word() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('test'))");
        $this->assertEquals('Test', $result);
    }
    
    /**
     * @test
     */
    public function it_works_with_double_underscore() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('test__test'))");
        $this->assertEquals('TestTest', $result);
    }
    
    /**
     * @test
     */
    public function it_works_with_numbers() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('test_2_test'))");
        $this->assertEquals('Test2Test', $result);
    }
    
    /**
     * @test
     */
    public function it_works_with_symbols() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('test_-/*_test'))");
        $this->assertEquals('Test-/*Test', $result);
    }
    
    /**
     * @test
     */
    public function it_works_with_spaces() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('test test'))");
        $this->assertEquals('Test Test', $result);
    }
    
    /**
     * @test
     */
    public function it_works_with_uppercase_characters() {
        $result = $this->runPythonScript(self::$imports . "print(underscore_to_camelcase('TEST'))");
        $this->assertEquals('Test', $result);
    }
}