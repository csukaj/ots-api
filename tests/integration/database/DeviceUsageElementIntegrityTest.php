<?php

namespace Tests\Integration\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeviceUsageElementIntegrityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    public function elements_are_consistent() {
        $inconsistencies = DB::select(
            'SELECT device_usage_elements.*
FROM device_usage_elements
WHERE
  device_usage_id IN (
    SELECT device_usages.id
    FROM device_usages
    WHERE device_usages.deleted_at IS NOT NULL
  )
  AND deleted_at IS NULL'
        );
        $this->assertEmpty($inconsistencies);
    }
}
