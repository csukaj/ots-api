<?php

namespace App\Http\Controllers\Admin;

use App\Entities\OrganizationGroupPoiEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationGroupPoiSetter;
use App\OrganizationGroupPoi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationGroupPoiController extends ResourceController
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        if ($request->has('organization_group_id')) {
            $orgGrpPois = OrganizationGroupPoi::where('organization_group_id',
                $request->get('organization_group_id'))->get();
        } else {
            $orgGrpPois = OrganizationGroupPoi::all();
        }

        return response()->json([
            'success' => true,
            'data' => OrganizationGroupPoiEntity::getCollection($orgGrpPois)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $orgGrpPoi = (new OrganizationGroupPoiSetter($request->toArray()))->set();

        return response()->json([
            'success' => true,
            'data' => (new OrganizationGroupPoiEntity($orgGrpPoi))->getFrontendData()
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
        $orgGrpPoi = OrganizationGroupPoi::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => (new OrganizationGroupPoiEntity($orgGrpPoi))->getFrontendData()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $orgGrpPoi = (new OrganizationGroupPoiSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new OrganizationGroupPoiEntity($orgGrpPoi))->getFrontendData()
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
        OrganizationGroupPoi::destroy($id);

        return response()->json(['success' => true]);
    }
}
