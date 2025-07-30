<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Location;
use App\Program;
use App\ProgramDescription;
use App\Traits\HardcodedIdSetterTrait;
use App\Traits\PropertyCategorySetterTrait;
use Modules\Stylersmedia\Manipulators\GallerySetter;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new Program
 * instance after the supplied data passes validation
 */
class ProgramSetter
{
    use PropertyCategorySetterTrait, HardcodedIdSetterTrait;

    const CONNECTION_COLUMN = 'program_id';

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'type' => null,
        'name' => null,
        'descriptions' => null,
        'location' => null,
        'organization_id' => null
    ];

    private $properties = [];

    /**
     * Construct Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        //TODO: extend baseSetter
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            } else {
                $this->properties[] = ['name' => $key, 'value' => $value];
            }
        }

        if (empty($this->attributes['name'])) {
            throw new UserException('Invalid or empty name description');
        }
    }

    /**
     * Create new model or updates if exists
     * @param bool $hardcodedId
     * @return Program
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     */
    public function set($hardcodedId = false): Program
    {
        if (!$hardcodedId && $this->attributes['id']) {
            $program = Program::findOrFail($this->attributes['id']);
            $nameDescription = (new DescriptionSetter($this->attributes['name'], $program->name_description_id))->set();
            $program->load('name');
        } else {
            $nameDescription = (new DescriptionSetter($this->attributes['name']))->set();
            $program = new Program();
            if ($hardcodedId && $this->attributes['id']) {
                $program->id = $this->attributes['id'];
            }
        }

        $program->type_taxonomy_id = Config::getOrFail("taxonomies.program_types.{$this->attributes['type']}");
        $program->name_description_id = $nameDescription->id;
        $program->organization_id = $this->attributes['organization_id'];

        if (empty($this->attributes['location'])) {
            $location = new Location();
            $location->saveOrFail();
            $program->location_id = $location->id;
        } else {
            $program->location_id = (new LocationSetter($this->attributes['location']))->set()->id;
        }

        $program->saveOrFail();
        if ($hardcodedId && $this->attributes['id']) {
            $this->updateAutoIncrement($program);
        }

        if ($hardcodedId || !$this->attributes['id']) { // Do not update. Done @ prgClSetter
            $this->setPropertyCategories($program, !$hardcodedId);
        }

        if (!$this->attributes['id']) {
            // create default gallery for program
            (new GallerySetter([
                'galleryable_id' => $program->id,
                'galleryable_type' => Program::class,
                'role_taxonomy_id' => Config::getOrFail('taxonomies.gallery_roles.frontend_gallery')
            ]))->set();
        }

        if (!empty($this->attributes['descriptions']) && !empty($this->attributes['descriptions']['long_description'])) {
            ProgramDescription::setDescription(
                self::CONNECTION_COLUMN, $program->id,
                Config::getOrFail('taxonomies.program_descriptions.long_description'),
                $this->attributes['descriptions']['long_description']
            );
        } else {
            ProgramDescription::deleteDescription(self::CONNECTION_COLUMN, $program->id,
                Config::getOrFail('taxonomies.program_descriptions.long_description'));
        }

        return $program;
    }

}
