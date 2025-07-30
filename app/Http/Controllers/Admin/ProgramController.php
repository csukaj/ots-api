<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ProgramEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\ProgramSetter;
use App\Program;
use App\ProgramRelation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends ResourceController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request=null): JsonResponse
    {
        $query = Program::query();

        if ($request->has('organization_id')) {
            $query = $query->where('organization_id', $request->get('organization_id'));
        }

        if ($request->has('type')) {
            $query = $query->where('type_taxonomy_id',
                Config::getOrFail('taxonomies.program_types.' . $request->get('type')));
        }

        return response()->json([
            'success' => true,
            'data' => ProgramEntity::getCollection($query->get())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $program = (new ProgramSetter($request->toArray()))->set();

        return response()->json([
            'success' => true,
            'data' => (new ProgramEntity($program))->getFrontendData()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $program = Program::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => (new ProgramEntity($program))->getFrontendData(['ship_company', 'galleries'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $program = (new ProgramSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new ProgramEntity($program))->getFrontendData()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return JsonResponse
     * @throws UserException
     */
    public function destroy($id): JsonResponse
    {
        if (ProgramRelation::where('parent_id', $id)->orWhere('child_id', $id)->exists()) {
            throw new UserException('An activity with active relation can not be deleted!');
        }
        Program::destroy($id);

        return response()->json(['success' => true]);
    }
}
