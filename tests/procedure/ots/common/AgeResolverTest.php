<?php

namespace Tests\Procedure\Ots\Common;

use Tests\Procedure\ProcedureTestCase;

class AgeResolverTest extends ProcedureTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.common.age_resolver import AgeResolver' . PHP_EOL;

    private function prepareAndRun($organizationId, $script) {
        return $this->runPythonScript(self::$imports . "age_resolver = AgeResolver(plpy_mocker,'App\\Organization', {$organizationId})" . PHP_EOL . $script);
    }

    /**
     * @test
     */
    public function it_can_resolve_room_usage() {
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 1}])'),
            true
        );
        $expected = ['adult' => 1];
        $this->assertEquals($expected, $actual);
    
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 2}])'),
            true
        );
        $expected = ['adult' => 2];
        $this->assertEquals($expected, $actual);
        
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 1}, {"age": 21, "amount": 1}])'),
            true
        );
        $expected = ['adult' => 2];
        $this->assertEquals($expected, $actual);
        
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 6, "amount": 1}])'),
            true
        );
        $expected = ['child' => 1];
        $this->assertEquals($expected, $actual);
    
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 1}, {"age": 6, "amount": 1}])'),
            true
        );
        $expected = ['adult' => 1, 'child' => 1];
        $this->assertEquals($expected, $actual);
    
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 1}, {"age": 6, "amount": 1}, {"age": 3, "amount": 1}])'),
            true
        );
        $expected = ['adult' => 1, 'child' => 2];
        $this->assertEquals($expected, $actual);
    
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 1}, {"age": 6, "amount": 1}, {"age": 1, "amount": 1}])'),
            true
        );
        $expected = ['adult' => 1, 'child' => 1, 'baby' => 1];
        $this->assertEquals($expected, $actual);
    
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.resolve_room_usage([{"age": 21, "amount": 1}, {"age": 6, "amount": 1}, {"age": 1, "amount": 1}, {"age": 0, "amount": 1}])'),
            true
        );
        $expected = ['adult' => 1, 'child' => 1, 'baby' => 2];
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @test
     */
    public function it_can_get_age_ranges_dict() {
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1, 'print age_resolver.get_age_ranges_dict()'),
            true
        );
        $expected = [
            'baby' => [
                'name' => 'baby',
                'from_age' => 0,
                'to_age' => 2
            ],
            'child' => [
                'name' => 'child',
                'from_age' => 3,
                'to_age' => 6
            ],
            'adult' => [
                'name' => 'adult',
                'from_age' => 7,
                'to_age' => null
            ]
        ];
        $this->assertEquals(3, count($actual));
        foreach ($expected as $key => $value) {
            $this->assertTrue(isset($actual[$key]));
            $this->assertEquals([], array_diff_assoc($expected[$key], $actual[$key]));
        }
    }

}
