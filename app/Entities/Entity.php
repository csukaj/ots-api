<?php

namespace App\Entities;

use App\CruiseDescription;
use App\DeviceDescription;
use App\OrganizationDescription;
use App\OrganizationGroupDescription;
use App\ProgramDescription;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class Entity
{
    protected $model;

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    public function getFrontendData(array $additions = []): array
    {
        return [];
    }

    protected function filterAdditions(array $additions, array $config)
    {
        $filteredAdditions = [];
        foreach ($config as $parentAddition => $childAddition) {
            if (is_numeric($parentAddition) || in_array($parentAddition, $additions)) {
                $filteredAdditions[] = $childAddition;
            }
        }
        return $filteredAdditions;
    }

    protected function getTaxonomyTranslation(Taxonomy $taxonomy)
    {
        return (new TaxonomyEntity($taxonomy))->translations();
    }

    protected function getDescriptionWithTranslationsData(Description $description)
    {
        return (new DescriptionEntity($description))->getFrontendData();
    }

    /**
     * @param $modelId
     * @param $taxonomyId
     * @return array
     * @throws \Exception
     */
    protected function getEntityDescriptionsData($modelId, $taxonomyId)
    {
        switch (static::MODEL_TYPE) {
            case 'device':
                $modelDescription = new DeviceDescription();
                $localKey = 'device_id';
                break;
            case 'organization':
                $modelDescription = new OrganizationDescription();
                $localKey = 'organization_id';
                break;
            case 'organization_group':
                $modelDescription = new OrganizationGroupDescription();
                $localKey = 'organization_group_id';
                break;
            case 'place':
                $modelDescription = new PlaceDescription();
                $localKey = 'place_id';
                break;
            case 'program':
                $modelDescription = new ProgramDescription();
                $localKey = 'program_id';
                break;
            case 'cruise':
                $modelDescription = new CruiseDescription();
                $localKey = 'cruise_id';
                break;
            default:
                throw new \Exception("Unsupported model type: `" . static::MODEL_TYPE . "`!");
        }

        $modelDescriptions = $modelDescription->where($localKey, $modelId)->get();
        $descriptionTypeTxs = Taxonomy::find($taxonomyId)->getChildren();

        $return = [];
        foreach ($descriptionTypeTxs as $descriptionTypeTx) {
            foreach ($modelDescriptions as $modelDescription) {
                if ($modelDescription->taxonomy_id == $descriptionTypeTx->id && $modelDescription->description) {
                    $return[$descriptionTypeTx->name] = $this->getDescriptionWithTranslationsData($modelDescription->description);
                    continue;
                }
            }
        }

        return $return;
    }

    static protected function getTaxonomyTree($rootTxId)
    {
        return TaxonomyEntity::getCollection(Taxonomy::findOrFail($rootTxId)->getChildren(),
            ['descendants', 'translations']);
    }

    static public function getCollection($models, array $additions = []): array
    {
        $return = [];
        if (!empty($models)) {
            foreach ($models as $model) {
                $return[] = (new static($model))->getFrontendData($additions);
            }
        }
        return $return;
    }
}
