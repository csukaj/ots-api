<?php
namespace App\Relations;

use App\Cruise;
use App\Entities\DeviceEntity;
use App\Organization;
use App\OrganizationGroup;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Relation for displaying Device names from organization
 */
class DevicesRelation extends Relation
{

    protected $type = self::TYPE_ONE_TO_MANY;
    protected $format = self::FORMAT_CSV;
    protected $model;

    public function __construct(Taxonomy $taxonomy, Model $model)
    {
        parent::__construct($taxonomy);
        $this->model = $model;
    }

    /**
     * Format data for displaying on frontend
     * 
     * @return array
     */
    public function getFrontendData()
    {
        switch (get_class($this->model)) {
            case Organization::class:
            case OrganizationGroup::class:
                $devices = $this->model->devices;
                break;
            case Cruise::class:
                $devices = $this->model->shipGroup->devices;
                break;

            default:
                $devices = [];
                break;
        }
        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => DeviceEntity::getCollection($devices)
        ];
    }
}
