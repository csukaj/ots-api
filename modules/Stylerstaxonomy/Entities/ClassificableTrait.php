<?php

namespace Modules\Stylerstaxonomy\Entities;

use App\Exceptions\UserException;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

trait ClassificableTrait {
    
    /**
     * Creates or updates a classification model and returns it
     * @param integer $classificationTxId
     * @param integer $valueTxId
     * @return object
     */
    public function setClassification($classificationTxId, $valueTxId) {
        if (!$this->isValidClassificationValue($classificationTxId, $valueTxId)) {
            throw new UserException('Invalid classification value.');
        }
        
        $old = $this->classifications()->where('classification_taxonomy_id', $classificationTxId)->first();
        
        $classificationClass = $this->getClassificationClassName();
        $classification = new $classificationClass([
            $this->getModelIdNameinClassification() => $this->id,
            'classification_taxonomy_id' => $classificationTxId,
            'value_taxonomy_id' => $valueTxId
        ]);
        if (!is_null($old)) {
            $classification->id = $old->id;
            $classification->exists = true;
        }
        $classification->saveOrFail();
        
        return $classification;
    }
    
    /**
     * Gets a classification value taxonomy by classification taxonomy id
     * @param integer $classificationTxId
     * @return object|null
     */
    public function getClassification($classificationTxId) {
        $classificationData = $this->classifications()->where('classification_taxonomy_id', $classificationTxId)->first();
        
        if (!$classificationData) {
            return null;
        }
        
        $classificationClass = $this->getClassificationClassName();
        $classification = new $classificationClass($classificationData->toArray());
        $classification->id = $classificationData->id;
        $classification->exists = true;
        return $classification;
    }
    
    /**
     * Check the validity of a classification value
     * @param integer $classificationTxId
     * @param integer $valueTxId
     * @return boolean
     */
    protected function isValidClassificationValue($classificationTxId, $valueTxId) {
        $classificationTx = Taxonomy::findOrFail($classificationTxId);
        if (is_null($valueTxId)) {
            return true;
        }
        $valueTx = Taxonomy::findOrFail($valueTxId);
        return $valueTx->isDescendantOf($classificationTx);
    }
    
    protected function getClassificationClassName() {
        return self::class . 'Classification';
    }
    
    protected function getModelIdNameinClassification() {
        $modelClassParts = explode('\\', self::class);
        return strtolower(array_pop($modelClassParts)) . '_id';
    }
}