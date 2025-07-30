<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @require protected parentTaxonomyId;
 */
trait ClassificationTrait
{

    public function classificationTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'classification_taxonomy_id');
    }

    public function valueTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'value_taxonomy_id');
    }

    public function priceTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'price_taxonomy_id');
    }

    public function additionalDescription(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }

    public function getClassificationObjects(string $columnName, int $objectId, string $orderBy = null)
    {
        $query = self::where($columnName, $objectId);
        if (!is_null($orderBy)) {
            $query->orderBy($orderBy);
        }
        return $query->get();
    }

    static public function getClassification(string $columnName, int $objectId, int $classificationTaxonomyId)
    {
        return self::where($columnName, $objectId)
            ->where('classification_taxonomy_id', $classificationTaxonomyId)
            ->first();
    }

    protected function getClassificationWithTrashed(string $columnName, int $objectId, int $classificationTaxonomyId, int $valueTaxonomyId = null)
    {
        $query = self::withTrashed()
            ->where($columnName, $objectId)
            ->where('classification_taxonomy_id', $classificationTaxonomyId);
        if ($valueTaxonomyId) {
            $query->where('value_taxonomy_id', $valueTaxonomyId);
        }
        return $query->first();
    }

    public function getActiveClassificationIds(string $columnName, int $objectId): array
    {
        return self::where($columnName, $objectId)->get()->pluck('id')->toArray();
    }

    public function getClassificationEntities(string $columnName, int $objectId)
    {
        $return = [];
        $classifications = $this->getClassificationObjects($columnName, $objectId);
        if ($classifications) {
            foreach ($classifications as $classification) {
                $data = [
                    'name' => $classification->classificationTaxonomy->name,
                    'isset' => true
                ];

                if ($classification->value_taxonomy_id) {
                    $data['value'] = $classification->valueTaxonomy->name;
                }

                if ($classification->additional_description_id) {
                    $data['description'] = (new DescriptionEntity($classification->additionalDescription))->getFrontendData();
                }

                if (isset($classification->is_highlighted)) {
                    $data['highlighted'] = $classification->is_highlighted;
                }

                $return[] = $data;
            }
        }
        return $return;
    }

    public function insertOrUpdateClassification(string $columnName, int $objectId, int $nameTxId, $value = null)
    {
        $valueTxId = is_null($value) ? null : Taxonomy::getTaxonomy($value, $nameTxId)->id;

        $classification = $this->getClassificationWithTrashed($columnName, $objectId, $nameTxId, $valueTxId);
        if ($classification) {
            if ($classification->trashed()) {
                $classification->restore();
            }
            $classification->value_taxonomy_id = $valueTxId;
            return $classification;
        }

        $newClassification = new static();
        $newClassification->{$columnName} = $objectId;
        $newClassification->classification_taxonomy_id = $nameTxId;
        $newClassification->value_taxonomy_id = $valueTxId;
        $newClassification->saveOrFail();
        return $newClassification;
    }

    public function setClassifications(string $columnName, int $objectId, int $parentId, array $classifications)
    {
        $classificationIds = $this->getActiveClassificationIds($columnName, $objectId);
        foreach ($classifications as $classification) {
            if (isset($classification['isset']) && empty($classification['isset']))
                continue;
            $classificationId = $this->insertOrUpdateClassification($columnName, $objectId, $parentId, $classification['value'])->id;
            $classificationIds = array_diff($classificationIds, [$classificationId]);
        }
        $this->deleteUnusedClassifications($classificationIds);
    }

    public function deleteUnusedClassifications(array $unusedIds)
    {
        self::destroy($unusedIds);
    }

    public function clearClassifications(string $columnName, int $objectId)
    {
        $classificationIds = $this->getActiveClassificationIds($columnName, $objectId);
        $this->deleteUnusedClassifications($classificationIds);
    }
}
