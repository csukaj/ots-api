<?php

namespace App\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HardcodedIdSetterTrait
{

    public static function updateAutoIncrement(Model $model)
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        if ($driver != 'pgsql') {
            return;
        }
        $tableName = $model->getTable();
        DB::statement('SELECT setval(\'' . $tableName . '_id_seq\', (SELECT GREATEST((SELECT MAX(id) from "' . $tableName . '"),(SELECT nextval(\'' . $tableName . '_id_seq\')-1))))');
    }

}