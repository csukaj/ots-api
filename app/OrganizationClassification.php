<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationClassification
 *
 * @property int $id
 * @property int $organization_id
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
 * @property-read Collection|OrganizationClassification[] $childClassifications
 * @property-read Collection|OrganizationMeta[] $childMetas
 * @property-read Taxonomy $classificationTaxonomy
 * @property-read Organization $organization
 * @property-read OrganizationClassification $parentClassification
 * @property-read Taxonomy $priceTaxonomy
 * @property-read Taxonomy $valueTaxonomy
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereChargeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereClassificationTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationClassification whereValueTaxonomyId($value)
 */
class OrganizationClassification extends ModelClassification
{

    protected $table = 'organization_classifications';
    protected $classificationClass = self::class;
    protected $metaClass = OrganizationMeta::class;
    protected static $foreignKey = 'organization_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
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
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['organization'];

    /**
     * Relation to organization
     *
     * @return HasOne
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class/*, 'id', self::$foreignKey*/);
    }

}
