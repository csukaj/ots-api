<?php

namespace Tests\Procedure\Ots\Common;

use Tests\Procedure\ProcedureTestCase;

class UsageRequestHandlerTest extends ProcedureTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.common.usage_request_handler import UsageRequestHandler' . PHP_EOL;

    private function prepareAndRun($script) {
        return $this->runPythonScript(
            self::$imports .
            "usage_request_handler = UsageRequestHandler(plpy_mocker, 'App\\Organization', 1)" .
            PHP_EOL .
            $script
        );
    }

    /**
     * @test
     */
    public function it_can_init_itself() {
        $this->assertEquals('', $this->prepareAndRun(''));
    }

    /**
     * @test
     */
    public function it_can_load_age_ranges() {
        $actual = $this->jsonDecode(
            $this->prepareAndRun(
                'print usage_request_handler.named_age_ranges'
            ),
            true
        );
        $check = [
            'baby' => [
                'name' => 'baby',
                'to_age' => 2,
                'from_age' => 0
            ],
            'adult' => [
                'name' => 'adult',
                'to_age' => NULL,
                'from_age' => 7
            ],
            'child' => [
                'name' => 'child',
                'to_age' => 6,
                'from_age' => 3
            ],
        ];
        $this->assertEquals(count($check), count($actual));
        foreach ($check as $key => $value) {
            $this->assertEquals([], array_diff_assoc($value, $actual[$key]));
        }
    }

    /**
     * @test
     */
    public function it_can_set_request() {
        $actual = $this->jsonDecode(
            $this->prepareAndRun(
                'usage_request_handler.set_request(factory.request(json=False))' . PHP_EOL .
                'print usage_request_handler.request'
            ),
            true
        );
        $check = [
            [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ]
                ]
            ]
        ];
        $this->assertEquals($check, $actual);
    }

}
