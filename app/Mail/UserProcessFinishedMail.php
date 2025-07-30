<?php

namespace App\Mail;

class UserProcessFinishedMail extends TemplatedMail
{
    public $templateId = 3;

    /**
     * Create a new message instance.
     *
     * @param $config
     * @param $language
     */
    public function __construct($config, string $language)
    {
        parent::__construct($config, $language);

    }

    protected function getPlaceHolderDictionary(): array
    {
        return  [
            '{{username}}' => $this->config->username
        ];
    }
}
