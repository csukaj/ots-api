<?php

namespace App\Manipulators;

use App\Email;
use App\Exceptions\EmailException;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;


/**
 * Manipulator to update a new Email
 * instance after the supplied data passes validation
 */
class EmailSetter
{
    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'type' => null,
        'subject' => null,
        'content' => null
    ];

    /**
     * Fields of type Description
     * @var array
     */
    private $descriptionFields = ['subject', 'content'];

    /**
     * Constructs Setter
     * @param array $attributes
     * @throws EmailException
     */
    public function __construct(array $attributes)
    {
        //TODO: extend basesetter with caution to descriptionfields
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                if (in_array($key, $this->descriptionFields)) {
                    $value = array_filter($value, function ($v) {
                        return !is_null($v);
                    });
                }
                $this->attributes[$key] = $value;
            }
        }
    }

    /**
     * Creates new Model or updates if exists
     * @return Email
     * @throws EmailException
     * @throws \Throwable
     */
    public function set(): Email
    {
        if ($this->attributes['id']) {
            $email = Email::findOrFail($this->attributes['id']);
        } else {
            throw new EmailException('Save is not implemented');
        }

        if (!empty($this->attributes['subject'])) {
            $email->subject_description_id = (
            new DescriptionSetter($this->attributes['subject'], $email->subject_description_id))->set()->id;
        } else {
            $email->subject_description_id = null;
        }

        if (!empty($this->attributes['content'])) {
            $email->content_description_id = (
            new DescriptionSetter($this->attributes['content'], $email->content_description_id))->set()->id;
        } else {
            $email->content_description_id = null;
        }

        $email->saveOrFail();

        return $email;
    }

}
