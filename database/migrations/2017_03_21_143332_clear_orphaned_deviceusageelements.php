<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ClearOrphanedDeviceusageelements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // delete orphraned usage elements
        DB::statement("
UPDATE device_usage_elements
SET deleted_at = now()
WHERE
  device_usage_id IN (
    SELECT device_usages.id
    FROM device_usages
    WHERE device_usages.deleted_at IS NOT NULL
  )
  AND deleted_at IS NULL
        ");
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
