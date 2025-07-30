<?php

namespace App\Manipulators;

use App\AgeRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Traits\FileTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Manipulator to create a new AgeRange
 * instance after the supplied data passes validation
 */
class AgeRangeSetter extends BaseSetter
{

    use FileTrait;

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'age_rangeable_type' => null,
        'age_rangeable_id' => null,
        'from_age' => null,
        'to_age' => null,
        'name_taxonomy_id' => null,
        'taxonomy' => null,
        'banned' => false,
        'free' => false
    ];
    private $nameTaxonomy;

    /**
     * AgeRangeSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        if (!empty($attributes['name_taxonomy'])) {
            $this->nameTaxonomy = $attributes['name_taxonomy'];
        }

        if (count(AgeRange::getAgeRangesInInterval(
            $this->attributes['age_rangeable_type'],
            $this->attributes['age_rangeable_id'],
            $this->attributes['from_age'],
            $this->attributes['to_age'],
            $this->attributes['id']
        ))) {
            throw new UserException('Age range overlap.');
        }

        if (empty($this->nameTaxonomy) && empty($this->attributes['taxonomy'])) {
            throw new UserException('Empty taxonomy.');
        }

    }

    /**
     * Creates new age range and throws error in case of any overlap
     * @return AgeRange
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     */
    public function set(): AgeRange
    {
        if ($this->attributes['taxonomy']) {
            $nameTx = Taxonomy::createOrUpdateTaxonomy($this->attributes['taxonomy'],
                Config::get('taxonomies.age_range'));
        } else {
            $nameTx = Taxonomy::getOrCreateTaxonomy($this->nameTaxonomy, Config::get('taxonomies.age_range'));
        }

        if (is_null($this->attributes['id'])) {
            $this->validateNameTaxonomy($nameTx->id);
        }

        $this->attributes['name_taxonomy_id'] = $nameTx->id;

        if ($this->attributes['id']) {
            $ageRange = AgeRange::findOrFail($this->attributes['id']);
            if ($ageRange->name_taxonomy_id != $this->attributes['name_taxonomy_id'] && $ageRange->name_taxonomy_id == Config::getOrFail('taxonomies.age_ranges.adult.id')) {
                throw new UserException('You can not change default age range name');
            }
        } else {
            $ageRange = new AgeRange($this->attributes);
        }
        $ageRange->fill($this->attributes)->saveOrFail();

        return $ageRange;
    }

    /**
     * @param $nameTaxonomyId
     * @throws UserException
     */
    public function validateNameTaxonomy($nameTaxonomyId)
    {
        $rangeNumber = AgeRange
            ::forAgeRangeable($this->attributes['age_rangeable_type'], $this->attributes['age_rangeable_id'])
            ->where('name_taxonomy_id', $nameTaxonomyId)
            ->count();

        if ($rangeNumber > 0) {
            throw new UserException('Age range name already used.');
        }
    }

}
