<?php

namespace App\Mail;

use App\Email;
use App\Entities\EmailEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class TemplatedMail extends Mailable
{

    use Queueable, SerializesModels;

    public $config;

    public $templateId = null;

    protected $language;

    /**
     * Create a new message instance.
     *
     * @param $config
     * @param string $language
     */
    public function __construct($config, string $language)
    {
        $this->config = $config;
        $this->language = $language;
    }

    protected abstract function getPlaceHolderDictionary(): array;

    protected function populatePlaceHolders(string $content): string
    {
        return strtr($content, $this->getPlaceHolderDictionary());
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emailEntity = (new EmailEntity(Email::findOrFail($this->templateId)))->getFrontendData();

        /*
         * @todo @ivan @20190403 - fastfix
         * Adatbazisban igy van bent sok helyen:
         *      href="/{{link}}"
         * e helyett:
         *      href="{{link}}"
         */
        $content = $this->populatePlaceHolders($emailEntity['content'][$this->language]);
        $content = str_replace('href="/', 'href="', $content);

        return $this->view('emails.dynamic-template')
            ->from(env('OTS_MAIL_FROM_ADDRESS'), env('OTS_MAIL_NAME'))
            ->subject($emailEntity['subject'][$this->language])
            ->with(['content' => $content]);
    }

}