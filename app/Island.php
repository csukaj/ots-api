<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Island
 *
 * @property int $id
 * @property int $name_taxonomy_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Collection|District[] $districts
 * @property-read Collection|Location[] $locations
 * @property-read Taxonomy $name
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Island onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Island whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Island whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Island whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Island whereNameTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Island whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Island withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Island withoutTrashed()
 */
class Island extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name_taxonomy_id'];

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
     * Relation to district
     *
     * @return HasMany
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'island_id', 'id');
    }

    /**
     * Relation to location
     *
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'island_id', 'id');
    }

    /**
     * Find island by name
     *
     * @param string $name
     * @return Island
     */
    static public function findByName(string $name): self
    {
        $nameTx = Taxonomy::getTaxonomy($name, Config::get('taxonomies.island'));
        return self::where(['name_taxonomy_id' => $nameTx->id])->firstOrFail();
    }

    /**
     * Return all island ids
     *
     * @return array
     */
    static public function getIslandIds(): array
    {
        return self::all()->pluck('id')->toArray();
    }

    /**
     * get all Islands In Order by priority
     *
     * @return Collection
     */
    static public function getIslandsInOrder(): Collection
    {
        return self::hydrate(
            DB
                ::table('islands')
                ->select('islands.*')
                ->join('taxonomies', 'islands.name_taxonomy_id', '=', 'taxonomies.id')
                ->whereNull('islands.deleted_at')
                ->orderBy('taxonomies.priority', 'asc')
                ->get()
                ->toArray()
        );
    }

}
