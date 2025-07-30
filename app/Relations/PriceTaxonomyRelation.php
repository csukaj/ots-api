<?php
namespace App\Relations;

use App\Price;
use App\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * Relation for displaying Price Name Taxonomies
 */
class PriceTaxonomyRelation extends Relation
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
        $ageRangeIds = $this->model->ageRanges()->get()->pluck('id');
        $nameIds = Price::whereIn('age_range_id',$ageRangeIds)->get()->pluck('name_taxonomy_id')->unique()->values()->all();
        //throw new \App\Exceptions\UserException(json_encode($nameIds));
        $taxonomies = Taxonomy::find($nameIds);
        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => TaxonomyEntity::getCollection($taxonomies, [], [], true)
        ];
    }
}
