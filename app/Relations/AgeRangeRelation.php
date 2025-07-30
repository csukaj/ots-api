<?php
namespace App\Relations;

use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * Relation for displaying Organization age range names
 */
class AgeRangeRelation extends Relation
{

    protected $type = self::TYPE_ONE_TO_MANY_KEYS;
    protected $format = self::FORMAT_JSON;
    protected $model;

    public function __construct(Taxonomy $taxonomy, Model $model)
    {
        parent::__construct($taxonomy);
        $this->model = $model;
    }

    /**
     * Format data for displaying on frontend
     * 
     * @return array
     */
    public function getFrontendData()
    {
        if ($this->model->ageRanges) {
            $ageRanges = $this->model->ageRanges()->orderBy('from_age', 'desc')->get();
            $nameIds = $ageRanges->pluck('name_taxonomy_id')->unique()->values()->all();
            $taxonomies = Taxonomy::find($nameIds);
        } else {
            $taxonomies = [];
        }

        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => TaxonomyEntity::getCollection($taxonomies, [], [], true)
        ];
    }
}
