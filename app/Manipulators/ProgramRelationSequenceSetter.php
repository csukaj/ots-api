<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\ProgramRelation;

class ProgramRelationSequenceSetter
{
    private $programRelationList;

    public function __construct(array $programRelationList)
    {
        $this->programRelationList = $programRelationList;
    }

    /**
     * @throws UserException
     */
    public function set()
    {
        foreach ($this->programRelationList as $data) {
            $this->validate($data);

            $programRelation = ProgramRelation::findOrFail($data['id']);
            $programRelation->sequence = $data['sequence'];
            $programRelation->save();
        }
    }

    /**
     * @param $data
     * @throws UserException
     */
    private function validate($data)
    {
        if (!array_key_exists('id', $data) ||
            !array_key_exists('sequence', $data)) {
            throw new UserException('Invalid program relation list format');
        }
    }
}