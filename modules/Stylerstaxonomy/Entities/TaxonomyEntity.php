<?php

namespace Modules\Stylerstaxonomy\Entities;

class TaxonomyEntity {

    protected $taxonomy;

    public function __construct(Taxonomy $taxonomy) {
        $this->taxonomy = $taxonomy;
    }

    public function getFrontendData(array $additions = [], array $dependencies = []) {
        if (is_null($this->taxonomy)) {
            return null;
        }

        if (in_array('attributes', $additions)) {
            $return = $this->taxonomy->attributesToArray();
        } elseif (in_array('searchable_info', $additions)) {
            $return = [
                'name' => $this->translations(),
                'priority' => $this->taxonomy->priority
            ];
        } else {
            $return = [
                'id' => $this->taxonomy->id,
                'parent_id' => $this->taxonomy->parent_id,
                'name' => $this->taxonomy->name,
                'priority' => $this->taxonomy->priority,
                'is_active' => $this->taxonomy->is_active,
                'is_required' => $this->taxonomy->is_required,
                'is_readonly' => $this->taxonomy->is_readonly,
                'is_merchantable' => $this->taxonomy->is_merchantable,
                'is_searchable' => $this->taxonomy->is_searchable,
                'type' => $this->taxonomy->type,
                'icon' => $this->taxonomy->icon
            ];
        }

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'translations':
                    $return['translations'] = $this->getTranslations();
                    break;
                case 'translations_with_plurals':
                    $return['translations_with_plurals'] = $this->getTranslations(true);
                    break;
                case 'descendants':
                    $return['descendants'] = $this->getDescendants($additions, $dependencies);
                    break;
                case 'relation':
                    array_unshift($dependencies, $this->taxonomy);
                    $relation = $this->taxonomy->getTaxonomyRelation($dependencies);
                    $return['relation'] = $relation ? $relation->getFrontendData() : null;
                    break;
            }
        }

        return $return;
    }

    public function translations($withPlurals = false) {
        $taxonomies = [Language::getDefault()->iso_code => $this->taxonomy->name];
        return array_merge($taxonomies, $this->getTranslations($withPlurals));
    }

    private function getTranslations($withPlurals = false) {
        if ($withPlurals) {
            return TaxonomyTranslationEntity::getCollection($this->taxonomy->translations);
        }

        $return = [];
        $translations = $this->taxonomy->translations;
        foreach ($translations as $translation) {
            $return[$translation->language->iso_code] = $translation->name;
        }
        return $return;
    }

    private function getDescendants(array $additions = [], array $dependencies = []) {
        $return = [];
        $childTaxonomies = $this->taxonomy->getChildren()->sortBy('name')->sortBy('priority')->values()->all();
        foreach ($childTaxonomies as $childTaxonomy) {
            $return[] = (new self($childTaxonomy))->getFrontendData($additions, $dependencies);
        }
        return $return;
    }

    static public function getCollection($taxonomies, array $additions = [], array $dependencies = [], $sortByName = false) {
        $return = [];
        foreach ($taxonomies as $taxonomy) {
            $return[] = (new self($taxonomy))->getFrontendData($additions, $dependencies);
        }
        return $return;
    }

}
