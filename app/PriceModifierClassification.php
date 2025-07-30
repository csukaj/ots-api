<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\ClassificationTrait;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\PriceModifierClassification
 *
 * @property int $id
 * @property int $price_modifier_id
 * @property int $classification_taxonomy_id
 * @property int $value_taxonomy_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read Taxonomy $classificationTaxonomy
 * @property-read PriceModifier $priceModifier
 * @property-read Taxonomy $priceTaxonomy
 * @property-read Taxonomy $valueTaxonomy
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierClassification onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification whereClassificationTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification wherePriceModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierClassification whereValueTaxonomyId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierClassification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierClassification withoutTrashed()
 */
class PriceModifierClassification extends Model
{

    use SoftDeletes,
        ClassificationTrait;

    protected $table = 'price_modifier_classifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['classification_taxonomy_id', 'value_taxonomy_id', 'price_modifier_id'];

    /**
     * Relation to Price Modifier
     *
     * @return HasOne
     */
    public function priceModifier(): HasOne
    {
        return $this->hasOne(PriceModifier::class, 'id', 'price_modifier_id');
    }

}
