<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait MetaTrait
{

    public function metaTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    public function additionalDescription(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }

    public function getMetaEntities($columnName, $objectId): array
    {
        $return = [];
        $metas = self::where($columnName, $objectId)->get();
        if ($metas) {
            foreach ($metas as $meta) {
                $tmpMeta = [
                    'name' => $meta->metaTaxonomy->name,
                    'value' => $meta->value
                ];

                if ($meta->additionalDescription) {
                    $tmpMeta['description'] = (new DescriptionEntity($meta->additionalDescription))->getFrontendData();
                }

                $return[] = $tmpMeta;
            }
        }
        return $return;
    }

    static protected function getListableMetaEntitiesForModel(string $columnName, int $modelId, array $entityAdditions = ['frontend']): array
    {
        $models = static
            ::where($columnName, $modelId)
            ->listable()
            ->forParent(null)
            ->with(['metaTaxonomy', 'additionalDescription'])
            ->orderBy('priority')
            ->get();

        $entityClass = static::$entityClass;
        return $entityClass::getCollection($models, $entityAdditions);
    }

    public function setMetas(string $columnName, int $objectId, int $parentId, array $metas)
    {
        $metaIds = $this->getActiveMetaIds($columnName, $objectId);
        foreach ($metas as $meta) {
            $metaId = $this->insertOrUpdateMeta($columnName, $objectId, $parentId, $meta);
            $metaIds = array_diff($metaIds, [$metaId]);
        }
        $this->deleteUnusedMetas($metaIds);
    }

    public function clearMetas($columnName, $objectId)
    {
        $metaIds = $this->getActiveMetaIds($columnName, $objectId);
        $this->deleteUnusedMetas($metaIds);
    }

    public function insertOrUpdateMeta($columnName, $objectId, $parentTxId, $data)
    {
        $taxonomy = Taxonomy::getTaxonomy($data['name'], $parentTxId);
        $object = static::withTrashed()
            ->where($columnName, $objectId)
            ->where('taxonomy_id', $taxonomy->id)
            ->first();
        if ($object) {
            if ($object->trashed()) {
                $object->restore();
            }
        } else {
            $class = get_class($this);
            $object = new $class();
            $object->{$columnName} = $objectId;
            $object->taxonomy_id = $taxonomy->id;
        }

        if (isset($data['parent_classification_id'])) { //doesn't exists on every meta type...
            $object->parent_classification_id = $data['parent_classification_id'];
        }
        if (in_array('additional_description_id', $this->fillable)) {
            $object->additional_description_id = isset($data['additional_description_id']) ? $data['additional_description_id'] : null;
        }
        if (in_array('is_listable', $this->fillable)) {
            $object->is_listable = !empty($data['is_listable']);
        }
        if (in_array('priority', $this->fillable)) {
            $object->priority = isset($data['priority']) ? $data['priority'] : null;
        }
        $object->value = $data['value'];
        $object->saveOrFail();
        return $object->id;
    }

    public function getActiveMetaIds($columnName, $objectId)
    {
        return self::where($columnName, $objectId)->get()->pluck('id')->toArray();
    }

    public function deleteUnusedMetas(array $unusedIds)
    {
        self::destroy($unusedIds);
    }

    static public function getMeta(string $columnName, int $objectId, int $taxonomyId)
    {
        return self::where($columnName, $objectId)
            ->where('taxonomy_id', $taxonomyId)
            ->first();
    }
}
