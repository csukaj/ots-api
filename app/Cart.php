<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Cart
 *
 * @property int $id
 * @property int $status_taxonomy_id
 * @property string|null $tax_number
 * @property string $country
 * @property string $zip
 * @property string $city
 * @property string $address
 * @property string $email
 * @property string $phone
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $company_name
 * @property int $billing_type_taxonomy_id
 * @property string $site
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $billingType
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UniqueProduct[] $uniqueProducts
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Cart onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereBillingTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereSite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereStatusTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cart whereZip($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Cart withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Cart withoutTrashed()
 * @mixin \Eloquent
 */
class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status_taxonomy_id',
        'billing_type_taxonomy_id',
        'first_name',
        'last_name',
        'company_name',
        'site',
        'tax_number',
        'country',
        'zip',
        'city',
        'address',
        'email',
        'phone'
    ];

    /**
     * type
     * Relation to UniqueProducts
     *
     * @return HasMany
     */
    public function uniqueProducts(): HasMany
    {
        return $this->hasMany(UniqueProduct::class);
    }

    /**
     * Relation to status taxonomy
     *
     * @return HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'status_taxonomy_id');
    }

    /**
     * Relation to billing type taxonomy
     *
     * @return HasOne
     */
    public function billingType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'billing_type_taxonomy_id');
    }
}
