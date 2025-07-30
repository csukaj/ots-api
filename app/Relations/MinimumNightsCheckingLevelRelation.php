<?php

namespace App\Relations;

use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * Relation for displaying Organization age range names
 */
class MinimumNightsCheckingLevelRelation extends Relation
{

    protected $type = self::TYPE_ONE_TO_ONE;
    protected $format = self::FORMAT_SINGLE_VALUE;

    /**
     * Format data for displaying on frontend
     *
     * @return array
     * @throws \Exception
     */
    public function getFrontendData()
    {
        $taxonomies = [];

        foreach (Config::getOrFail('taxonomies.minimum_nights_checking_levels') as $level) {
            $taxonomies[] = Taxonomy::findOrFail($level['id']);
        }

        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => TaxonomyEntity::getCollection($taxonomies, [], [], true)
        ];
    }
}
