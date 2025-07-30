<?php

use App\Location;
use App\Manipulators\ProgramRelationSequenceSetter;
use App\Program;
use App\ProgramRelation;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProgramRelationSequenceSetterTest extends TestCase
{
    /**
     * @test
     * @expectedException \App\Exceptions\UserException
     */
    function it_only_accept_array_with_id_and_sequence()
    {
        $data = [
            [
                "id" => 3,
            ]
        ];

        $progRelSeqSet = new ProgramRelationSequenceSetter($data);
        $progRelSeqSet->set();
    }

    /** @test */
    function it_can_set_the_sequence_of_program_relation()
    {
        $programs = $this->generatePrograms(3);

        $programRelations = [];
        for ($i = 1; $i < 3; $i++) {
            $programRelations[] = ProgramRelation::create([
                'parent_id' => $programs->get(0)->id,
                'child_id' => $programs->get($i)->id,
                'sequence' => 0,
                'relative_time_id' => 1
            ]);
        }

        $newSequence = [];
        $sequence = 1;
        foreach ($programRelations as $programRelation) {
            $newSequence[$programRelation->id] = [
                'id' => $programRelation->id,
                'sequence' => $sequence
            ];
            $sequence++;
        }

        $progRelSeqSet = new ProgramRelationSequenceSetter($newSequence);
        $progRelSeqSet->set();

        $programRelations = ProgramRelation::where('parent_id', $programs[0]->id)->get();
        foreach ($programRelations as $programRelation) {
            $this->assertEquals($newSequence[$programRelation->id]['sequence'], $programRelation->sequence);
        }
    }

    /**
     * @param int $count
     * @return Collection
     */
    private function generatePrograms(int $count = 1): Collection
    {
        return factory(Program::class, $count)->create(['location_id' => Location::first()->id]);
    }
}