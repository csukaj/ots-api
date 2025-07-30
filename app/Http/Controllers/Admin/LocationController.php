<?php

namespace App\Http\Controllers\Admin;

use App\Entities\LocationEntity;
use App\Http\Controllers\ResourceController;
use App\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/LocationController
 */
class LocationController extends ResourceController
{

    /**
     * show
     * Display the specified Location
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $location = Location::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => (new LocationEntity($location))->getFrontendData(['admin'])
        ]);
    }

    /**
     * create
     * Create the specified Location
     * @param  Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $location = new Location();
        $location->fill($request->toArray());
        $location->saveOrFail();
        return response()->json([
            'success' => true,
            'data' => (new LocationEntity($location))->getFrontendData(['admin'])
        ]);
    }


    /**
     * update
     * Update the specified Location
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $location->fill($request->toArray());
        $location->saveOrFail();
        return response()->json([
            'success' => true,
            'data' => (new LocationEntity($location))->getFrontendData(['admin'])
        ]);
    }

}
