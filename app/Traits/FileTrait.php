<?php

namespace App\Traits;

use App\Exceptions\UserException;
use App\Facades\Config;

trait FileTrait
{

    /**
     * Save data to cacha files to all cache directories
     *
     * @param string $filename
     * @param mixed $data
     * @throws UserException on file save error
     */
    static protected function saveToCacheFiles(string $filename, $data)
    {
        foreach (Config::getOrFail('cache.frontend_cache_directories') as $directory) {
            $path = base_path("{$directory}/{$filename}.ts");
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $content = "const {$filename} = {$json};\nexport default {$filename};\n";
            if (!file_put_contents($path, $content)) {
                throw new UserException('Cannot write cache file!');
            }
        }
    }

}