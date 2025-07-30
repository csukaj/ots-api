<?php

namespace App\Caches;

use App\Charter;
use App\Organization;
use App\Traits\RedisTrait;

/**
 * App\Caches\CharterIdsCache
 * 
 * Cache generator for charters ids list using redis
 */
class CharterIdsCache {
    use RedisTrait;
    
    /**
     * Get values. Either from cache or from fresh resource if needed
     * @return array
     */
    public function getValues() {
        return $this->getSelfUpdatedCacheResource(
            'charterIds',
            strtotime(Organization::getLastUpdate()),
            'getFreshIds'
        );
    }

    /**
     * Seeder method for cache values
     * 
     * @return array
     */
    protected function getFreshIds() {
        return Charter::getIds();
    }
}