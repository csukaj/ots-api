<?php

namespace App\Manipulators\Abstracts;

use App\Exceptions\UserException;
use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;

/**
 * Manipulator to create a new ModelClassification or ModelMeta
 * instance after the supplied data passes validation
 */
abstract class ModelPropertySetter {

    protected $classificationClass;
    protected $metaClass;
    protected $foreignKey;
    protected $categoryTxPath;
    protected $metaTxPath;

    public function __construct() {
        //
    }

    /**
     * Creates or updates an ModelClassification
     * @return ModelClassification
     * @throws UserException
     */
    public function setClassification($modelCl, $requestArray) {
        if ($modelCl->{$this->foreignKey}) {
            $requestArray[$this->foreignKey] = $modelCl->{$this->foreignKey};
        }
        if ($modelCl->parent_classification_id) {
            $requestArray['parent_classification_id'] = $modelCl->parent_classification_id;
            $parentCl = call_user_func([$this->classificationClass, 'find'], $modelCl->parent_classification_id);
        } else {
            $parentCl = null;
        }
        $modelCl->fill($requestArray);

        if (empty($requestArray['taxonomy'])) {
            $modelCl->classification_taxonomy_id = null;
        } else {
            if ($parentCl) {
                $parentTxId = $parentCl->classificationTaxonomy->id;
            } else {
                $parentTxId = Config::getOrFail($this->categoryTxPath);
            }
            $requestArray['taxonomy']['type'] = 'classification';
            $modelCl->classification_taxonomy_id = $this->createOrUpdateTaxonomy($requestArray['taxonomy'], $parentTxId)->id;
        }

        if ($this->classificationIsDuplicate($modelCl)) {
            throw new UserException('There is already a property with the same name. Instead of creating a duplication try to edit the existing one!');
        }

        if (empty($requestArray['value'])) {
            $modelCl->value_taxonomy_id = null;
        } else {
            if (empty($requestArray['value']['parent_id'])) {
                $requestArray['value']['parent_id'] = $modelCl->classification_taxonomy_id;
            }
            $modelCl->value_taxonomy_id = $this->createOrUpdateTaxonomy($requestArray['value'], null)->id;
        }

        if (empty($requestArray['charge'])) {
            $modelCl->charge_taxonomy_id = null;
        } else {
            $modelCl->charge_taxonomy_id = $this->createOrUpdateTaxonomy($requestArray['charge'], Config::get('taxonomies.charge'))->id;
        }

        if (isset($requestArray['additional_description'])) {
            $modelCl->additional_description_id = (new DescriptionSetter($requestArray['additional_description'], $modelCl->additional_description_id))->set()->id;
        }

        if (isset($requestArray['child_classifications'])) {
            $this->setClassificationChild($modelCl, $requestArray['child_classifications']);
        }

        if (isset($requestArray['child_metas'])) {
            $this->setMetaChild($modelCl, $requestArray['child_metas']);
        }

        $modelCl->saveOrFail();

        return $modelCl;
    }

    /**
     * @param $modelMt
     * @param $requestArray
     * @return mixed
     * @throws UserException
     */
    public function setMeta($modelMt, $requestArray) {
        $modelMt->fill($requestArray);

        if ($modelMt->parent_classification_id) {
            $requestArray['parent_classification_id'] = $modelMt->parent_classification_id;
            $parentCl = call_user_func([$this->classificationClass, 'find'], $modelMt->parent_classification_id);
        } else {
            $parentCl = null;
        }

        if (empty($requestArray['taxonomy'])) {
            $modelMt->taxonomy_id = null;
        } else {
            if ($parentCl) {
                $parentTxId = $parentCl->classificationTaxonomy->id;
            } else {
                $parentTxId = Config::get($this->metaTxPath);
            }
            $requestArray['taxonomy']['type'] = 'meta';
            $modelMt->taxonomy_id = $this->createOrUpdateTaxonomy($requestArray['taxonomy'], $parentTxId)->id;
        }

        if ($this->metaIsDuplicate($modelMt)) {
            throw new UserException('There is already a property with the same name. Instead of creating a duplication try to edit the existing one!');
        }

        if (!empty($requestArray['additional_description'])) {
            $modelMt->additional_description_id = (new DescriptionSetter($requestArray['additional_description'], $modelMt->additional_description_id))->set()->id;
        }

        $modelMt->saveOrFail();
        return $modelMt;
    }

