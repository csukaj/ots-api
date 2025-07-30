<?php
namespace Modules\Stylerstaxonomy\Entities;

class DescriptionEntity
{

    protected $description;

    public function __construct(Description $description)
    {
        $this->description = $description;
    }

    public function getFrontendData(): array
    {
        $return = [Language::getDefault()->iso_code => $this->description->description];
        $translations = $this->description->translations;
        foreach ($translations as $translation) {
            $return[$translation->language->iso_code] = $translation->description;
        }
        return $return;
    }

    static public function getCollection($descriptions): array
    {
        $return = [];
        foreach ($descriptions as $description) {
            $return[] = (new self($description))->getFrontendData();
        }
        return $return;
    }
}
