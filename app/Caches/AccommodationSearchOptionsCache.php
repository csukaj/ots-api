<?php
namespace App\Caches;

use App\Entities\AccommodationEntity;
use App\Facades\Config;
use App\HotelChain;
use App\Traits\RedisTrait;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class AccommodationSearchOptionsCache
{
    use RedisTrait;

    public function getValues()
    {
        return $this->getSelfUpdatedCacheResource(
                'accommodationSearchOptions',
                max(
                    strtotime(DB::table('descriptions')->max('updated_at')),
                    strtotime(DB::table('taxonomies')->max('updated_at'))
                ),
                'getFreshSearchOptions'
        );
    }

    protected function getFreshSearchOptions(): array
    {
        $txList = [];

        $emptyCategory = [
            'name' => [
                'en' => null
            ],
            'priority' => null,
            'items' => []
        ];

        $hardcodedTxIds = AccommodationEntity::getHardcodedSearchoptionTaxonomyIds();
        $hardcodedTxCollection = Taxonomy::findOrFail($hardcodedTxIds);
        $emptyCategory['items'] = array_merge(
            $emptyCategory['items'],
            TaxonomyEntity::getCollection(
                $hardcodedTxCollection, ['searchable_info', 'descendants']));

        $txList[] = $emptyCategory;

        /*
         * @todo @ivan @20190128 - Gergoek keresere kikapcsolva
        $categories = Config::get('taxonomies.organization_properties.categories');

        foreach ($categories as $category) {
            $categoryTx = Taxonomy::find($category['id']);
            $txData = (new TaxonomyEntity($categoryTx))->getFrontendData(['searchable_info']);
            $searchableChildren = $categoryTx->children()->searchable()->whereNotIn('id', $hardcodedTxIds)->get();
            $txData['items'] = TaxonomyEntity::getCollection($searchableChildren, ['searchable_info', 'descendants']);
            if (!empty($txData['items'])) {
                $txList[] = $txData;
            }
        }
        */

        return $txList;
    }
}
