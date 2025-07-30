<?php

namespace App;

use App\Entities\DeviceMetaEntity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\DeviceMeta
 *
 * @property int $id
 * @property int $device_id
 * @property int $taxonomy_id
 * @property string $value
 * @property int $additional_description_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read Device $device
 * @property-read Taxonomy $metaTaxonomy
 * @mixin \Eloquent
 * @property int|null $parent_classification_id
 * @property int|null $priority
 * @property bool $is_listable
 * @property-read \App\DeviceClassification $parentClassification
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMeta whereValue($value)
 */
class DeviceMeta extends ModelMeta
{

    protected $classificationClass = DeviceClassification::class;
    static protected $entityClass = DeviceMetaEntity::class;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'taxonomy_id',
        'value',
        'additional_description_id',
        'parent_classification_id',
        'priority',
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
