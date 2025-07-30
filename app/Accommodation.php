<?php

namespace App;

use App\Facades\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Accommodation
 *
 * @property int $id
 * @property int $name_description_id
 * @property int $type_taxonomy_id
 * @property bool $is_active
 * @property int|null $parentable_id
 * @property int|null $pricing_logic_taxonomy_id
 * @property int|null $margin_type_taxonomy_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $location_id
 * @property string|null $parentable_type
 * @property int|null $supplier_id
 * @property int|null $short_name_description_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\AgeRange[] $ageRanges
 * @property-read \App\Organization $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationClassification[] $classifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DateRange[] $dateRanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Device[] $devices
 * @property-read \Modules\Stylersmedia\Entities\Gallery $galleries
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $hotelChain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Location[] $location
 * @property-read \App\OrganizationManager $managers
 * @property-read \Baum\Extensions\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\Taxonomy[] $marginType
 * @property-read \App\ModelMealPlan $modelMealPlans
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $organizationGroup
 * @property-read \App\Accommodation|null $parentOrganization
 * @property-read \App\Accommodation|null $parentable
 * @property-read \Baum\Extensions\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\Taxonomy[] $pricingLogic
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Supplier[] $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization forParentable($type, $id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereParentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereParentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereShortNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Accommodation whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationDescription[] $descriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationMeta[] $metas
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $name
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $shortName
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 */
class Accommodation extends Organization
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organizations';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('accommodation', function (Builder $builder) {
            $builder->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.accommodation.id'));
        });
    }

    public function getMorphClass(): string
    {
        return Organization::class;
    }

    /**
     * Relation to parent organization
     *
     * @return MorphTo
     */
    public function hotelChain(): MorphTo
    {
        return $this->morphTo(HotelChain::class, 'parentable_type', 'parentable_id');
    }

    /**
     * Get name list of accommodations on default language
     *
     * @return Collection|array
     * @static
     * @throws \Exception
     */
    static public function getEnglishNames()
    {
        return parent::getEnglishNamesByType(Config::getOrFail('taxonomies.organization_types.accommodation.id'));
    }

    /**
     * Get active accommodations ids
     *
     * @return array
     */
    static public function getIds(): array
    {
        return self::orderBy('id')->pluck('id')->toArray();
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getChannelManagerId()
    {
        $channelManagerTx = $this->getClassification(Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.id'));
        return ($channelManagerTx && $channelManagerTx->value_taxonomy_id) ? $channelManagerTx->value_taxonomy_id : null;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getChannelManagerHotelId()
    {
        $channelManagerHotelIdTaxonomyId = Config::getOrFail('taxonomies.organization_properties.categories.settings.metas.channel_manager_id.id');
        $channelManagerHotelIdTx = $this
            ->metas()
            ->where('taxonomy_id', $channelManagerHotelIdTaxonomyId)
            ->first();
        return ($channelManagerHotelIdTx && $channelManagerHotelIdTx->value) ? $channelManagerHotelIdTx->value : null;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getHotelAuthenticationChannelKey()
    {
        $hotelAuthenticationChannelKeyTaxonomyId = Config::getOrFail('taxonomies.organization_properties.categories.settings.metas.hotel_authentication_channel_key.id');
        $hotelAuthenticationChannelKeyTx = $this
            ->metas()
            ->where('taxonomy_id', $hotelAuthenticationChannelKeyTaxonomyId)
            ->first();
        return ($hotelAuthenticationChannelKeyTx && $hotelAuthenticationChannelKeyTx->value) ? $hotelAuthenticationChannelKeyTx->value : null;
    }
}
