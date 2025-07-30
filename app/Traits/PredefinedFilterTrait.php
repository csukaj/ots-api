<?php

namespace App\Traits;

use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * This trait is used for predefined filters in SearchOptionsCache classes
 */
trait PredefinedFilterTrait {

    public function getPredefinedFilter($configKey): array
    {
        $config = Config::getOrFail("taxonomies.predefined_filters.{$configKey}");
        $parentOption = [
            'name' => (new TaxonomyEntity(Taxonomy::findOrFail($config['id'])))->getFrontendData(['translations'])['translations'],
            'priority' => 1,
            'descendants' => []
        ];
        foreach ($config['elements'] as $key => $item) {
            $parentOption['descendants'][] = [
                'id' => $key,
                'name' => (new TaxonomyEntity(Taxonomy::findOrFail($item['id'])))->getFrontendData(['translations'])['translations'],
                'priority' => null,
                'descendants' => []
            ];
        }
        return $parentOption;
    }

}