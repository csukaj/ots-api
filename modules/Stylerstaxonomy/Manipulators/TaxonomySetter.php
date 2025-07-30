<?php

namespace Modules\Stylerstaxonomy\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyTranslation;

class TaxonomySetter
{

    private $translations = [];
    private $taxonomyId = null;
    private $type = null;
    private $attributes = null;
    private $connection;
    private $defaultLanguage;
    private $parentTaxonomyId;

    /**
     * TaxonomySetter constructor.
     * @param array $translations
     * @param int|null $taxonomyId
     * @param int|null $parentTaxonomyId
     * @param string|null $type
     * @param null $attributes
     * @throws \Exception
     */
    public function __construct(
        array $translations,
        int $taxonomyId = null,
        int $parentTaxonomyId = null,
        string $type = null,
        $attributes = null
    ) {
        $this->translations = $translations;
        $this->taxonomyId = $taxonomyId;
        $this->parentTaxonomyId = $parentTaxonomyId;
        if ($type) {
            if (!in_array($type, Config::getOrFail('stylerstaxonomy'))) {
                throw new \Exception("Unknown taxonomy type (`{$type}`)");
            }
            $this->type = $type;
        }
        $this->attributes = $attributes;
    }

    /**
     * Creates or updates a taxonomy with its translations
     * @return Taxonomy
     * @throws \Exception
     * @throws \Throwable
     */
    public function set(): Taxonomy
    {
        $this->defaultLanguage = Language::getDefault($this->connection);

        $this->translations[$this->defaultLanguage->iso_code] = $this->getName();

        if (!isset($this->translations[$this->defaultLanguage->iso_code])) {
            if (empty($this->translations[$this->defaultLanguage->iso_code])) {
                throw new \Exception("Missing translation for main language (`{$this->defaultLanguage->iso_code}`)");
            }
        }

        $taxonomy = $this->getTaxonomy();
        if (!is_null($taxonomy->id) && $taxonomy->is_required && $this->getName() != $taxonomy->name) {
            throw new UserException("Trying to modify a required taxonomy");
        }
        if (!empty($this->attributes)) {
            $taxonomy->fill($this->attributes);
        }
        $taxonomy->name = $this->getName();
        $taxonomy->type = $this->type ?: Config::getOrFail('stylerstaxonomy.type_unknown');
        $taxonomy->saveOrFail();

        $this->updateTranslations($taxonomy);

        $taxonomy->load('translations');
        return $taxonomy;
    }

    private function getName()
    {
        if (isset($this->translations[$this->defaultLanguage->iso_code])) {
            if (is_array($this->translations[$this->defaultLanguage->iso_code]) && !empty($this->translations[$this->defaultLanguage->iso_code]['singular'])) {
                return $this->translations[$this->defaultLanguage->iso_code]['singular'];
            }
            if (!is_array($this->translations[$this->defaultLanguage->iso_code]) && !empty($this->translations[$this->defaultLanguage->iso_code])) {
                return $this->translations[$this->defaultLanguage->iso_code];
            }
        }
        if (!empty($this->attributes['name'])) {
            return $this->attributes['name'];
        }
        return null;
    }

    private function updateTranslations(Taxonomy $taxonomy)
    {
        $languageCodes = Language::getLanguageCodes();
        $languageIdsToKeep = [];

        foreach ($this->translations as $languageCode => $translation) {
            if (!isset($languageCodes[$languageCode])) {
                throw new \Exception("Invalid language code: `{$languageCode}`");
            }
            $this->setTaxonomyTranslation($taxonomy->id, $languageCodes[$languageCode], $translation);
            $languageIdsToKeep[] = $languageCodes[$languageCode];
        }
        $this->clearTaxonomyTranslations($taxonomy->id, $languageIdsToKeep);
    }

    /**
     * Gets existing or new taxonomy
     * @return Taxonomy
     */
    private function getTaxonomy(): Taxonomy
    {
        $taxonomy = null;
        if (!is_null($this->taxonomyId)) {
            $taxonomy = Taxonomy::findOrFail($this->taxonomyId);
        } else {
            $taxonomy = Taxonomy::withTrashed()
                ->where('parent_id', '=', $this->parentTaxonomyId)
                ->whereRaw('LOWER(name) = ?', [strtolower($this->getName())])
                ->first();
            if (!$taxonomy) {
                $taxonomy = new Taxonomy();
                $taxonomy->parent_id = $this->parentTaxonomyId;
            } else {
                $taxonomy->restore();
            }
        }
        return $taxonomy;
    }

    /**
     * Updates or creates a taxonomy translation
     * @param int $taxonomyId
     * @param int $languageId
     * @param string $translationName
     * @return TaxonomyTranslation
     * @throws \Throwable
     */
    private function setTaxonomyTranslation(int $taxonomyId, int $languageId, $translationName): TaxonomyTranslation
    {
        $translation = new TaxonomyTranslation();

        // if translation exists, overwrite
        $matchingTranslation = $translation->where([
            'taxonomy_id' => $taxonomyId,
            'language_id' => $languageId
        ])->first();
        if ($matchingTranslation) {
            $translation = $matchingTranslation;
        }

        $translation->fill([
            'taxonomy_id' => $taxonomyId,
            'language_id' => $languageId,
            'name' => is_array($translationName) ? $translationName['singular'] : $translationName
        ])->saveOrFail();

        if (is_array($translationName)) {
            unset($translationName['singular']);
            $translation->updatePlurals($translationName);
        }

        return $translation;
    }

    /**
     * Clears undefined translations
     * @param integer $taxonomyId
     * @param array $languageIdsToKeep
     * @return bool|int|null
     * @throws \Exception
     */
    private function clearTaxonomyTranslations(int $taxonomyId, array $languageIdsToKeep)
    {
        return TaxonomyTranslation
            ::where('taxonomy_id', $taxonomyId)
            ->whereNotIn('language_id', $languageIdsToKeep)
            ->delete();
    }

}
