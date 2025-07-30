<?php

namespace App;

use App\Exceptions\UserException;
use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\AgeRange
 *
 * @property int $id
 * @property int $from_age
 * @property int $to_age
 * @property int $age_rangeable_id
 * @property string $age_rangeable_type
 * @property int $name_taxonomy_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property bool $banned
 * @property bool $free
 * @property-read Collection|DeviceUsageElement[] $deviceUsageElements
 * @property-read Taxonomy $name
 * @property-read Organization $organization
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $ageRangeable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange forAgeRangeable($type, $id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange forOrganizationGroup($id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\AgeRange onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereAgeRangeableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereAgeRangeableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereFromAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereNameTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereToAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AgeRange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\AgeRange withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\AgeRange withoutTrashed()
 */
class AgeRange extends Model
{

    use SoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_age',
        'to_age',
        'age_rangeable_type',
        'age_rangeable_id',
        'name_taxonomy_id',
        'banned',
        'free'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['ageRangeable'];

    /**
     * Polymorphic relation to cruise, organization and price fee
     *
     * @return MorphTo
     */
    public function ageRangeable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation to name taxonomy
     *
     * @return HasOne
     */
    public function name(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'name_taxonomy_id');
    }

    /**
     * Relation to device Usage Elements
     *
     * @return HasMany
     */
    public function deviceUsageElements(): HasMany
    {
        return $this->hasMany(DeviceUsageElement::class, 'age_range_id', 'id');
    }


    /**
     * Scope a query to only include price modifier date ranges.
     *
     * @param Builder $query query to scope to
     * @param int $id
     * @return Builder
     */
    public function scopeForOrganizationGroup(Builder $query, int $id): Builder
    {
        return $this->scopeForAgeRangeable($query, OrganizationGroup::class, $id);
    }

    /**
     * Scope a query to only include price modifier date ranges.
     *
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForAgeRangeable($query, string $type, int $id): Builder
    {
        return $query
            ->where('age_rangeable_type', $type)
            ->where('age_rangeable_id', $id);
    }

    /**
     * Sets to_age attribute to specified value or null
     * @param type $value
     */
    public function setToAgeAttribute($value)
    {
        if ($value == '' || $value == null) {
            $this->attributes['to_age'] = null;
        } else {
            $this->attributes['to_age'] = $value;
        }
    }

    /**
     * Delete the model from the database if deviceUsageElements not exists
     * @return bool|null
     * @throws \UserException
     * @throws UserException
     * @throws \Exception
     */
    public function delete()
    {
        if (count($this->deviceUsageElements)) {
            throw new UserException('A model with active relations can not be deleted.');
        }
        return parent::delete();
    }

    /**
     * Get age range id by name at organization
     *
     * @param string $ageRangeableType
     * @param int $ageRangeableId
     * @param string $taxonomyName
     * @return mixed
     * @static
     */
    static public function getAgeRangeId(string $ageRangeableType, int $ageRangeableId, string $taxonomyName)
    {
        return self
            ::select('age_ranges.id')
            ->join('taxonomies', 'age_ranges.name_taxonomy_id', '=', 'taxonomies.id')
            ->where('age_ranges.age_rangeable_type', '=', $ageRangeableType)
            ->where('age_ranges.age_rangeable_id', '=', $ageRangeableId)
            ->where('taxonomies.name', '=', $taxonomyName)
            ->whereNull('taxonomies.deleted_at')
            ->value('id');

    }

    /**
     * Find age range By Name Or Fail
     *
     * @param string $name
     * @param string $ageRangeableType
     * @param int $ageRangeableId
     * @return AgeRange
     * @static
     */
    static public function findByNameOrFail(string $name, string $ageRangeableType, int $ageRangeableId): self
    {
        $ageRangeTx = Taxonomy::getTaxonomy($name, Config::get('taxonomies.age_range'));
        $return = AgeRange
            ::where('name_taxonomy_id', '=', $ageRangeTx->id)
            ->where('age_rangeable_type', '=', $ageRangeableType)
            ->where('age_rangeable_id', '=', $ageRangeableId)
            ->first();
        if (!$return) {
            throw new ModelNotFoundException('AgeRange not found.');
        }
        return $return;
    }

    /**
     * Get age ranges of organization in interval constructed by from age and to age
     *
     * @param string $ageRangeableType
     * @param int $ageRangeableId
     * @param int $fromAge
     * @param int|null $toAge
     * @param int $ignoreId AgeRange id to ignore
     * @return Collection
     * @throws UserException
     * @static
     */
    static public function getAgeRangesInInterval(
        string $ageRangeableType,
        int $ageRangeableId,
        int $fromAge,
        $toAge,
        int $ignoreId = null
    ) {
        if (is_null($ageRangeableId)) {
            throw new UserException('Missing ageRangeable ID.');
        }
        if (is_null($fromAge)) {
            throw new UserException('Invalid argument for from age.');
        }
        if ($toAge) {
            return self::getAgeRangesInFixInterval($ageRangeableType, $ageRangeableId, $fromAge, $toAge, $ignoreId);
        } else {
            return self::getAgeRangesInInfiniteInterval($ageRangeableType, $ageRangeableId, $fromAge, $ignoreId);
        }
    }

    /**
     * Get age ranges in interval when to age specified
     *
     * @param string $ageRangeableType
     * @param int $ageRangeableId
     * @param int $fromAge
     * @param int $toAge
     * @param int $ignoreId
     * @return Collection
     * @static
     */
    static public function getAgeRangesInFixInterval(
        string $ageRangeableType,
        int $ageRangeableId,
        int $fromAge,
        int $toAge,
        int $ignoreId = null
    ) {
        $query1 = AgeRange
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->where('from_age', '<=', $fromAge)
            ->where('to_age', '>=', $toAge);

        if ($ignoreId) {
            $query1->where('id', '!=', $ignoreId);
        }

        $query2 = AgeRange
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->where('from_age', '<=', $fromAge)
            ->whereNull('to_age');
        if ($ignoreId) {
            $query2->where('id', '!=', $ignoreId);
        }

        $query3 = AgeRange
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->where('from_age', '>=', $fromAge)
            ->where('from_age', '<', $toAge);
        if ($ignoreId) {
            $query3->where('id', '!=', $ignoreId);
        }

        $query4 = AgeRange
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->where('to_age', '>=', $fromAge)
            ->where('to_age', '<', $toAge);
        if ($ignoreId) {
            $query4->where('id', '!=', $ignoreId);
        }

        return $query4->union($query3)->union($query2)->union($query1)->orderBy('from_age')->get();
    }

    /**
     * Get age ranges in interval when to age not specified
     *
     * @param string $ageRangeableType
     * @param int $ageRangeableId
     * @param int $fromAge
     * @param int $ignoreId
     * @return Collection
     * @static
     */
    static public function getAgeRangesInInfiniteInterval(
        string $ageRangeableType,
        int $ageRangeableId,
        int $fromAge,
        int $ignoreId = null
    ) {

        $query1 = self
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->where('from_age', '<=', $fromAge)
            ->where('to_age', '>=', $fromAge);
        if ($ignoreId) {
            $query1->where('id', '!=', $ignoreId);
        }

        $query2 = self
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->where('from_age', '>', $fromAge);
        if ($ignoreId) {
            $query2->where('id', '!=', $ignoreId);
        }

        $query3 = self
            ::forAgeRangeable($ageRangeableType, $ageRangeableId)
            ->whereNull('to_age');
        if ($ignoreId) {
            $query3->where('id', '!=', $ignoreId);
        }

        return $query3->union($query2)->union($query1)->orderBy('from_age')->get();
    }
}
