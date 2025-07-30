<?php

namespace Modules\Stylerscontact\Http\Controllers;


use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerscontact\Entities\Person;
use Modules\Stylerscontact\Entities\PersonEntity;
use Modules\Stylerscontact\Manipulators\PersonSetter;

class PersonController extends ResourceController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $persons = Person::forPersonable($request->get('personable_type'), $request->get('personable_id'))->get();
        return response()->json([
            'success' => true,
            'data' => PersonEntity::getCollection($persons, ['personable','contacts'])
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $person = (new PersonSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new PersonEntity($person))->getFrontendData(['personable','contacts'])
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
        return response()->json([
            'success' => true,
            'data' => (new PersonEntity(Person::findOrFail($id)))->getFrontendData(['personable','contacts'])
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $person = (new PersonSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new PersonEntity($person))->getFrontendData(['personable','contacts'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => Person::findOrFail($id)->delete(),
            'data' => []
        ]);
    }
}

