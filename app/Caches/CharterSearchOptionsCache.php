<?php

namespace App\Caches;

use App\Entities\ShipGroupEntity;
use App\Facades\Config;
use App\Traits\PredefinedFilterTrait;
use App\Traits\RedisTrait;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class CharterSearchOptionsCache
{
    use RedisTrait;
    use PredefinedFilterTrait;

    private $uniqueSearchOptionTaxonomyIds = [];

    /**
     * CharterSearchOptionsCache constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->uniqueSearchOptionTaxonomyIds = [
            Config::getOrFail('taxonomies.organization_group_properties.categories.general.items.ship_group_category.id'),
            Config::getOrFail('taxonomies.organization_group_properties.categories.general.items.propulsion.id')
        ];
    }

    public function getValues()
    {
        return $this->getSelfUpdatedCacheResource(
            'charterSearchOptions',
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
                $this->getPredefinedFilter('ship_length')
            ]
        ];
        $emptyCategory['items'] = array_merge(
            $emptyCategory['items'],
            TaxonomyEntity::getCollection($hardcodedTxCollection, ['searchable_info', 'descendants'])
        );
        $txList[] = $emptyCategory;

        $categories = Config::get('taxonomies.organization_group_properties.categories');
        foreach ($categories as $category) {
            $categoryTx = Taxonomy::find($category['id']);
            $txData = (new TaxonomyEntity($categoryTx))->getFrontendData(['searchable_info']);
            $searchableChildren = $categoryTx->children()->searchable()->whereNotIn('id', $hardcodedTxIds)->get();
            $txData['items'] = TaxonomyEntity::getCollection($searchableChildren, ['searchable_info', 'descendants']);
            if (!empty($txData['items'])) {
                $txList[] = $txData;
            }
        }

        return $txList;
    }

}
