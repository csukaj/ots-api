<?php

namespace App\Caches;

use App\Cruise;
use App\Organization;
use App\Traits\RedisTrait;

/**
 * App\Caches\CruiseIdsCache
 * 
 * Cache generator for cruises ids list using redis
 */
class CruiseIdsCache {
    use RedisTrait;
    
    /**
     * Get values. Either from cache or from fresh resource if needed
     * @return array
     */
    public function getValues() {
        return $this->getSelfUpdatedCacheResource(
            'cruiseIds',
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
        return Cruise::getIds();
    }
}