<?php

namespace App;

use App\Facades\Config;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Content
 *
 * @property int $id
 * @property int $title_description_id
 * @property int $author_user_id
 * @property int $status_taxonomy_id
 * @property int $lead_description_id
 * @property int $content_description_id
 * @property int $url_description_id
 * @property int $meta_title_description_id
 * @property int $meta_description_description_id
 * @property int $meta_keyword_description_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property int $category_taxonomy_id
 * @property string $written_by
 * @property string $edited_by
 * @property-read User $author
 * @property-read Taxonomy $category
 * @property-read Description $content
 * @property-read Collection|ContentMedia[] $contentImages
 * @property-read Collection|File[] $files
 * @property-read Description $lead
 * @property-read Collection|ContentMedia[] $leadImages
 * @property-read Description $metaDescription
 * @property-read Description $metaKeyword
 * @property-read Description $metaTitle
 * @property-read Collection|ContentModification[] $modifications
 * @property-read Taxonomy $status
 * @property-read Description $title
 * @property-read Description $url
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content ofCategory($categoryId)
 * @method static \Illuminate\Database\Query\Builder|\App\Content onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content page()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content post()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content published()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereAuthorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereCategoryTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereContentDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereEditedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereLeadDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereMetaDescriptionDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereMetaKeywordDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereMetaTitleDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereStatusTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereTitleDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereUrlDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Content whereWrittenBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Content withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Content withoutTrashed()
 */
class Content extends Model {

