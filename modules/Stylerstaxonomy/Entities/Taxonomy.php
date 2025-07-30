<?php

namespace Modules\Stylerstaxonomy\Entities;

use App\AgeRange;
use App\Cart;
use App\Content;
use App\ContentMedia;
use App\Cruise;
use App\CruiseClassification;
use App\CruiseDescription;
use App\CruiseMeta;
use App\DateRange;
use App\Device;
use App\DeviceClassification;
use App\DeviceDescription;
use App\DeviceMeta;
use App\District;
use App\Email;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Fee;
use App\Island;
use App\MealPlan;
use App\OfferClassification;
use App\OfferMeta;
use App\Order;
use App\Organization;
use App\OrganizationClassification;
use App\OrganizationDescription;
use App\OrganizationGroup;
use App\OrganizationGroupClassification;
use App\OrganizationGroupDescription;
use App\OrganizationGroupMeta;
use App\OrganizationGroupPoi;
use App\OrganizationMeta;
use App\Poi;
use App\Price;
use App\PriceElement;
use App\PriceModifier;
use App\PriceModifierClassification;
use App\PriceModifierMeta;
use App\PriorityOrderScope;
use App\Product;
use App\Program;
use App\ProgramClassification;
use App\ProgramDescription;
use App\ProgramMeta;
use App\ProgramRelation;
use App\Relations\Relation;
use App\RelativeTime;
use App\Schedule;
use App\UserSetting;
use Baum\Node;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;

/**
 * Taxonomy
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int|null $lft
 * @property int|null $rgt
 * @property int|null $depth
 * @property string $name
 * @property int|null $priority
 * @property bool $is_active
 * @property bool $is_required
 * @property bool $is_merchantable
 * @property string $type
 * @property string|null $relation
 * @property string|null $icon
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property bool $is_readonly
 * @property bool $is_searchable
 * @property-read \Baum\Extensions\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\Taxonomy[] $children
 * @property-read mixed $has_descendants
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\Language[] $languages
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation[] $translations
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Baum\Node limitDepth($limit)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereIsMerchantable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereIsReadonly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereIsSearchable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereLft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereRgt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Baum\Node withoutNode($node)
 * @method static \Illuminate\Database\Eloquent\Builder|\Baum\Node withoutRoot()
 * @method static \Illuminate\Database\Eloquent\Builder|\Baum\Node withoutSelf()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Taxonomy withoutTrashed()
 * @mixin \Eloquent
 */
class Taxonomy extends Node
{

    use SoftDeletes, CascadeSoftDeletes;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'taxonomies';

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $appends = ['has_descendants'];

    /**
     * Column name which stores reference to parent's node.
     *
     * @var string
     */
    protected $parentColumn = 'parent_id';

    /**
     * Column name for the left index.
     *
     * @var string
     */
    protected $leftColumn = 'lft';

    /**
     * Column name for the right index.
     *
     * @var string
     */
    protected $rightColumn = 'rgt';

    /**
     * Column name for the depth field.
     *
     * @var string
     */
    protected $depthColumn = 'depth';

    /**
     * Column to perform the default sorting
     *
     * @var string
     */
    protected $orderColumn = 'priority';

    /**
     * With Baum, all NestedSet-related fields are guarded from mass-assignment
     * by default.
     *
     * @var array
     *
     * While $fillable serves as a "white list" of attributes that should be mass assignable, you may also choose to use $guarded.
     * The $guarded property should contain an array of attributes that you do not want to be mass assignable.
     * All other attributes not in the array will be mass assignable. So, $guarded functions like a "black list".
     * Of course, you should use either  $fillable or $guarded - not both
     *
     */
    protected $guarded = ['id', 'lft', 'rgt', 'depth', 'is_readonly'];
    protected $fillable = [
        'name',
        'parent_id',
        'priority',
        'is_active',
        'is_required',
        'is_merchantable',
        'type',
        'relation',
        'icon',
        'is_searchable'
    ];

    protected $cascadeDeletes = ['translations'];

