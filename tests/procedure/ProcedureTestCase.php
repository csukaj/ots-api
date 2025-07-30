<?php

namespace Tests\Procedure;

use Exception;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProcedureTestCase extends TestCase {

    /**
     * Runs a Python script with PlpyMocker and ModelFactory available
     * @param string $testScript
     * @return string
     */
    static public function runPythonScript($testScript) {
        $dir = realpath(dirname(__DIR__) . '/../');
        $connection = DB::connection();
        $headerScript = "
import sys
sys.path.append('{$dir}/database/procedures')
sys.path.append('{$dir}/database/tests')

from json import dumps

from stylers.plpy_mocker import PlpyMocker
plpy_mocker = PlpyMocker()
plpy_mocker.connect('{$connection->getConfig('host')}', '{$connection->getConfig('database')}', '{$connection->getConfig('username')}', '{$connection->getConfig('password')}')
    
from model_factory import ModelFactory
factory = ModelFactory(plpy_mocker)

import jsonpickle
from stylers.utils import pickle_resultset

        ";
        $footerScript = "
plpy_mocker.disconnect()
        ";

        $fileName = '/tmp/ots_test_'.rand(10000, 99999).'.py';
        file_put_contents($fileName, $headerScript . "\n" . $testScript . "\n" . $footerScript);

        $output = null;
        exec("python {$fileName} 2>&1", $output);

        unlink($fileName);

        return implode("\n", $output);
    }

    protected function composeKeywordArguments(array $arguments, $useKeywords = true) {
        $return = [];
        foreach ($arguments as $key => $value) {
            $keyString = ($useKeywords) ? "{$key}=" : '';
            if (is_string($value)) {
                $return[] = $keyString . "'" . stripslashes($value) . "'";
            } elseif (is_object($value) && $value instanceof ScriptContainer) {
                $return[] = $keyString . $value->get();
            } elseif (is_null($value)) {
                $return[] = $keyString . 'None';
            } else {
                $return[] = $keyString . $value;
            }
        }
        return implode(', ', $return);
    }

    protected function composeParams(array $arguments) {
        return $this->composeKeywordArguments($arguments, false);
    }

    public function runPythonAndDecodeJSON($script, $assoc = true, $useOwnDecoder = false) {
        $result = $this->runPythonScript($script);
        if ($result == '[]' || $assoc && $result == '{}') {
            return [];
        }
        if ($useOwnDecoder) {
            $decoded = $this->jsonDecode($result, $assoc);
        } else {
            $decoded = \json_decode($result, $assoc);
            if (\json_last_error() != JSON_ERROR_NONE) {
                throw new Exception('JSON error: ' . \json_last_error_msg() . PHP_EOL . $result);
            }
        }
        return $decoded;
    }

    protected function jsonDecode($pyJson, $assoc = false) {
        if ($pyJson == '') {
            return null;
        }
        $pyJson = str_replace('None', 'null', $pyJson);
        $pyJson = str_replace('True', 'true', $pyJson);
        $pyJson = str_replace('False', 'false', $pyJson);
        $pyJson = preg_replace_callback('/datetime\\.datetime\\((\\d+), (\\d+), (\\d+), (\\d+), (\\d+), (\\d+)\\)/', [$this, 'reformatPyDate'], $pyJson);
        $pyJson = preg_replace_callback('/datetime\\.datetime\\((\\d+), (\\d+), (\\d+), (\\d+), (\\d+)\\)/', [$this, 'reformatPyDate'], $pyJson);
        $pyJson = preg_replace_callback('/"([^"]+)"/', [$this, 'protectString'], $pyJson);
        $pyJson = preg_replace('/(?<!\\\\)\'/', '"', $pyJson);
        $pyJson = preg_replace('/u"(?=[^:]+")/', '"', $pyJson);
        $pyJson = str_replace('#STR_PROTECT#', "'", $pyJson);
        $pyJson = str_replace('\\x', '\\\\x', $pyJson);
        $return = \json_decode($pyJson, $assoc);
        $error = \json_last_error();
        if ($error != JSON_ERROR_NONE) {
            throw new Exception('JSON error: ' . \json_last_error_msg() . PHP_EOL . $pyJson);
        }
        return $return;
    }

    protected function reformatPyDate($matches) {
        $date = sprintf('%04d', $matches[1]) . '-' . sprintf('%02d', $matches[2]) . '-' . sprintf('%02d', $matches[3]);
        $time = sprintf('%02d', $matches[4]) . ':' . sprintf('%02d', $matches[5]) . ':' . sprintf('%02d', isset($matches[6])?$matches[6]:0);
        return "'$date $time'";
    }

    protected function protectString($matches) {
        return '"' . str_replace("'", '#STR_PROTECT#', $matches[1]) . '"';
    }

    protected function scriptContainer($script) {
        return new ScriptContainer($script);
    }

}

class ScriptContainer {

    private $script;

    public function __construct($script) {
        $this->script = $script;
    }

    public function get() {
        return $this->script;
    }

}
