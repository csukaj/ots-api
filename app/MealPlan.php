<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\MealPlan
 *
 * @property int $id
 * @property int $name_taxonomy_id
 * @property int $service_bitmap
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Taxonomy $name
 * @property-read Collection|ModelMealPlan[] $modelMealPlans
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\MealPlan onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MealPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MealPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MealPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MealPlan whereNameTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MealPlan whereServiceBitmap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MealPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MealPlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\MealPlan withoutTrashed()
 */
class MealPlan extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['service_bitmap', 'name_taxonomy_id', 'created_at', 'updated_at', 'deleted_at'];

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
     * Relation to model MealPlans
     *
     * @return HasMany
     */
    public function modelMealPlans(): BelongsToMany
    {
        return $this->belongsToMany(ModelMealPlan::class);
    }

    /**
     * find By Name
     *
     * @param string $name
     * @return MealPlan
     * @static
     */
    static public function findByName(string $name): self
    {
        $nameTx = Taxonomy::getTaxonomy($name, Config::get('taxonomies.meal_plan'));
        return self::where('name_taxonomy_id', $nameTx->id)->firstOrFail();
    }

    /**
     * get all active MealPlan Ids
     * @return Collection
     * @static
     */
    static public function getMealPlanIds(): array
    {
        return self::all()->pluck('id')->toArray();
    }

    /**
     * get all active MealPlan Names indexed by its id
     * @return array
     * @static
     */
    static public function getMealPlanNames(): array
    {
        return DB::table('meal_plans')
            ->select(['meal_plans.id', 'taxonomies.name'])
            ->join('taxonomies', 'meal_plans.name_taxonomy_id', '=', 'taxonomies.id')
            ->whereNull('meal_plans.deleted_at')
            ->pluck('name', 'id')
            ->toArray();
    }
}
