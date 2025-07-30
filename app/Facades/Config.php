<?php

namespace App\Facades;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Facade;

/**
 * Facade to extend default Laravel Config
 * @see Repository
 */
class Config extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'config';
    }

    /**
     * Get a config value. Throws exception if config key not found.
     *
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    static public function getOrFail($key)
    {
        if (!self::has($key)) {
            throw new Exception("Missing configuration key: `{$key}`");
        }
        return self::get($key);
    }

    /**
     * Get a config value from specified parent tree.
     *
     * @param string $baseKey basename of searched config key
     * @param string $parentKey Key of parent where $baseKey is searched
     * @param null $default
     * @return mixed
     */
    static public function getChild(string $baseKey, string $parentKey, $default = null)
    {
        return self::get("${parentKey}.${baseKey}", $default);
    }

}
