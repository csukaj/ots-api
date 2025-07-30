<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ProgramRelationEntity;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\ProgramRelationSequenceSetter;
use App\Manipulators\ProgramRelationSetter;
use App\ProgramRelation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ProgramRelationController extends ResourceController
{

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index(Request $request = null): JsonResponse
    {
        $parentId = $request->get('parent_id');
        $programs = $parentId ?
            ProgramRelation::where('parent_id', '=', $parentId)
                ->orderBy('sequence', 'ASC')
                ->orderBy('id', 'DESC')
                ->get() :
            ProgramRelation::all();

        $options = [
            'types' => TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.embarkation_type'))->getChildren(),
                ['translations_with_plurals']),
            'directions' => TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.embarkation_direction'))->getChildren(),
                ['translations_with_plurals'])
        ];

        return response()->json([
            'success' => true,
            'data' => ProgramRelationEntity::getCollection($programs, ['child', 'embarkation']),
            'options' => $options
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return array
     */
    public function store(Request $request): JsonResponse
    {
        $programRelation = (new ProgramRelationSetter($request->all()))->set();

        return response()->json([
            'success' => true,
            'data' => (new ProgramRelationEntity($programRelation))->getFrontendData(['child', 'embarkation'])
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return array
     */
    public function show($id): JsonResponse
    {
        $programRelation = ProgramRelation::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => (new ProgramRelationEntity($programRelation))->getFrontendData(['child', 'embarkation'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return array
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $programRelation = (new ProgramRelationSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new ProgramRelationEntity($programRelation))->getFrontendData(['child', 'embarkation'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return array
     */
    public function destroy($id): JsonResponse
    {
        ProgramRelation::destroy($id);

        return response()->json(['success' => true]);
    }

    public function saveSequence(Request $request): JsonResponse
    {
        $programRelationSequenceSetter = new ProgramRelationSequenceSetter($request->all());
        $programRelationSequenceSetter->set();

        return response()->json(['success' => true]);
    }
}
