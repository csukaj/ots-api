<?php

namespace App\Entities;

use App\Facades\Config;

class TranslationUpdaterEntity extends TranslationEntity
{
    /**
     * Replace the dist's example translation with src's file
     */
    public function replaceTranslation(string $isoCode, bool $isExample = false)
    {
        $file = $isoCode . '.json';
        $source = base_path() . '/' . Config::get('cache.frontend_i18n_directory') . '/' . $file;
        $target = base_path() . '/' . Config::get('cache.frontend_i18n_directory_dist');

        if ($isExample)
        {
            $target.= '/example';
        }

        $target.= '/' . $file;

        copy($source, $target);

        exec('sudo chmod ' . $target . ' 777');
    }

    public function update()
    {
        $isoCodes = $this->getIsoCodes();
        foreach ($isoCodes as $isoCode) {
            $this->updateOneByIsoCode($isoCode);
        }
    }

    public function updateOneByIsoCode($isoCode)
    {
        $exampleJsonFilePath = $this->basePathOfExampleTranslations . '/' . $isoCode . '.json';
        if (file_exists($exampleJsonFilePath)) {
            $actualTranslations = [];
            $exampleTranslations = $this->flatten(json_decode(file_get_contents($exampleJsonFilePath), true));
            $actualJsonFilePath = $this->basePathOfActiveTranslations . '/' . $isoCode . '.json';

            if (file_exists($actualJsonFilePath)) {
                $actualTranslations = $this->flatten(json_decode(file_get_contents($actualJsonFilePath), true));
            }

            $thereWasNewKeyInTheExampleJSON = false;
            foreach ($exampleTranslations as $key => $value) {
                if(!isset($actualTranslations[$key])) {
                    $actualTranslations[$key] = $value;
                    $thereWasNewKeyInTheExampleJSON = true;
                }
            }

            if($thereWasNewKeyInTheExampleJSON){
                $this->createJson($isoCode, $this->unflatten($actualTranslations));
            }
        }
    }
}