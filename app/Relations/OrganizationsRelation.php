<?php
namespace App\Relations;

use App\Cruise;
use App\Entities\OrganizationEntity;
use App\Entities\OrganizationGroupEntity;
use App\HotelChain;
use App\Organization;
use App\OrganizationGroup;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Relation for displaying Organizations with same parent
 */
class OrganizationsRelation extends Relation
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

        $siblings = [];
        switch (get_class($this->model)) {
            case Organization::class:
                if ($this->model->parentable_id) {
                    $siblings = Organization
                        ::forParentable(HotelChain::class, $this->model->parentable_id)
                        ->where('id', '!=', $this->model->id)
                        ->get();
                }
                $options = OrganizationEntity::getCollection($siblings);
                break;
            case OrganizationGroup::class:
                $siblings = OrganizationGroup
                    ::where('parent_id', $this->model->parent_id)
                    ->where('id', '!=', $this->model->id)
                    ->get();
                $options = OrganizationGroupEntity::getCollection($siblings);
                break;
            case Cruise::class:
            default:
                $options = [];
                break;
        }

        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => $options
        ];
    }
}
