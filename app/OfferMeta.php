<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\MetaTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OfferMeta
 *
 * @property int $id
 * @property int $price_modifier_id
 * @property int $taxonomy_id
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read PriceModifier $priceModifier
 * @property-read Taxonomy $metaTaxonomy
 * @property-read Taxonomy $taxonomy
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OfferMeta onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta wherePriceModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfferMeta whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OfferMeta withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OfferMeta withoutTrashed()
 */
class OfferMeta extends Model
{

    use SoftDeletes,
        MetaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['price_modifier_id', 'taxonomy_id', 'value'];

    /**
     * Relation to Price Modifier
     *
     * @return HasOne
     */
    public function priceModifier(): HasOne
    {
        return $this->hasOne(PriceModifier::class);
    }

    /**
     * Relation to taxonomy
     *
     * @return HasOne
     */
    public function taxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class);
    }

}
