<?php

namespace App\Caches;

use App\Accommodation;
use App\Organization;
use App\Traits\RedisTrait;
use Modules\Stylerstaxonomy\Entities\Language;

/**
 * App\Caches\AccommodationSearchableTextsCache
 * 
 * Cache generator for searchable text list for accommodations search using redis
 */
class AccommodationSearchableTextsCache {

    use RedisTrait;

    /**
     * Get values. Either from cache or from fresh resource if needed
     * @return array
     */
    public function getValues() {
        return $this->getSelfUpdatedCacheResource(
            'accommodationSearchableTexts', Organization::getLastNameUpdateTimestamp(), 'getFreshSearchableTexts'
        );
    }

    /**
     * Seeder method for searchable texts cache
     * 
     * Get existing accommodations names, 
     * fill missing translations with english (default) values
     * and publicate to cache
     * 
     * @return array
     */
    protected function getFreshSearchableTexts() {
        $data = [];

        $languageCodes = array_keys(Language::getLanguageCodes());

        // get existing accommodations names
        $accommodationNames = [];

        foreach (Accommodation::getNames() as $accommodationData) {
            if (!isset($accommodationNames[$accommodationData->id])) {
                $accommodationNames[$accommodationData->id] = [];
            }
            $accommodationNames[$accommodationData->id][$accommodationData->language] = $accommodationData->description;
        }

        // fill missing translations with english (default) values
        foreach ($accommodationNames as $accommodationId => $accommodationData) {
            foreach ($languageCodes as $lang) {
                if (!isset($accommodationData[$lang])) {
                    $accommodationNames[$accommodationId][$lang] = $accommodationNames[$accommodationId][Language::getDefault()->iso_code];
                }
            }
        }

        foreach ($accommodationNames as $accommodationId => $accommodationData) {
            $data[]=['name'=>$accommodationData, 'accommodations'=>[$accommodationId]];
        }

        return $data;
    }

}
