<?php
namespace Tests\Procedure\Stylers;

use Tests\Procedure\ProcedureTestCase;

class PlpyMockerTest extends ProcedureTestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    public function it_can_connect() {
        $result = $this->runPythonScript("");
        $this->assertEquals('', $result);
    }
    
    /**
     * @test
     */
    public function it_can_execute_a_query() {
        $result = $this->runPythonScript("print(plpy_mocker.execute('SELECT id FROM users'))");
        $this->assertEquals("[{'id': 1}, {'id': 2}, {'id': 3}, {'id': 4}]", $result);
    }
}