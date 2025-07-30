<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Stylerstaxonomy\Entities\Classification
 *
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $additionalDescription
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $classificationTaxonomy
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $priceTaxonomy
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $value
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Classification onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Classification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Classification withoutTrashed()
 * @mixin \Eloquent
 */
class Classification extends Model
{

    use SoftDeletes;

    public function classificationTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'classification_taxonomy_id');
    }

    public function value(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'value_taxonomy_id');
    }

    public function priceTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'price_taxonomy_id');
    }

    public function additionalDescription(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }
}
