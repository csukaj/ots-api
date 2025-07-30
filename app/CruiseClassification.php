<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\CruiseClassification
 *
 * @property int $id
 * @property int $cruise_id
 * @property int $parent_classification_id
 * @property int $classification_taxonomy_id
 * @property int $value_taxonomy_id
 * @property int $priority
 * @property int $charge_taxonomy_id
 * @property int $additional_description_id
 * @property bool $is_highlighted
 * @property bool $is_listable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read Taxonomy $chargeTaxonomy
 * @property-read Collection|CruiseClassification[] $childClassifications
 * @property-read Collection|CruiseMeta[] $childMetas
 * @property-read Taxonomy $classificationTaxonomy
 * @property-read Cruise $cruise
 * @property-read CruiseClassification $parentClassification
 * @property-read Taxonomy $priceTaxonomy
 * @property-read Taxonomy $valueTaxonomy
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereChargeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereClassificationTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereCruiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseClassification whereValueTaxonomyId($value)
 */
class CruiseClassification extends ModelClassification
{

    protected $table = 'cruise_classifications';
    protected $classificationClass = self::class;
    protected $metaClass = CruiseMeta::class;
    protected static $foreignKey = 'cruise_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cruise_id',
        'parent_classification_id',
        'classification_taxonomy_id',
        'value_taxonomy_id',
        'priority',
        'charge_taxonomy_id',
        'additional_description_id',
        'is_highlighted',
        'is_listable'
    ];

    /**
     * Relation to cruise
     *
     * @return HasOne
     */
    public function cruise(): HasOne
    {
        return $this->hasOne(Cruise::class, 'id', self::$foreignKey);
    }

}
