<?php

/**
 * Get the language specific content from $translations
 *
 * Translation looks like:
 *
 * array:1 [
 *  "en" => "Standard Room",
 *  "hu" => "Sztenderd szoba",
 *  ...
 * ]
 *
 * @param string $language
 * @param array $translations
 * @param string $fallbackLanguage
 * @return string
 * @throws Exception
 */
function languageContent(string $language, $translations, $fallbackLanguage = 'en')
{
    if (is_array($translations)) {

        if (!empty($translations[$language])) {
            return $translations[$language];
        }

        if (empty($translations[$fallbackLanguage])) {
            throw new \Exception('Missing "' . $fallbackLanguage . '" from content array!');
        }

        return $translations[$fallbackLanguage];
    } else {
        if (!empty($translations->$language)) {
            return $translations->$language;
        }

        if (empty($translations->$fallbackLanguage)) {
            throw new \Exception('Missing "' . $fallbackLanguage . '" from content array!');
        }

        return $translations->$fallbackLanguage;
    }
}

function getLanguageBySite($site)
{
    return \App\Facades\Config::getOrFail('ots.site_languages')[$site];
}