<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\DeviceDescription
 *
 * @property int $id
 * @property int $device_id
 * @property int $taxonomy_id
 * @property int $description_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $description
 * @property-read Taxonomy $descriptionTaxonomy
 * @property-read Device $device
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceDescription onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceDescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceDescription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceDescription withoutTrashed()
 */
class DeviceDescription extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        DescriptionTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['device_id', 'taxonomy_id', 'description_id'];

    protected $cascadeDeletes = ['description'];

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
