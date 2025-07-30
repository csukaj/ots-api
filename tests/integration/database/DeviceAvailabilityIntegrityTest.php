<?php

namespace Tests\Integration\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeviceAvailabilityIntegrityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    public function amounts_are_consistent() {
        $inconsistencies = DB::select(
            'SELECT availabilities.*, devices.amount
            FROM availabilities
            INNER JOIN devices ON availabilities.available_type = \'App\\Device\' AND available_id = devices.id
            INNER JOIN organizations ON devices.deviceable_id = organizations.id AND deviceable_type = \'App\\Organization\'
            INNER JOIN organization_classifications ON organizations.id = organization_classifications.organization_id
            WHERE
              organization_classifications.classification_taxonomy_id = 53 AND
              organization_classifications.value_taxonomy_id = 54 AND
              availabilities.amount NOT IN(0, devices.amount)'
        );
        $this->assertEmpty($inconsistencies);
    }
}