    /**
     * @param $parentCl
     * @param $childClassificationData
     * @return array
     * @throws UserException
     */
    private function setClassificationChild($parentCl, $childClassificationData) {
        $return = [];
        $childClassifications = $parentCl->childClassifications;
        foreach ($childClassificationData as $childData) {
            $childCl = null;
            if (!empty($childData['id'])) {
                $childKey = call_user_func([$this->classificationClass, 'findKeyById'], $childData['id'], $childClassifications);
                if (!is_null($childKey)) {
                    $childCl = $childClassifications[$childKey];
                    unset($childClassifications[$childKey]);
                }
            }
            if (is_null($childCl)) {
                $classificationClass = $this->classificationClass;
                $childCl = new $classificationClass();
                $childCl->{$this->foreignKey} = $parentCl->{$this->foreignKey};
                $childCl->parent_classification_id = $parentCl->id;
            }
            $return[] = $this->setClassification($childCl, $childData);
        }
        foreach ($childClassifications as $childCl) {
            $childCl->delete();
        }
        return $return;
    }

    /**
     * @param $parentCl
     * @param $childMetaData
     * @return array
     * @throws UserException
     */
    private function setMetaChild($parentCl, $childMetaData) {
        $return = [];
        $childMetas = $parentCl->childMetas;
        foreach ($childMetaData as $childData) {
            $childMt = null;
            if (!empty($childData['id'])) {
                $childKey = call_user_func([$this->metaClass, 'findKeyById'], $childData['id'], $childMetas);
                if (!is_null($childKey)) {
                    $childMt = $childMetas[$childKey];
                    unset($childMetas[$childKey]);
                }
            }
            if (is_null($childMt)) {
                $metaClass = $this->metaClass;
                $childMt = new $metaClass();
                $childMt->{$this->foreignKey} = $parentCl->{$this->foreignKey};
                $childMt->parent_classification_id = $parentCl->id;
            }
            $return[] = $this->setMeta($childMt, $childData);
        }
        foreach ($childMetas as $childMt) {
            $childMt->delete();
        }
        return $return;
    }

    private function createOrUpdateTaxonomy($data, $parentTxId = null) {
        return Taxonomy::createOrUpdateTaxonomy($data, $parentTxId);
    }

    private function classificationIsDuplicate($modelCl) {
        $modelClExists = call_user_func([$this->classificationClass, 'where'], $this->foreignKey, '=', $modelCl->{$this->foreignKey});
        $modelClExists->where(function ($query) use ($modelCl) {
            if (!empty($modelCl->classification_taxonomy_id)) {
                $query->orWhere('classification_taxonomy_id', '=', $modelCl->classification_taxonomy_id);
            } else {
                $query->orWhereNull('classification_taxonomy_id');
            }
            if (!empty($modelCl->priority)) {
                $query->orWhere('priority', '=', $modelCl->priority);
            }
        });

        if (!empty($modelCl->parent_classification_id)) {
            $modelClExists->where('parent_classification_id', '=', $modelCl->parent_classification_id);
        } else {
            $modelClExists->whereNull('parent_classification_id');
        }

        if (!empty($modelCl->id)) {
            $modelClExists->where('id', '!=', $modelCl->id);
        }

        $modelClExists->whereNull('deleted_at');

        return (bool) $modelClExists->count();
    }

    private function metaIsDuplicate($modelMt) {
        $modelMtExists = call_user_func([$this->metaClass, 'where'], $this->foreignKey, '=', $modelMt->{$this->foreignKey});
        $modelMtExists->where(function ($query) use($modelMt) {
            if (!empty($modelMt->classification_taxonomy_id)) {
                $query->orWhere('taxonomy_id', '=', $modelMt->taxonomy_id);
            } else {
                $query->orWhereNull('taxonomy_id');
            }
        });

        if (!empty($modelMt->parent_classification_id)) {
            $modelMtExists->where('parent_classification_id', '=', $modelMt->parent_classification_id);
        } else {
            $modelMtExists->whereNull('parent_classification_id');
        }

        if (!empty($modelMt->id)) {
            $modelMtExists->where('id', '!=', $modelMt->id);
        }

        return (bool) $modelMtExists->count();
    }

}
