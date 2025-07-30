<?php
namespace App\Traits;

use Illuminate\Support\Facades\Redis;

/**
 * Trait to add redis specific functions
 */
trait RedisTrait {
    
    /**
     * Get resource from redis if exists and not expired.
     * Otherwise sets it
     * 
     * @param string $label
     * @param int $lastUpdatedTimestamp
     * @param string $seederMethodName
     * @return mixed
     */
    public function getSelfUpdatedCacheResource($label, $lastUpdatedTimestamp, string $seederMethodName) {
        $lastResourceName = Redis::get("{$label}:resourceName");
        $freshResourceName = "{$label}:{$lastUpdatedTimestamp}";
        
        if ($lastResourceName != $freshResourceName) {
            Redis::set($freshResourceName, json_encode($this->$seederMethodName()));
            Redis::del($lastResourceName);
            Redis::set("{$label}:resourceName", $freshResourceName);
        }
        
        return json_decode(Redis::get($freshResourceName));
    }
}