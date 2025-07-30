<?php

namespace App\Manipulators;

use App\Content;
use App\ContentMedia;
use App\ContentModification;
use App\Entities\ContentEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Traits\FileTrait;
use Modules\Stylersauth\Entities\User;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new Content 
 * instance after the supplied data passes validation
 */
class ContentSetter {
    use FileTrait;

    /**
     * Attributes that can be set from input
     * @var array 
     */
    protected $attributes = [
        'id' => null,
        'title' => null,
        'author' => null,
        'status' => null,
        'lead' => null,
        'lead_image' => null,
        'content' => null,
        'url' => null,
        'meta_title' => null,
        'meta_description' => null,
        'meta_keyword' => null,
        'category' => null,
        'written_by' => null,
        'edited_by' => null
    ];
    private $content;
    
    /**
     * Fields of type Description
     * @var array 
     */
    private $descriptionFields = ['title', 'lead', 'content', 'url', 'meta_title', 'meta_description', 'meta_keyword'];

    /**
     * Constructs Setter and validates input data
     * @param array $attributes
     * @throws UserException
     * @throws \Exception
     */
    public function __construct(array $attributes) {

        //TODO: extend BaseSetter wit caution to descriptionfields

        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                if (in_array($key, $this->descriptionFields)) {
                    $value = array_filter($value, function($v) {
                        return !is_null($v);
                    });
                }
                $this->attributes[$key] = $value;
            }
        }


        if (!isset($attributes['title']) || !isset($attributes['title']['en'])) {
            throw new UserException('Empty title');
        }

        if (Content::isDescriptionExists('title_description_id', $attributes['title'], isset($this->attributes['id']) ? $this->attributes['id'] : null)) {
            throw new UserException('Content page with same name exists.');
        }

        if (isset($this->attributes['author'])) {
            $this->attributes['author_user_id'] = User::where('name', "=", $this->attributes['author'])->firstOrFail()->id;
        } else {
            throw new UserException('Author user can not be found!');
        }
        if (isset($attributes['status'])) {
            $this->attributes['status_taxonomy_id'] = Taxonomy::getTaxonomy($this->attributes['status'], Config::getOrFail('taxonomies.content_status'))->id;
        } else {
            throw new UserException('Bad status');
        }
        if (isset($attributes['category'])) {
            if (Taxonomy::taxonomyExists($this->attributes['category'], Config::get('taxonomies.content_category'))) {
                $this->attributes['category_taxonomy_id'] = Taxonomy::getTaxonomy($this->attributes['category'], Config::get('taxonomies.content_category'))->id;
            } else {
                throw new UserException('Bad category');
            }
        } else {
            $this->attributes['category_taxonomy_id'] = null;
        }
        if (!isset($attributes['url']) || !isset($attributes['url']['en'])) {
            throw new UserException('Empty URL');
        }
        if (Content::isDescriptionExists('url_description_id', $attributes['url'], isset($this->attributes['id']) ? $this->attributes['id'] : null)) {
            throw new UserException('Content page with same url exists.');
        }
    }

    /**
     * Creates new Model or updates if exists
     * @return Content
     * @throws \Exception
     * @throws \Throwable
     */
    public function set() {
        if ($this->attributes['id']) {
            $this->content = Content::findOrFail($this->attributes['id']);
            $this->content->exists = true;
        } else {
            $this->content = new Content();
        }


        $this->content->title_description_id = (new DescriptionSetter($this->attributes['title'], $this->content->title_description_id))->set()->id;
        $this->content->author_user_id = $this->attributes['author_user_id'];
        $this->content->status_taxonomy_id = $this->attributes['status_taxonomy_id'];
        $this->content->category_taxonomy_id = $this->attributes['category_taxonomy_id'];
        if (!empty($this->attributes['written_by'])) {
            $this->content->written_by = $this->attributes['written_by'];
        } else {
            $this->content->written_by = null;
        }
        if (!empty($this->attributes['edited_by'])) {
            $this->content->edited_by = $this->attributes['edited_by'];
        } else {
            $this->content->edited_by = null;
        }
        if (!empty($this->attributes['lead'])) {
            $this->content->lead_description_id = (new DescriptionSetter($this->attributes['lead'], $this->content->lead_description_id))->set()->id;
        } else {
            $this->content->lead_description_id = null;
        }
        if (!empty($this->attributes['content'])) {
            $this->content->content_description_id = (new DescriptionSetter($this->attributes['content'], $this->content->content_description_id))->set()->id;
        } else {
            $this->content->content_description_id = null;
        }
        $this->content->url_description_id = (new DescriptionSetter($this->attributes['url'], $this->content->url_description_id))->set()->id;
        if (!empty($this->attributes['meta_title'])) {
            $this->content->meta_title_description_id = (new DescriptionSetter($this->attributes['meta_title'], $this->content->meta_title_description_id))->set()->id;
        } else {
            $this->content->meta_title_description_id = null;
        }
        if (!empty($this->attributes['meta_description'])) {
            $this->content->meta_description_description_id = (new DescriptionSetter($this->attributes['meta_description'], $this->content->meta_description_description_id))->set()->id;
        } else {
            $this->content->meta_description_description_id = null;
        }
        if (!empty($this->attributes['meta_keyword'])) {
            $this->content->meta_keyword_description_id = (new DescriptionSetter($this->attributes['meta_keyword'], $this->content->meta_keyword_description_id))->set()->id;
        } else {
            $this->content->meta_keyword_description_id = null;
        }

        $this->content->saveOrFail();

        $this->setLeadImage($this->attributes);

        $this->setContentImages();

        $this->logModification();

        return $this->content;
    }

    /**
     * Log content modification
     * @return ContentModification
     * @throws \Throwable
     */
    private function logModification() {
        $modification = new ContentModification();
        $modification->content_id = $this->content->id;
        $modification->editor_user_id = $this->content->author_user_id;
        $modification->new_content = \json_encode((new ContentEntity($this->content))->getFrontendData());
        $modification->saveOrFail();
        return $modification;
    }

    /**
     * Set lead image from attributes
     * @param array $attributes
     * @return ContentMedia
     * @throws \Throwable
     */
    public function setLeadImage(array $attributes) {
        $contentMedia = null;
        $existing = $this->content->leadImages();
        if ($existing) {
            $existing->delete();
        }
        if (!empty($attributes['lead_image']) && !empty($attributes['lead_image']['id'])) {

            $attributesForRestore = [
                'content_id' => $this->content->id,
                'media_role_taxonomy_id' => Config::get('taxonomies.media_roles.lead_image'),
                'mediable_type' => File::class,
                'mediable_id' => $attributes['lead_image']['id']
            ];
            $contentMedia = ContentMedia::createOrRestore($attributesForRestore);
        }
        return $contentMedia;
    }

    /**
     * set Content Images
     * @throws \Throwable
     */
    protected function setContentImages() {
        $existing = $this->content->contentImages();
        if ($existing) {
            $existing->delete();
        }
        $imageIdsInText = self::getImageIdsFromDescription($this->content->content);
        $contentImageTxId = Config::get('taxonomies.media_roles.content_image');
        foreach ($imageIdsInText as $imgId) {
            $attributes = [
                'content_id' => $this->content->id,
                'media_role_taxonomy_id' => $contentImageTxId,
                'mediable_type' => File::class,
                'mediable_id' => $imgId
            ];
            ContentMedia::createOrRestore($attributes);
        }
    }

    /**
     * get Image Ids From Description (html tags)
     * @param Description $description
     * @return array
     */
    static public function getImageIdsFromDescription(Description $description = null) {
        if(!$description){
            return [];
        }
        $contentHTML = (new DescriptionEntity($description))->getFrontendData();
        $found = [];
        foreach ($contentHTML as $text) {
            if (
                    preg_match_all('#<img[^>]+src=[\'"](.+?)[\'"][^>]*>#', $text, $matches, PREG_PATTERN_ORDER)) {
                $paths = $matches[1];
                array_walk($paths, function(&$v) {
                    $v = intval(pathinfo($v, PATHINFO_FILENAME));
                });
                $found = array_merge($found, $paths);
            }
        }
        return array_merge(array_unique($found));
    }

}
