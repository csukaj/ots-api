<?php

namespace Modules\Stylerstaxonomy\Database\Seeders;

use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyTranslation;
use Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural;

/**
 * @codeCoverageIgnore
 */
trait TaxonomySeederTrait
{

    /**
     *
     * @param int $id
     * @param string $name
     * @param Taxonomy $parentTx
     * @param array $properties
     * @return Taxonomy
     * @throws \Exception
     */
    protected function saveTaxonomy(int $id, string $name, Taxonomy $parentTx = null, array $properties = [])
    {
        $tx = Taxonomy::loadTaxonomy($id);
        $tx->name = is_array($properties) && !empty($properties['name']) ? $properties['name'] : $name;
        $tx->is_required = !empty($properties['is_required']);
        $tx->is_readonly = true;
        $tx->is_searchable = !empty($properties['is_searchable']);
        $tx->type = empty($properties['type']) ? Config::getOrFail('stylerstaxonomy.type_unknown') : $properties['type'];
        $tx->icon = empty($properties['icon']) ? null : $properties['icon'];
        $tx->priority = (isset($properties['priority'])) ? (int)$properties['priority'] : null;
        $tx->relation = (isset($properties['relation'])) ? $properties['relation'] : null;
        $tx->save();
        if ($parentTx) {
            $tx->makeChildOf($parentTx);
        }
        $this->saveTranslations($properties, $tx);
        return $tx;
    }

    protected function saveTaxonomyPath($taxonomyPath, Taxonomy $parentTx = null)
    {
        $pathArray = explode('.', $taxonomyPath);
        $txName = array_pop($pathArray);

        return $this->saveTaxonomy(Config::getOrFail($taxonomyPath), $txName, $parentTx);
    }

    /**
     * Seeds a taxonomy with immediate children.
     * Key of children array must be the correct plural form of parent. Childrean must be in "name => id" form
     * Not working for additional properties
     *
     * @param string $taxonomyPath
     * @return type
     * @throws \Exception
     */
    protected function saveTaxonomyWithChildren(string $taxonomyPath)
    {
        $parentTx = $this->saveTaxonomyPath($taxonomyPath);

        foreach (Config::getOrFail(str_plural($taxonomyPath)) as $name => $idOrProperties) {
            $id = is_int($idOrProperties) ? $idOrProperties : $idOrProperties['id'];
            $properties = is_int($idOrProperties) ? [] : $idOrProperties;
            $this->saveTaxonomy($id, $name, $parentTx, $properties);
        }
        return $parentTx;
    }

    /**
     * @param array $data
     * @param Taxonomy $tx
     */
    protected function saveTranslations(array $data, Taxonomy $tx)
    {
        if (empty($data['translations'])) {
            return;
        }
        $defaultLanguageCode = Language::getDefault()->iso_code;
        if(!isset($data['translations'][$defaultLanguageCode])){
            $data['translations'][$defaultLanguageCode] = $tx->name;
        }
        foreach ($data['translations'] as $languageCode => $text) {
            $language = Language::where('iso_code', $languageCode)->firstOrFail();
            $txTr = TaxonomyTranslation::getOrNew($tx->id, $language->id);

            if ($languageCode != $defaultLanguageCode) {
                $txTr->name = (is_array($text) && isset($text['singular'])) ? $text['singular'] : $text;
            } else {
                $txTr->name = $tx->name;
            }
            $txTr->saveOrFail();

            if (is_array($text) && isset($text['plurals'])) {
                $this->savePlurals($text, $txTr, $language);

            }
        }
    }

    /**
     * @param array $data
     * @param TaxonomyTranslation $translation
     * @param Language $language
     */
    private function savePlurals(array $data, TaxonomyTranslation $translation, Language $language)
    {
        if (empty($data['plurals'])) {
            return;
        }
        foreach ($data['plurals'] as $pluralTxName => $pluralName) {
            $typeTxId = Config::getOrFail("taxonomies.languages.{$language->name->name}.plurals.{$pluralTxName}.id");
            $pl = TaxonomyTranslationPlural::getOrNew($translation->id, $typeTxId);
            $pl->name = $pluralName;
            $pl->saveOrFail();
        }
    }

}
