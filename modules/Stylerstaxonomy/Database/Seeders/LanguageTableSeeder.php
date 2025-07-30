<?php

namespace Modules\Stylerstaxonomy\Database\Seeders;

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Entities\Language;

/**
 * @codeCoverageIgnore
 */
class LanguageTableSeeder extends Seeder
{

    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // language taxonomy
        $languageTx = $this->saveTaxonomyPath('taxonomies.language');

        foreach (Config::get('taxonomies.languages') as $name => $properties) {
            // language taxonomies
            $tx = $this->saveTaxonomy($properties['id'], $name, $languageTx);

            // language model
            try {
                $language = Language::findOrFail($properties['language_id']);
            } catch (ModelNotFoundException $e) {
                $language = new Language();
                $language->id = $properties['language_id'];
            }
            $language->name_taxonomy_id = $properties['id'];
            $language->iso_code = $properties['iso_code'];
            $language->date_format = $properties['date_format'];
            $language->time_format = $properties['time_format'];
            $language->first_day_of_week = $properties['first_day_of_week'];
            if (Config::get('taxonomies.default_language') == $name) {
                $language->is_default = 1;
            }
            $language->save();

            // plural parent taxonomy
            $pluralParentTx = $this->saveTaxonomy($properties['plural'], 'plural', $tx);

            foreach ($properties['plurals'] as $pluralProperties) {
                // plural child taxonomies
                $pluralChildTx = $this->saveTaxonomy($pluralProperties['id'], $pluralProperties['name'],
                    $pluralParentTx);
            }

        }
    }

}
