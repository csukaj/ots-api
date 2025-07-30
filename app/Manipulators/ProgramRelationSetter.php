<?php
namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\ProgramRelation;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Manipulator to create a new Program
 * instance after the supplied data passes validation
 */
class ProgramRelationSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'parent_id' => null,
        'child_id' => null,
        'sequence' => null,
        'relative_time' => null,
        'embarkation_type' => null,
        'embarkation_direction' => null,
    ];

    /**
     * Construct Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        //TODO: extend BaseSetter

        $this->validateRelativeTime($attributes);
        $this->validateChild($attributes);

        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            } else {
                $this->properties[] = ['name' => $key, 'value' => $value];
            }
        }
        
        if (isset($attributes['embarkation_type'])) {
            $this->attributes['embarkation_type_taxonomy_id'] = Taxonomy::getOrCreateTaxonomy($this->attributes['embarkation_type']['name'], Config::getOrFail('taxonomies.embarkation_type'))->id;
        }
        
        if (isset($attributes['embarkation_direction'])) {
            $this->attributes['embarkation_direction_taxonomy_id'] = Taxonomy::getOrCreateTaxonomy($this->attributes['embarkation_direction']['name'], Config::getOrFail('taxonomies.embarkation_direction'))->id;
        }
    }

    /**
     * Create new model or updates if exists
     * @return ProgramRelation
     */
    public function set()
    {
        $programRelation = new ProgramRelation();
        if (!empty($this->attributes['id'])) {
            $programRelation = ProgramRelation::findOrFail($this->attributes['id']);
        } elseif (isset($this->attributes['parent_id']) && $this->attributes['child_id']) {
            $existingCount = ProgramRelation
                ::forParent($this->attributes['parent_id'])
                ->forChild($this->attributes['child_id'])
                ->count();
            if ((bool)$existingCount) {
                throw new UserException('A ProgramRelation for this parent and child already exists.');
            }   
        }

        if (isset($this->attributes['parent_id'])) {
            $programRelation->parent_id = $this->attributes['parent_id'];
        }

        if (isset($this->attributes['child_id'])) {
            $programRelation->child_id = $this->attributes['child_id'];
        }

        if (isset($this->attributes['sequence'])) {
            $programRelation->sequence = $this->attributes['sequence'];
        }

        if (isset($this->attributes['relative_time'])) {
            $relativeTime = (new RelativeTimeSetter($this->attributes['relative_time']))->set();
            $programRelation->relative_time_id = $relativeTime->id;
        }
        
        if (isset($this->attributes['embarkation_type_taxonomy_id'])) {
            $programRelation->embarkation_type_taxonomy_id = $this->attributes['embarkation_type_taxonomy_id'];
        }
        if (isset($this->attributes['embarkation_direction_taxonomy_id'])) {
            $programRelation->embarkation_direction_taxonomy_id = $this->attributes['embarkation_direction_taxonomy_id'];
        }

        $programRelation->saveOrFail();

        return $programRelation;
    }

    private function validateRelativeTime(array $attributes)
    {
        if (!isset($attributes['relative_time']) || empty($attributes['relative_time'])) {
            throw new UserException('Relative time is required!');
        }
    }

    private function validateChild(array $attributes)
    {
        if (!isset($attributes['child_id']) || empty($attributes['child_id'])) {
            throw new UserException('Child ID is required!');
        }
    }
}
