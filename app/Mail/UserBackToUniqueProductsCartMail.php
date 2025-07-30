<?php

namespace App\Mail;

class UserBackToUniqueProductsCartMail extends TemplatedMail
{
    public $templateId = 7;

    /**
     * Create a new message instance.
     *
     * @param $config
     * @param string $language
     */
    public function __construct($config, string $language)
    {
        parent::__construct($config, $language);
    }

    protected function getPlaceHolderDictionary(): array
    {
        return  [
            '{{username}}' => $this->config->username,
            '{{link}}' => $this->config->link
        ];
    }
}

