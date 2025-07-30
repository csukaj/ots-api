<?php

namespace App\Entities;

use App\Facades\Config;
use App\Program;
use App\ProgramClassification;
use App\ProgramMeta;
use Modules\Stylersmedia\Entities\GalleryEntity;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class ProgramEntity extends Entity
{
    const MODEL_TYPE = 'program';
    const CONNECTION_COLUMN = 'program_id';

    protected $model;

    public function __construct(Program $program)
    {
        parent::__construct($program);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
            'type' => $this->model->type->name,
            'organization_id' => $this->model->organization_id,
            'name' => (new DescriptionEntity($this->model->name))->getFrontendData(),
            'location' => (new LocationEntity($this->model->location))->getFrontendData(
                in_array('frontend', $additions) ? ['frontend'] : ['admin']
            ),
            'descriptions' => $this->getEntityDescriptionsData($this->model->id, Config::get('taxonomies.program_description'))
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'frontend':
                    $return['properties'] = $this->getProperties();
                    $return['fees'] = $this->getFees();
                    break;

                case 'activities':
                    $return['activities'] = ProgramRelationEntity::getCollection(
                        $this->model->childRelations,
                        ['frontend']
                    );
                    break;

                case 'ship_company':
                    $return['ship_company'] = (new ShipCompanyEntity($this->model->shipCompany))->getFrontendData();
                    break;

                case 'galleries':
                    $return['galleries'] = GalleryEntity::getCollection($this->model->galleries);
                    break;
            }
        }

        return $return;
    }

    protected function getProperties(): array
    {
        return array_merge(
            ProgramMeta::getListableMetaEntitiesForModel(self::CONNECTION_COLUMN, $this->model->id),
            $this->getClassifications()
        );
    }

    protected function getClassifications(): array
    {
        $models = ProgramClassification
            ::where(self::CONNECTION_COLUMN, $this->model->id)
            ->listable()
            ->forParent(null)
            ->orderBy('priority')
            ->get();

        return ProgramClassificationEntity::getCollection($models, ['frontend']);
    }

    protected function getFees(): array
    {
        $fees = [];
        if($this->model->product && $this->model->product->fees){
            $fees = FeeEntity::getCollection($this->model->product->fees);
        }
        return $fees;
    }
}