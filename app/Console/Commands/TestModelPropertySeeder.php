<?php

namespace App\Console\Commands;

use App\Cruise;
use App\Facades\Config;
use App\ModelClassification;
use App\Organization;
use App\OrganizationGroup;
use App\Program;
use App\Traits\PropertyCategorySetterTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Class to seed a accommodation test data
 */
class TestModelPropertySeeder
{
    
    use PropertyCategorySetterTrait;
    
    private $classificationClass;
    private $metaClass;
    private $categoryTxPath;
    private $foreignKey;
    private $model;

    /**
     * Seed all properties of a model
     *
     * @param Model $model
     * @param array $data
     * @return void
     */
    public function seed(Model $model, array $data)
    {
        $this->model = $model;
        $this->loadClassSettings();
        if (is_subclass_of($this->model, Organization::class)) {
            $this->foreignKey = 'organization_id';
        } else if (is_subclass_of($this->model, OrganizationGroup::class)) {
            $this->foreignKey = 'organization_group_id';
        } else if (get_class($this->model) == Program::class) {
            $this->foreignKey = 'program_id';
        } else if (get_class($this->model) == Cruise::class) {
            $this->foreignKey = 'cruise_id';
        } else {
            $modelClass = get_class($this->model);
            throw new Exception("Unsupported model type `{$modelClass}`!");
        }

        if (!empty($data['classifications'])) {
            foreach ($data['classifications'] as $classificationKey => $classificationData) {
                $this->seedModelClassifications($classificationData, $model->id, null, $classificationKey);
            }
        }
    }

    /**
     * Seed model classifications
     *
     * @param array $data
     * @param int $modelId
     * @param int $parentClassificationId
     * @param string $categoryKey
     * @param int $parentTxId
     */
    private function seedModelClassifications(
        $data,
        int $modelId,
        $parentClassificationId = null,
        $categoryKey = null,
        $parentTxId = null
    ) {
        if (!$parentTxId) {
            $classificationTxId = Config::getOrFail("{$this->categoryTxPath}.{$categoryKey}.id");
            $modelCl = $this->classificationClass::findByTaxonomyAndModel($classificationTxId, $modelId);
        } else {
            $classificationTx = Taxonomy::getOrCreateTaxonomy($data['name'], $parentTxId,
                Config::getOrFail('stylerstaxonomy.type_classification'), $data);
            $valueTx = null;
            if (!empty($data['value'])) {
                $valueTx = Taxonomy::getOrCreateTaxonomy($data['value'], $classificationTx->id);
            }
            $chargeTx = null;
            if (!empty($data['charge'])) {
                $chargeTx = Taxonomy::getOrCreateTaxonomy($data['charge'], Config::getOrFail('taxonomies.charge'));
            }
            $description = null;
            if (!empty($data['description'])) {
                $description = new Description();
                $description->description = $data['description'];
                $description->saveOrFail();
            }

            $classificationClass = $this->classificationClass;
            $modelCl = new $classificationClass();
            $modelCl->{$this->foreignKey} = $modelId;
            $modelCl->parent_classification_id = $parentClassificationId;
            $modelCl->classification_taxonomy_id = $classificationTx->id;
            $modelCl->value_taxonomy_id = is_null($valueTx) ? null : $valueTx->id;
            $modelCl->priority = isset($data['priority']) ? $data['priority'] : null;
            $modelCl->additional_description_id = is_null($description) ? null : $description->id;
            $modelCl->is_highlighted = !empty($data['is_highlighted']);
            $modelCl->is_listable = !empty($data['is_listable']);
            $modelCl->saveOrFail();
        }

        if (!empty($data['classifications'])) {
            foreach ($data['classifications'] as $childData) {
                $this->seedModelClassifications(
                    $childData,
                    $modelId,
                    $modelCl->id,
                    null,
                    $modelCl->classification_taxonomy_id
                );
            }
        }

        if (!empty($data['metas'])) {
            foreach ($data['metas'] as $childData) {
                $this->seedModelMeta($childData, $modelId, $modelCl);
            }
        }
    }

    /**
     * Seed model meta
     *
     * @param array $data
     * @param int $modelId
     * @param ModelClassification $parentClassification
     */
    private function seedModelMeta(
        array $data,
        int $modelId,
        $parentClassification = null
    ) {
        $metaTx = Taxonomy::getOrCreateTaxonomy(
            $data['name'],
            $parentClassification->classification_taxonomy_id,
            Config::getOrFail('stylerstaxonomy.type_meta'),
            $data
        );
        if (isset($data['description'])) {
            $description = new Description();
            $description->description = $data['description'];
            $description->saveOrFail();
            $data['additional_description_id'] = $description->id;
        }

        if($parentClassification){
            $data['parent_classification_id'] = $parentClassification->id;
        }
        $metaClass = $this->metaClass;
        (new $metaClass)->insertOrUpdateMeta($this->foreignKey, $modelId, $parentClassification->classification_taxonomy_id, $data);
    }

}
