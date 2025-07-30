<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

/**
 * App\PriceModifierCombination
 *
 * @property int $id
 * @property int $first_price_modifier_id
 * @property int $second_price_modifier_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read PriceModifier $firstPriceModifier
 * @property-read PriceModifier $secondPriceModifier
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierCombination onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierCombination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierCombination whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierCombination whereFirstPriceModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierCombination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierCombination whereSecondPriceModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierCombination whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierCombination withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierCombination withoutTrashed()
 */
class PriceModifierCombination extends Model {

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_price_modifier_id', 'second_price_modifier_id'];

    /**
     * Relation to first PriceModifier
     * 
     * @return HasOne
     */
    public function firstPriceModifier(): HasOne {
        return $this->hasOne(PriceModifier::class, 'id', 'first_price_modifier_id');
    }

    /**
     * Relation to second PriceModifier
     * 
     * @return HasOne
     */
    public function secondPriceModifier(): HasOne {
        return $this->hasOne(PriceModifier::class, 'id', 'second_price_modifier_id');
    }

    /**
     * Get combination of 2 Price Modifier (if exists)
     * 
     * @param int $firstPriceModifierId
     * @param int $secondPriceModifierId
     * @return PriceModifierCombination|null
     */
    static public function getCombination(int $firstPriceModifierId, int $secondPriceModifierId) {
        list($firstPriceModifierId, $secondPriceModifierId) = self::orderPriceModifierIds($firstPriceModifierId, $secondPriceModifierId);
        return self
                ::where('first_price_modifier_id', $firstPriceModifierId)
                ->where('second_price_modifier_id', $secondPriceModifierId)
                ->first();
    }

    /**
     * Set a new price modifier combination if not already exists. Check if they have same organizatiojn id
     * 
     * @param int $firstPriceModifierId
     * @param int $secondPriceModifierId
     * @return PriceModifierCombination
     * @throws ValidationException
     */
    static public function set(int $firstPriceModifierId, int $secondPriceModifierId) {
        $combination = self::getCombination($firstPriceModifierId, $secondPriceModifierId);
        if (!$combination) {
            list($firstPriceModifierId, $secondPriceModifierId) = self::orderPriceModifierIds($firstPriceModifierId, $secondPriceModifierId);
            $combination = new self();
            $combination->first_price_modifier_id = $firstPriceModifierId;
            $combination->second_price_modifier_id = $secondPriceModifierId;
            $firstOwnerModel = $combination->firstPriceModifier->getPricemodifiableModel();
            $secondOwnerModel = $combination->secondPriceModifier->getPricemodifiableModel();
            if (get_class($firstOwnerModel) != get_class($secondOwnerModel) || $firstOwnerModel->id != $secondOwnerModel->id) {
                throw new ValidationException('Owner model mismatch.');
            }
            $combination->saveOrFail();
        }
        return $combination;
    }

    /**
     * Get list of combinations for specified price modifier id list
     * @param array $priceModifierIds
     * @return Collection
     */
    static public function getForPriceModifiers(array $priceModifierIds) {
        $q1 = self::whereIn('first_price_modifier_id', $priceModifierIds);
        return self::whereIn('second_price_modifier_id', $priceModifierIds)
                ->union($q1)
                ->orderBy('first_price_modifier_id')
                ->orderBy('second_price_modifier_id')
                ->get();
    }

    /**
     * Sort price modifier ids (first should be smaller)
     * 
     * @param int $firstPriceModifierId
     * @param int $secondPriceModifierId
     * @return array
     */
    static public function orderPriceModifierIds(&$firstPriceModifierId, &$secondPriceModifierId): array {
        if ($firstPriceModifierId > $secondPriceModifierId) {
            $temp = $secondPriceModifierId;
            $secondPriceModifierId = $firstPriceModifierId;
            $firstPriceModifierId = $temp;
        }
        return [$firstPriceModifierId, $secondPriceModifierId];
    }

}