    /**
     * Relations to check before deleting a taxonomy.
     * If a relation to taxonomies is defined, need to add to this place too.
     * @var array
     */
    private $inverseRelations = [
        [AgeRange::class, 'name_taxonomy_id'],
        [Cart::class, 'billing_type_taxonomy_id'],
        [Contact::class, 'type_taxonomy_id'],
        [Content::class, 'category_taxonomy_id'],
        [Content::class, 'status_taxonomy_id'],
        [ContentMedia::class, 'media_role_taxonomy_id'],
        [Cruise::class, 'margin_type_taxonomy_id'],
        [Cruise::class, 'pricing_logic_taxonomy_id'],
        [CruiseClassification::class, 'charge_taxonomy_id'],
        [CruiseClassification::class, 'classification_taxonomy_id'],
        [CruiseClassification::class, 'value_taxonomy_id'],
        [CruiseDescription::class, 'taxonomy_id'],
        [CruiseMeta::class, 'taxonomy_id'],
        [DateRange::class, 'margin_type_taxonomy_id'],
        [DateRange::class, 'type_taxonomy_id'],
        [Device::class, 'margin_type_taxonomy_id'],
        [Device::class, 'name_taxonomy_id'],
        [Device::class, 'type_taxonomy_id'],
        [DeviceClassification::class, 'classification_taxonomy_id'],
        [DeviceClassification::class, 'price_taxonomy_id'],
        [DeviceClassification::class, 'value_taxonomy_id'],
        [DeviceDescription::class, 'taxonomy_id'],
        [DeviceMeta::class, 'taxonomy_id'],
        [District::class, 'name_taxonomy_id'],
        [Email::class, 'type_taxonomy_id'],
        [Fee::class, 'margin_type_taxonomy_id'],
        [File::class, 'type_taxonomy_id'],
        [Gallery::class, 'role_taxonomy_id'],
        [Island::class, 'name_taxonomy_id'],
        [Language::class, 'name_taxonomy_id'],
        [MealPlan::class, 'name_taxonomy_id'],
        [OfferClassification::class, 'classification_taxonomy_id'],
        [OfferClassification::class, 'value_taxonomy_id'],
        [OfferMeta::class, 'taxonomy_id'],
        [Order::class, 'billing_type_taxonomy_id'],
        [Order::class, 'order_type_taxonomy_id'],
        [Order::class, 'status_taxonomy_id'],
        [Organization::class, 'margin_type_taxonomy_id'],
        [Organization::class, 'pricing_logic_taxonomy_id'],
        [Organization::class, 'type_taxonomy_id'],
        [OrganizationClassification::class, 'charge_taxonomy_id'],
        [OrganizationClassification::class, 'classification_taxonomy_id'],
        [OrganizationClassification::class, 'value_taxonomy_id'],
        [OrganizationDescription::class, 'taxonomy_id'],
        [OrganizationGroup::class, 'margin_type_taxonomy_id'],
        [OrganizationGroup::class, 'pricing_logic_taxonomy_id'],
        [OrganizationGroup::class, 'type_taxonomy_id'],
        [OrganizationGroupClassification::class, 'charge_taxonomy_id'],
        [OrganizationGroupClassification::class, 'classification_taxonomy_id'],
        [OrganizationGroupClassification::class, 'value_taxonomy_id'],
        [OrganizationGroupDescription::class, 'taxonomy_id'],
        [OrganizationGroupMeta::class, 'taxonomy_id'],
        [OrganizationGroupPoi::class, 'type_taxonomy_id'],
        [OrganizationMeta::class, 'taxonomy_id'],
        [Poi::class, 'type_taxonomy_id'],
        [Price::class, 'margin_type_taxonomy_id'],
        [Price::class, 'name_taxonomy_id'],
        [PriceElement::class, 'margin_type_taxonomy_id'],
        [PriceModifier::class, 'condition_taxonomy_id'],
        [PriceModifier::class, 'modifier_type_taxonomy_id'],
        [PriceModifier::class, 'offer_taxonomy_id'],
        [PriceModifierClassification::class, 'classification_taxonomy_id'],
        [PriceModifierClassification::class, 'value_taxonomy_id'],
        [PriceModifierMeta::class, 'taxonomy_id'],
        [Product::class, 'margin_type_taxonomy_id'],
        [Product::class, 'type_taxonomy_id'],
        [Program::class, 'type_taxonomy_id'],
        [ProgramClassification::class, 'charge_taxonomy_id'],
        [ProgramClassification::class, 'classification_taxonomy_id'],
        [ProgramClassification::class, 'value_taxonomy_id'],
        [ProgramDescription::class, 'taxonomy_id'],
        [ProgramMeta::class, 'taxonomy_id'],
        [ProgramRelation::class, 'embarkation_direction_taxonomy_id'],
        [ProgramRelation::class, 'embarkation_type_taxonomy_id'],
        [RelativeTime::class, 'precision_taxonomy_id'],
        [RelativeTime::class, 'time_of_day_taxonomy_id'],
        [Schedule::class, 'frequency_taxonomy_id'],
        // [TaxonomyTranslation::class, 'taxonomy_id'], // not needed, it will be cascadeSoftDeleted
        [TaxonomyTranslationPlural::class, 'type_taxonomy_id'],
        [UserSetting::class, 'setting_taxonomy_id'],
        [UserSetting::class, 'value_taxonomy_id'],
    ];


    /*
      This is to support "scoping" which may allow to have multiple nested
      set trees in the same database table.
      You should provide here the column names which should restrict Nested
      Set queries. f.ex: company_id, etc.
     */

    /**
     * Columns which restrict what we consider our Nested Set list
     *
     * @var array
     */
    protected $scoped = [];

    /*
      Baum makes available two model events to application developers:

      1. `moving`: fired *before* the a node movement operation is performed.
      2. `moved`: fired *after* a node movement operation has been performed.

      In the same way as Eloquent's model events, returning false from the
      `moving` event handler will halt the operation.

      Please refer the Laravel documentation for further instructions on how
      to hook your own callbacks/observers into this events:
      http://laravel.com/docs/5.0/eloquent#model-events
     */

