<?php

namespace App\Entities;

use App\Content;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ContentEntity extends Entity
{

    protected $content;

    public function __construct(Content $content)
    {
        parent::__construct();

        $this->content = $content;
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->content->id,
            'title' => $this->getDescriptionWithTranslationsData($this->content->title),
            'category' => ($this->content->category) ? $this->getTxTranslations($this->content->category) : null,
            'author' => $this->content->author->name,
            'lead' => ($this->content->lead) ? $this->getDescriptionWithTranslationsData($this->content->lead) : null,
            'content' => ($this->content->content) ? $this->getDescriptionWithTranslationsData($this->content->content) : null,
            'url' => $this->getDescriptionWithTranslationsData($this->content->url),
            'meta_title' => ($this->content->metaTitle) ? $this->getDescriptionWithTranslationsData($this->content->metaTitle) : null,
            'meta_description' => ($this->content->metaDescription) ? $this->getDescriptionWithTranslationsData($this->content->metaDescription) : null,
            'meta_keyword' => ($this->content->metaKeyword) ? $this->getDescriptionWithTranslationsData($this->content->metaKeyword) : null,
            'created_at' => $this->content->created_at,
            'written_by' => $this->content->written_by,
            'edited_by' => $this->content->edited_by
        ];

        if (in_array('frontend', $additions)) {
            $return['updated_at'] = $this->content->updated_at;
        } else { // admin
            $return['status'] = $this->content->status->name;
            $return['category'] = ($this->content->category) ? $this->content->category->name : null;
            $return['modifications'] = ContentModificationEntity::getCollection($this->content->modifications);
        }
        $leadImage = $this->content->leadImages->first();
        $return['lead_image'] = $leadImage ? (new FileEntity(File::findOrFail($leadImage->mediable_id)))->getFrontendData() : null;

        return $return;
    }

    protected function getTxTranslations(Taxonomy $category)
    {
        $return = null;
        if (!empty($category)) {
            $tx = new TaxonomyEntity($category);
            return $tx->getFrontendData(['translations']);
        }
        return $return;
    }

}
