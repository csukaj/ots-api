<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ClearInvalidAmountsFromDeviceAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            UPDATE device_availabilities
            SET amount = (SELECT devices.amount FROM devices WHERE devices.id = device_availabilities.device_id)
            WHERE
                device_id IN (
                    SELECT devices.id
                    FROM devices
                        INNER JOIN organizations ON devices.organization_id = organizations.id
                        INNER JOIN organization_classifications ON organizations.id = organization_classifications.organization_id
                    WHERE
                        organization_classifications.classification_taxonomy_id = 53 AND
                        organization_classifications.value_taxonomy_id = 54
                )
                AND
                device_availabilities.amount NOT IN(
                    0,
                    (SELECT devices.amount FROM devices WHERE devices.id = device_availabilities.device_id)
                )
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