    /**
     * Relation class
     * @return Relation
     */
    protected $taxonomyRelation;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new PriorityOrderScope);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TaxonomyTranslation::class, 'taxonomy_id', 'id');
    }

    public function scopeSearchable(Builder $query): Builder
    {
        return $query->where('is_searchable', '=', true);
    }

    public function getHasDescendantsAttribute(): bool
    {
        return !$this->isLeaf();
    }

    public function getChildren($orderFunc = null)
    {
        if (!$orderFunc) {
            $orderFunc = function ($taxonomy) {
                return $taxonomy->name;
            };
        }
        return $this->getDescendants(1)->sortBy($orderFunc);
    }

    public function getOrderedChildren()
    {
        return $this->getChildren(function ($taxonomy) {
            return $taxonomy->priority;
        });
    }

    public function getTaxonomyRelation(array $dependencies)
    {
        $relationClassName = $this->relation;
        if (!$relationClassName) {
            return null;
        }
        if (empty($this->taxonomyRelation)) {
            $this->taxonomyRelation = $relationClassName::getInstance($dependencies);
        }
        return $this->taxonomyRelation;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkNotActiveRelationExistsOrFail(): bool
    {
        foreach ($this->inverseRelations as $relationConfig) {
            list($model, $field) = $relationConfig;
            if ($model::where($field, $this->id)->exists()) {
                $existing = $model::where($field, $this->id)->first();
                throw new \Exception(json_encode(['model' => $model, 'id' => $existing->id, 'field' => $field]));
            }
        }
        return true;
    }

    static public function getRoots()
    {
        return self::whereNull('parent_id')->get();
    }

    static public function taxonomyExists($name, $parent_id): bool
    {
        return (bool)self::where(['name' => $name, 'parent_id' => $parent_id])->count();
    }

    static public function getTaxonomy($name, $parent_id): self
    {
        return self::where(['name' => $name, 'parent_id' => $parent_id])->firstOrFail();
    }

    static public function getTaxonomyGrandChild($name, $grandparent_id): self
    {
        return self
            ::select('taxonomies.*')
            ->join('taxonomies AS parents', 'parents.id', '=', 'taxonomies.parent_id')
            ->where(['taxonomies.name' => $name, 'parents.parent_id' => $grandparent_id])
            ->firstOrFail();
    }

    /**
     * @param $name
     * @param null $parentTxId
     * @param null $type
     * @param array $properties
     * @return Taxonomy
     * @throws \Throwable
     */
    static public function getOrCreateTaxonomy($name, $parentTxId = null, $type = null, array $properties = []): self
    {
        try {
            return self::getTaxonomy($name, $parentTxId);
        } catch (ModelNotFoundException $e) {

            $taxonomy = self::withTrashed()
                ->where('parent_id', $parentTxId)
                ->where('name', $name)
                ->first();
            if ($taxonomy) {
                $taxonomy->restore();
            }

            $txType = ($type && in_array($type,
                    Config::getOrFail('stylerstaxonomy'))) ? $type : Config::getOrFail('stylerstaxonomy.type_unknown');
            $tx = new Taxonomy();
            $tx->name = $name;
            $tx->type = $txType;
            $tx->is_searchable = !empty($properties['is_searchable']);
            $tx->priority = (isset($properties['priority'])) ? (int)$properties['priority'] : null;
            $tx->saveOrFail();

            if ($parentTxId) {
                $parentTx = self::findOrFail($parentTxId);
                $tx->makeChildOf($parentTx);
            }

            return $tx;
        }
    }

    /**
     * @param $data
     * @param null $parentTxId
     * @return Taxonomy
     * @throws UserException
     */
    static public function createOrUpdateTaxonomy($data, $parentTxId = null)
    {
        $defaultLanguage = Language::getDefault();
        if (!empty($data['has_plurals'])) {
            $translations = $data['translations_with_plurals'];
        } else {
            $translations = $data['translations'];
            $translations[$defaultLanguage->iso_code] = $data['name'];
        }
        $id = !empty($data['id']) ? $data['id'] : null;
        $parentId = !empty($data['parent_id']) ? $data['parent_id'] : $parentTxId;
        $type = !empty($data['type']) ? $data['type'] : null;
        if (empty($id) && Taxonomy::taxonomyExists($data['name'], $parentId)) {
            throw new UserException('There is already a taxonomy with the same name. Instead of creating a duplication try to use the existing one!');
        }
        return (new TaxonomySetter($translations, $id, $parentId, $type, $data))->set();
    }

    static public function loadTaxonomy($id = null): self
    {
        try {
            $taxonomy = self::findOrFail($id);
        } catch (\Exception $e) {
            $taxonomy = new self();
            $taxonomy->id = $id;
        }
        return $taxonomy;
    }

}
