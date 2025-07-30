<?php

namespace App;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Fee
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $age_range_id
 * @property float|null $net_price
 * @property float|null $rack_price
 * @property int|null $margin_type_taxonomy_id
 * @property float|null $margin_value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\AgeRange $ageRange
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $marginType
 * @property-read \App\Product $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Fee onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereAgeRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereNetPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereRackPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Fee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Fee withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Fee withoutTrashed()
 * @mixin \Eloquent
 */
class Fee extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'age_range_id',
        'net_price',
        'rack_price',
        'margin_type_taxonomy_id',
        'margin_value'
    ];

    /**
     * Relation to product
     *
     * @return HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    /**
     * Relation to ageRange
     *
     * @return HasOne
     */
    public function ageRange(): HasOne
    {
        return $this->hasOne(AgeRange::class, 'id', 'age_range_id');
    }

    /**
     * Relation to margin Type taxonomy
     *
     * @return HasOne
     */
    public function marginType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
    }
}
