<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\DeviceClassification
 *
 * @property int $id
 * @property int $device_id
 * @property int $parent_classification_id
 * @property int $classification_taxonomy_id
 * @property int $value_taxonomy_id
 * @property int $priority
 * @property int $price_taxonomy_id
 * @property int $additional_description_id
 * @property bool $is_highlighted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read Collection|DeviceClassification[] $childClassifications
 * @property-read Taxonomy $classificationTaxonomy
 * @property-read Device $device
 * @property-read DeviceClassification $parentClassification
 * @property-read Taxonomy $priceTaxonomy
 * @property-read Taxonomy $valueTaxonomy
 * @mixin \Eloquent
 * @property bool $is_listable
 * @property int|null $charge_taxonomy_id
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy|null $chargeTaxonomy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DeviceMeta[] $childMetas
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereChargeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereClassificationTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification wherePriceTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceClassification whereValueTaxonomyId($value)
 */
class DeviceClassification extends ModelClassification
{

    protected $table = 'device_classifications';
    protected $classificationClass = self::class;
    protected $metaClass = DeviceMeta::class;
    protected static $foreignKey = 'device_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'parent_classification_id',
        'classification_taxonomy_id',
        'value_taxonomy_id',
        'priority',
        'price_taxonomy_id',
        'charge_taxonomy_id',
        'additional_description_id',
        'is_highlighted',
        'is_listable'
    ];

    protected $touches = ['device'];

    /**
     * Relation to device
     *
     * @return BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

}
