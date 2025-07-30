<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcedureSeeder extends Seeder
{
    private $procedureDirectory = 'database/procedures/ots/';
    private $procedureFiles = [
        'get_result_rooms.py',
        'get_result_charters.py',
        'get_result_cruises.py'
    ];
    private $moduleDirectories = [
        'database/procedures',
        'database/tests'
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::statement("CREATE OR REPLACE PROCEDURAL LANGUAGE 'plpythonu' HANDLER plpython_call_handler");

        foreach ($this->procedureFiles as $procedureFile) {
            DB::statement($this->parsePythonFileWithSqlBlocks($this->procedureDirectory.$procedureFile));
        }
    }

    /**
     * Parses a Python file with create SQL statements in comment blocks
     * @param string $filename
     * @return string
     */
    private function parsePythonFileWithSqlBlocks($filename) {
        $sqlBlock = false;
        $return = '';

        $pythonfile = file_get_contents($filename);
        $file = str_replace(array("\r", "\n\n"), array("\n", "\n"), $pythonfile); //normalize every line ending to linux.
        foreach (explode("\n", $file) as $line) {
            // Line with three leading hashes: start an SQL block, ignore this line
            if (substr($line, 0, 3) === '###') {
                $sqlBlock = true;
                continue;
            }
            // While in SQL block: add modules, strip a single leading hash
            if ($sqlBlock) {
                if ($line == "#@modules") {
                    $return .= $this->getModulePaths()."\n";
                    continue;
                } else if (substr($line, 0, 1) === '#') {
                    $return .= substr($line, 1)."\n";
                    continue;
                }
            }
            // No leading hash or not in SQL block: relay line without changes
            $sqlBlock = false;
            $return .= $line."\n";
        }

        return $return;
    }

    private function getModulePaths() {
        $return = "from sys import path\n";
        foreach ($this->moduleDirectories as $moduleDirectory) {
            $realPath = realpath(dirname(__FILE__) . '/../../' . $moduleDirectory);
            $return .= "path.append('{$realPath}')\n";
        }
        return $return;
    }
}