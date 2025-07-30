<?php

namespace App\Traits;

use App\Cruise;
use App\CruiseClassification;
use App\CruiseMeta;
use App\Facades\Config;
use App\Organization;
use App\OrganizationClassification;
use App\OrganizationGroup;
use App\OrganizationGroupClassification;
use App\OrganizationGroupMeta;
use App\OrganizationMeta;
use App\Program;
use App\ProgramClassification;
use App\ProgramMeta;
use Exception;
use Illuminate\Database\Eloquent\Model;

trait PropertyCategorySetterTrait
{

    private $model;
    private $classificationClass;
    private $metaClass;
    private $categoryTxPath;
    private $checkRequired = true;

    protected function setPropertyCategories(Model $model, $checkRequired = true)
    {
        $this->model = $model;
        $this->checkRequired = $checkRequired;

        $this->loadClassSettings();

        $this->clearModelClassifications();

        $this->setModelClassificationCategories();

        $this->setModelClassifications();

        if (!empty($this->properties)) {
            $this->clearModelMetas();
            $this->setModelMetas();
        }
    }

    protected function loadClassSettings()
    {
        if (is_subclass_of($this->model, Organization::class)) {
            $this->classificationClass = OrganizationClassification::class;
            $this->metaClass = OrganizationMeta::class;
            $this->categoryTxPath = 'taxonomies.organization_properties.categories';
        } else if (is_subclass_of($this->model, OrganizationGroup::class)) {
            $this->classificationClass = OrganizationGroupClassification::class;
            $this->metaClass = OrganizationGroupMeta::class;
            $this->categoryTxPath = 'taxonomies.organization_group_properties.categories';
        } else if (get_class($this->model) == Program::class) {
            $this->classificationClass = ProgramClassification::class;
            $this->metaClass = ProgramMeta::class;
            $this->categoryTxPath = 'taxonomies.program_properties.categories';
        } else if (get_class($this->model) == Cruise::class) {
            $this->classificationClass = CruiseClassification::class;
            $this->metaClass = CruiseMeta::class;
            $this->categoryTxPath = 'taxonomies.cruise_properties.categories';
        } else {
            $modelClass = get_class($this->model);
            throw new Exception("Unsupported model type `{$modelClass}`!");
        }
    }

    /**
     * Destroy existing ModelClassifications
     */
    protected function clearModelClassifications()
    {
        $classificationClass = $this->classificationClass;
        (new $classificationClass())->clearClassifications(
            self::CONNECTION_COLUMN, $this->model->id
        );
    }

    /**
     * Destroy existing ModelMetas
     */
    protected function clearModelMetas()
    {
        $metaClass = $this->metaClass;
        (new $metaClass())->clearMetas(
            self::CONNECTION_COLUMN, $this->model->id
        );
    }

    /**
     * Create default ModelClassification categories for a model
     */
    protected function setModelClassificationCategories()
    {
        $classificationClass = $this->classificationClass;
        $classificationObj = new $classificationClass();
        $categoriesData = Config::getOrFail($this->categoryTxPath);
        $priority = 0;
        foreach ($categoriesData as $categoryData) {
            $classification = $classificationObj->insertOrUpdateClassification(
                self::CONNECTION_COLUMN, $this->model->id, $categoryData['id'], null
            );
            $classification->is_highlighted = false;
            $classification->is_listable = $categoryData['is_listable'];
            $classification->priority = $priority++;
            $classification->saveOrFail();
        }
    }

    /**
     * Create ModelClassifications from provided data (or updates existing)
     */
    protected function setModelClassifications()
    {
        $classificationClass = $this->classificationClass;
        $classificationObj = new $classificationClass();
        foreach (Config::getOrFail($this->categoryTxPath) as $category => $cData) {
            if (empty($cData['items'])) {
                continue;
            }
            $classifications = $cData['items'];
            foreach ($classifications as $classification) {
                $isClassificationRequired = !empty($classification['is_required']);
                $isClassificationSet = false;
                foreach ($this->properties as $property) {
                    if ($property['name'] == $classification['name']) {
                        $newClassification = $classificationObj->insertOrUpdateClassification(
                            self::CONNECTION_COLUMN, $this->model->id, $classification['id'], $property['value']
                        );
                        $newClassification->is_highlighted = !empty($property['is_highlighted']);
                        $newClassification->is_listable = !empty($property['is_listable']);
                        if (isset($property['category']) || isset($property['categoryId'])) {
                            $classificationTxId = isset($property['categoryId']) ?
                                $property['categoryId'] :
                                Config::getOrFail("{$this->categoryTxPath}.{$property['category']}.id");
                            $modelCl = $classificationClass::findByTaxonomyAndModel(
                                $classificationTxId, $this->model->id
                            );
                            $newClassification->parent_classification_id = $modelCl->id;
                        }
                        $newClassification->saveOrFail();
                        $isClassificationSet = true;
                    }
                }
                if ($isClassificationRequired && !$isClassificationSet && $this->checkRequired) {
                    throw new Exception('A required classification is not set: `' . $classification['name'] . '` @ ' . $this->classificationClass);
                }
            }
        }
    }

    /**
     * Create ModelMetas from provided data (or updates existing)
     */
    protected function setModelMetas()
    {
        $metaClass = $this->metaClass;
        $modelMeta = new $metaClass();

        foreach (Config::getOrFail($this->categoryTxPath) as $category => $cData) {
            if (empty($cData['metas'])) {
                continue;
            }
            foreach ($cData['metas'] as $meta) {
                foreach ($this->properties as $property) {
                    if ($property['name'] == $meta['name']) {
                        if (isset($property['listable'])) {
                            $property['is_listable'] = $property['listable'];
                        }
                        $modelMeta->insertOrUpdateMeta(self::CONNECTION_COLUMN, $this->model->id, $cData['id'], $property);
                        break;
                    }
                }
            }
        }
    }
}
