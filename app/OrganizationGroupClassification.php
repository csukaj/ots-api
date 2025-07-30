<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationGroupClassification
 *
 * @property int $id
 * @property int $organization_group_id
 * @property int $parent_classification_id
 * @property int $classification_taxonomy_id
 * @property int $value_taxonomy_id
 * @property int $priority
 * @property int $charge_taxonomy_id
 * @property int $additional_description_id
 * @property bool $is_highlighted
 * @property bool $is_listable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read Taxonomy $chargeTaxonomy
 * @property-read Collection|OrganizationGroupClassification[] $childClassifications
 * @property-read Collection|OrganizationGroupMeta[] $childMetas
 * @property-read Taxonomy $classificationTaxonomy
 * @property-read OrganizationGroup $organization
 * @property-read OrganizationGroupClassification $parentClassification
 * @property-read Taxonomy $priceTaxonomy
 * @property-read Taxonomy $valueTaxonomy
 * @mixin \Eloquent
 * @property-read \App\OrganizationGroup $organizationGroup
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereChargeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereClassificationTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereOrganizationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupClassification whereValueTaxonomyId($value)
 */
class OrganizationGroupClassification extends ModelClassification {

    protected $table = 'organization_group_classifications';
    protected $classificationClass = self::class;
    protected $metaClass = OrganizationGroupMeta::class;
    protected static $foreignKey = 'organization_group_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_group_id',
        'parent_classification_id',
        'classification_taxonomy_id',
        'value_taxonomy_id',
        'priority',
        'charge_taxonomy_id',
        'additional_description_id',
        'is_highlighted',
        'is_listable'
    ];

    /**
     * Relation to organization group
     * 
     * @return HasOne
     */
    public function organizationGroup() {
        return $this->hasOne(OrganizationGroup::class, 'id', self::$foreignKey);
    }

}
