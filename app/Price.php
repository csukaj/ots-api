<?php

namespace App;

use App\Exceptions\UserException;
use App\Traits\ModelTrait;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Price
 *
 * @property int $id
 * @property int $name_taxonomy_id
 * @property int $product_id
 * @property int $age_range_id
 * @property int $amount
 * @property bool $extra
 * @property int $margin_type_taxonomy_id
 * @property float $margin_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property bool $mandatory
 * @property-read Collection|PriceElement[] $elements
 * @property-read Taxonomy $marginType
 * @property-read Taxonomy $name
 * @property-read AgeRange $ageRange
 * @property-read Product $product
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Price onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereAgeRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereMandatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereNameTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Price whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Price withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Price withoutTrashed()
 */
class Price extends Model {

    use SoftDeletes, CascadeSoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'age_range_id', 'name_taxonomy_id', 'amount',
        'margin_type_taxonomy_id', 'margin_value', 'extra', 'mandatory'
    ];

    protected $cascadeDeletes = ['elements'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['product'];

    /**
     * Custom save for Price overwriting Model::save()
     * Sets margin type of Date range if assigned plus do extra checks for integrity
     * 
     * @param array $options
     * @return bool
     * @throws UserException
     */
    public function save(array $options = []): bool {
        if ($this->extra && $this->amount != 1) {
            throw new UserException('The amount of an extra price must be 1.');
        }

        if ($this->priceRowNameExists()) {
            throw new UserException('There is already a price row with the same name.');
        }

        if (!empty($this->date_range_id)) {
            $this->margin_type_taxonomy_id = AgeRange::findOrFail($this->age_range_id)->ageRangeable->margin_type_taxonomy_id;
        } else {
            $this->margin_type_taxonomy_id = null;
        }

        if ($this->extra || !$this->mandatory) {
            $this->mandatory = false;
        }
        if (!$this->isThereAnOtherMandatoryPrice()) {
            $this->mandatory = true;
        }

        $return = parent::save($options);
        $this->maintainMandatoryIntegrity();
        return $return;
    }

    /**
     * Relation to product
     * 
     * @return HasOne
     */
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation to ageRange
     * 
     * @return HasOne
     */
    public function ageRange(): HasOne {
        return $this->hasOne(AgeRange::class, 'id', 'age_range_id');
    }

    /**
     * Relation to margin Type taxonomy
     * 
     * @return HasOne
     */
    public function marginType(): HasOne {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
    }

    /**
     * Relation to name taxonomy
     * 
     * @return HasOne
     */
    public function name(): HasOne {
        return $this->hasOne(Taxonomy::class, 'id', 'name_taxonomy_id');
    }

    /**
     * Relation to priceelements
     * 
     * @return HasMany
     */
    public function elements(): HasMany {
        return $this->hasMany(PriceElement::class, 'price_id', 'id')->with(['marginType','modelMealPlan.mealPlan.name']);
    }

    /**
     * Check if price row name already exists
     * 
     * @return bool
     */
    private function priceRowNameExists(): bool {
        $query = Price
                ::where('name_taxonomy_id', '=', $this->name_taxonomy_id)
                ->where('product_id', '=', $this->product_id);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return !!$query->count();
    }

    /**
     * Check if another mandatory price row already exists
     * 
     * @return bool
     */
    private function isThereAnOtherMandatoryPrice(): bool {
        $count = Price
                ::where('product_id', '=', $this->product_id)
                ->where('id', '!=', $this->id)
                ->where('mandatory', '=', true)
                ->count();
        return !!$count;
    }

    /**
     * Maintains integrity of mandatory property (there can be only one)
     * 
     * @return void
     */
    private function maintainMandatoryIntegrity() {
        if (!$this->mandatory) {
            return;
        }
        Price
                ::where('product_id', '=', $this->product_id)
                ->where('id', '!=', $this->id)
                ->where('mandatory', '=', true)
                ->update(['mandatory' => false]);
    }

}
