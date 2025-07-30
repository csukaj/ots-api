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
 * App\PriceModifierMeta
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
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierMeta onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta wherePriceModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierMeta whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierMeta withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierMeta withoutTrashed()
 */
class PriceModifierMeta extends Model
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
     * Relation to price modifier
     *
     * @return HasOne
     */
    public function priceModifier(): HasOne
    {
        return $this->hasOne(PriceModifier::class, 'id', 'price_modifier_id');
    }

    /**
     * Relation to taxonomy
     *
     * @return HasOne
     */
    public function taxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

}
