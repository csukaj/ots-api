<?php

namespace Modules\Stylerstaxonomy\Manipulators;

use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionTranslation;
use Modules\Stylerstaxonomy\Entities\Language;

class DescriptionSetter {

    private $translations = [];
    private $descriptionId = null;
    private $connection;

    public function __construct(array $translations = [], $descriptionId = null) {
        $this->translations = $translations;
        $this->descriptionId = $descriptionId;
        //TODO: use Validator facade
    }

    /**
     * Creates or updates a description with its translations
     * @return Description
     * @throws \Exception
     * @throws \Throwable
     */
    public function set(): Description {
        $defaultLanguage = Language::getDefault($this->connection);
        $languageCodes = Language::getLanguageCodes();
        $languageIdsToKeep = [];

        $description = $this->getDescription();
        if (isset($this->translations[$defaultLanguage->iso_code])) {
            $description->description = $this->translations[$defaultLanguage->iso_code];
        }
        if (empty($this->translations)) {
            if ($description->exists) {
                $description->delete();
                $this->descriptionId = null;
                $description = $this->getDescription();
            }
            return $description;
        }
        $description->saveOrFail();

        foreach ($this->translations as $languageCode => $translation) {
            if ($languageCode == $defaultLanguage->iso_code || is_null($translation) || $translation === '') {
                continue;
            }
            if (!isset($languageCodes[$languageCode])) {
                throw new \Exception("Invalid language code: `{$languageCode}`");
            }
            $this->setDescriptionTranslation($description->id, $languageCodes[$languageCode], $translation);
            $languageIdsToKeep[] = $languageCodes[$languageCode];
        }
        $this->clearDescriptionTranslations($description->id, $languageIdsToKeep);

        $description->load('translations');
        return $description;
    }

    /**
     * Gets existing or new description
     * @return Description
     */
    private function getDescription(): Description {
        $description = new Description();
        if (!is_null($this->descriptionId)) {
            $description = Description::withTrashed()->findOrFail($this->descriptionId);
            if($description->trashed()){
                $description->restore();
            }
        }
        return $description;
    }

    /**
     * Updates or creates a description translation
     * @param int $descriptionId
     * @param int $languageId
     * @param string $description
     * @return DescriptionTranslation
     * @throws \Throwable
     */
    private function setDescriptionTranslation($descriptionId, $languageId, $description): DescriptionTranslation {
        $translation = new DescriptionTranslation();

        // if translation exists, overwrite
        $matchingTranslation = $translation->where([
                    'description_id' => $descriptionId,
                    'language_id' => $languageId
                ])->first();
        if ($matchingTranslation) {
            $translation = $matchingTranslation;
        }

        $translation->fill([
            'description_id' => $descriptionId,
            'language_id' => $languageId,
            'description' => $description
        ])->saveOrFail();
        return $translation;
    }

    /**
     * Clears undefined translations
     * @param integer $descriptionId
     * @param array $languageIdsToKeep
     * @return int
     * @throws \Exception
     */
    private function clearDescriptionTranslations($descriptionId, $languageIdsToKeep) {
        return DescriptionTranslation
            ::where('description_id', $descriptionId)
            ->whereNotIn('language_id', $languageIdsToKeep)
            ->delete();
    }

}
