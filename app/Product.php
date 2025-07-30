<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Product
 *
 * @property int $id
 * @property int $productable_id
 * @property string $productable_type
 * @property int $type_taxonomy_id
 * @property int $margin_type_taxonomy_id
 * @property float $margin_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $name_description_id
 * @property-read Taxonomy $marginType
 * @property-read Description $name
 * @property-read Collection|Price[] $prices
 * @property-read Model\Eloquent $productable
 * @property-read Taxonomy $type
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Fee[] $fees
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product forProductable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Product onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereProductableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereProductableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Product withoutTrashed()
 */
class Product extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_description_id',
        'productable_id',
        'productable_type',
        'type_taxonomy_id',
        'margin_type_taxonomy_id',
        'margin_value'
    ];

    protected $cascadeDeletes = ['prices','fees','name'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['productable'];

    /**
     * productable
     * function to construct morphto relation
     *
     * @return MorphTo
     */
    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * type
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    /**
     * marginType
     * Relation to Margin type taxonomy
     *
     * @return HasOne
     */
    public function marginType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
    }

    /**
     * type
     * Relation to Price
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'product_id', 'id')->orderBy('extra', 'asc')->orderBy('amount', 'asc');
    }

    /**
     * type
     * Relation to Fee
     *
     * @return HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'product_id', 'id');
    }

    /**
     * type
     * Relation to name description
     *
     * @return HasOne
     */
    public function name(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'name_description_id');
    }

    /**
     * Scope a query to only include products for a specific type.
     *
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForProductable(Builder $query, string $type, int $id): Builder
    {
        return $query
            ->where('productable_type', $type)
            ->where('productable_id', $id);
    }

    /**
     *
     * @param string $name Product name o find
     * @param string $productableType Class name of productable object
     * @param int $productableId Id of productable object
     * @return Product | null
     * @static
     */
    static public function findByName(string $name, string $productableType, int $productableId)
    {
        return Product
            ::join('descriptions', 'products.name_description_id', '=', 'descriptions.id')
            ->where('descriptions.description', '=', $name)
            ->where('products.productable_type', '=', $productableType)
            ->where('products.productable_id', '=', $productableId)
            ->whereNull('descriptions.deleted_at')
            ->first();
    }
}