    use SoftDeletes,
        CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title_description_id',
        'author_user_id',
        'status_taxonomy_id',
        'lead_description_id',
        'content_description_id',
        'url_description_id',
        'meta_title_description_id',
        'meta_description_description_id',
        'meta_keyword_description_id',
        'category_taxonomy_id',
        'written_by',
        'edited_by'
    ];

    protected $cascadeDeletes = ['title','lead','content', 'url', 'metaTitle', 'metaDescription', 'metaKeyword','modifications'];

    /**
     * Relation to title Description
     * 
     * @return HasOne
     */
    public function title() {
        return $this->hasOne(Description::class, 'id', 'title_description_id');
    }

    /**
     * Relation to author User
     * 
     * @return HasOne
     */
    public function author() {
        return $this->hasOne(User::class, 'id', 'author_user_id');
    }

    /**
     * Relation to status Taxonomy
     * 
     * @return HasOne
     */
    public function status() {
        return $this->hasOne(Taxonomy::class, 'id', 'status_taxonomy_id');
    }

    /**
     * Relation to category Taxonomy
     * 
     * @return HasOne
     */
    public function category() {
        return $this->hasOne(Taxonomy::class, 'id', 'category_taxonomy_id');
    }

    /**
     * Relation to lead Description
     * 
     * @return HasOne
     */
    public function lead() {
        return $this->hasOne(Description::class, 'id', 'lead_description_id');
    }

    /**
     * Relation to content Description
     * 
     * @return HasOne
     */
    public function content() {
        return $this->hasOne(Description::class, 'id', 'content_description_id');
    }

    /**
     * Relation to url Description
     * 
     * @return HasOne
     */
    public function url() {
        return $this->hasOne(Description::class, 'id', 'url_description_id');
    }

    /**
     * Relation to metaTitle Description
     * 
     * @return HasOne
     */
    public function metaTitle() {
        return $this->hasOne(Description::class, 'id', 'meta_title_description_id');
    }

    /**
     * Relation to metaDescription Description
     * 
     * @return HasOne
     */
    public function metaDescription() {
        return $this->hasOne(Description::class, 'id', 'meta_description_description_id');
    }

    /**
     * Relation to metaKeyword Description
     * 
     * @return HasOne
     */
    public function metaKeyword() {
        return $this->hasOne(Description::class, 'id', 'meta_keyword_description_id');
    }

    /**
     * Relation to modifications
     * 
     * @return HasMany
     */
    public function modifications() {
        return $this->hasMany(ContentModification::class, 'content_id', 'id')->orderBy('id', 'desc');
    }

    /**
     * Relation to attached files
     * 
     * @return MorphToMany
     */
    public function files() {
        return $this->morphToMany(File::class, 'mediable', 'content_media');
    }

    /**
     * Relation to lead Images
     * 
     * @return Builder
     */
    public function leadImages() {
        return $this->hasMany(ContentMedia::class, 'content_id', 'id')
                        ->where('media_role_taxonomy_id', Config::get('taxonomies.media_roles.lead_image'))
                        ->where('mediable_type', File::class);
    }

    /**
     * Relation to content Images
     *
     * @return Builder
     * @throws \Exception
     */
    public function contentImages() {
        return $this->hasMany(ContentMedia::class, 'content_id', 'id')
                        ->where('media_role_taxonomy_id', Config::getOrFail('taxonomies.media_roles.content_image'))
                        ->where('mediable_type', File::class);
    }

    /**
     * Scope a query to only include published content.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopePublished($query) {
        return $query->where('status_taxonomy_id', '=', Config::get('taxonomies.content_statuses.published'));
    }

    /**
     * Scope a query to only include page type content.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopePage($query) {
        return $query->whereNull('category_taxonomy_id');
    }

    /**
     * Scope a query to only post type content.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopePost($query) {
        return $query->whereNotNull('category_taxonomy_id');
    }

    /**
     * Scope a query to only include content of specified category.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeOfCategory($query, $categoryId) {
        return $query->where('category_taxonomy_id', '=', $categoryId);
    }

    /**
     * get CreatedAt Attribute
     * 
     * @param string $date
     * @return string
     */
    public function getCreatedAtAttribute($date) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
    }

    /**
     * get UpdatedAt Attribute
     * 
     * @param string $date
     * @return string
     */
    public function getUpdatedAtAttribute($date) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
    }

    /**
     * get All active Urls that assigned to a content
     * 
     * @return array
     */
    public static function getAllUrl() {
        $descriptions = Description
                        ::join('contents', 'descriptions.id', '=', 'contents.url_description_id')
                        ->where('status_taxonomy_id', '=', Config::get('taxonomies.content_statuses.published'))
                        ->whereNull('contents.deleted_at')
                        ->get();

        return DescriptionEntity::getCollection($descriptions);
    }

    /**
     * find content By any of Description type field
     * it searches is translations too
     * 
     * @param string $field Field name in contents table to search in
     * @param array $translations Value to search for
     * @param int $ignoreId Content id to ignore
     * @return Collection
     * @static
     */
    public static function findByDescription(string $field, array $translations, int $ignoreId = null) {
        $descriptions_query = Content
                ::select('contents.*')
                ->join('descriptions', 'contents.' . $field, '=', 'descriptions.id')
                ->whereIn('descriptions.description', array_values($translations))
                ->whereNull('descriptions.deleted_at');
        if (!empty($ignoreId)) {
            $descriptions_query->where('contents.id', '!=', $ignoreId);
        }

        $query = Content
                ::select('contents.*')
                ->join('descriptions', 'contents.' . $field, '=', 'descriptions.id')
                ->join('description_translations', 'descriptions.id', '=', 'description_translations.description_id')
                ->whereIn('description_translations.description', array_values($translations))
                ->whereNull('descriptions.deleted_at')
                ->whereNull('description_translations.deleted_at');
        if (!empty($ignoreId)) {
            $query->where('contents.id', '!=', $ignoreId);
        }
        $query->union($descriptions_query);
        return $query->get();
    }

    /**
     * Check if there is any Content that have any of searched texts 
     * in any of its Description type fields
     * it searches is translations too
     * 
     * @param string $field Field name in contents table to search in
     * @param array $translations Value to search for
     * @param int $ignoreId Content id to ignore
     * @return boolean
     * @static
     */
    public static function isDescriptionExists(string $field, array $translations, int $ignoreId = null) {
        return (bool) count(self::findByDescription($field, $translations, $ignoreId));
    }

}
