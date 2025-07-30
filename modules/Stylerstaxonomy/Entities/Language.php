<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

/**
 * Modules\Stylerstaxonomy\Entities\Language
 *
 * @property int $id
 * @property int $name_taxonomy_id
 * @property string $iso_code
 * @property string $date_format
 * @property string $time_format
 * @property string $first_day_of_week
 * @property bool $is_default
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $name
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Language onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereFirstDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereIsoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereNameTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Language whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Language withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Language withoutTrashed()
 * @mixin \Eloquent
 */
class Language extends Model
{

    use SoftDeletes;

    protected $fillable = ['name_taxonomy_id', 'iso_code', 'date_format', 'time_format', 'first_day_of_week'];

    public function name(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'name_taxonomy_id');
    }

    static public function getLanguageCodes()
    {
        return self::all()->pluck('id', 'iso_code')->toArray();
    }

    private static $defaultLanguage = null;
    private static $defaultLanguageDatabase = null;

    /**
     * Returns default language object
     * @return Language
     */
    static public function getDefault($database = null)
    {
        if (is_null(self::$defaultLanguage) || self::$defaultLanguageDatabase != $database) {
            self::$defaultLanguage = Language::findOrFail(Config::get('taxonomies.languages.' . Config::get('taxonomies.default_language'))['language_id']);
        }
        return self::$defaultLanguage;
    }

}
