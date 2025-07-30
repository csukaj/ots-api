<?php

namespace App\Caches;

use App\Accommodation;
use App\Organization;
use App\Traits\RedisTrait;

/**
 * App\Caches\AccommodationIdsCache
 * 
 * Cache generator for accommodations ids list using redis
 */
class AccommodationIdsCache {
    use RedisTrait;
    
    /**
     * Get values. Either from cache or from fresh resource if needed
     * @return array
     */
    public function getValues() {
        return $this->getSelfUpdatedCacheResource(
            'accommodationIds',
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
        return Accommodation::getIds();
    }
}