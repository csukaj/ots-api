<?php

namespace App\Caches;

use App\Entities\ShipGroupEntity;
use App\Facades\Config;
use App\Traits\PredefinedFilterTrait;
use App\Traits\RedisTrait;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class CruiseSearchOptionsCache
{
    use RedisTrait;
    use PredefinedFilterTrait;

    public function __construct()
    {
        //
    }

    public function getValues()
    {
        return $this->getSelfUpdatedCacheResource(
            'cruiseSearchOptions',
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
        $hardcodedTxIds = ShipGroupEntity::getHardcodedSearchoptionTaxonomyIds();
        $hardcodedTxCollection = Taxonomy::findOrFail($hardcodedTxIds);

        $emptyCategory = [
            'name' => ['en' => null],
            'priority' => null,
            'items' => [
                $this->getPredefinedFilter('nights')
            ]
        ];
        $emptyCategory['items'] = array_merge(
            $emptyCategory['items'],
            TaxonomyEntity::getCollection($hardcodedTxCollection, ['searchable_info', 'descendants'])
        );
        $txList[] = $emptyCategory;

        return $txList;
    }

}
