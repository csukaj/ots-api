<?php

namespace Modules\Stylerstaxonomy\Entities;

use App\Facades\Config;

class TaxonomyTranslationEntity
{

    protected $taxonomyTranslation;

    public function __construct(TaxonomyTranslation $taxonomyTranslation)
    {
        $this->taxonomyTranslation = $taxonomyTranslation;
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = [])
    {
        $return = ['singular' => $this->taxonomyTranslation->name];
        foreach ($this->taxonomyTranslation->plurals as $plural) {
            $return[self::getPluralShortName($plural->typeTaxonomy->id)] = $plural->name;
        }

        return $return;
    }

    static private $pluralShortNames;

    /**
     * @param $taxonomyId
     * @return mixed
     * @throws \Exception
     */
    static private function getPluralShortName($taxonomyId)
    {
        if (is_null(self::$pluralShortNames)) {
            self::$pluralShortNames = [];
            $languages = Config::getOrFail('taxonomies.languages');
            foreach ($languages as $language) {
                foreach ($language['plurals'] as $name => $data) {
                    self::$pluralShortNames[$data['id']] = $name;
                }
            }
        }
        return self::$pluralShortNames[$taxonomyId];
    }

    static public function getCollection($taxonomyTranslations, array $additions = [], array $dependencies = [])
    {
        $return = [];
        foreach ($taxonomyTranslations as $taxonomyTranslation) {
            $return[$taxonomyTranslation->language->iso_code] = (new self($taxonomyTranslation))->getFrontendData($additions);
        }
        return $return;
    }

}
