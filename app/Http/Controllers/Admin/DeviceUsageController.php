<?php

namespace App\Http\Controllers\Admin;

use App\DeviceUsage;
use App\Entities\DeviceUsageEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\DeviceUsageSetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/DeviceUsageController
 */
class DeviceUsageController extends ResourceController
{

    /**
     * index
     * Display a listing of DeviceUsages
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DeviceUsageEntity::getCollection(DeviceUsage::all(), ['admin'])
        ]);
    }

    /**
     * show
     * Display the specified DeviceUsage
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $deviceUsage = DeviceUsage::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => (new DeviceUsageEntity($deviceUsage))->getFrontendData(['admin'])
        ]);
    }

    /**
     * store
     * Store a newly created DeviceUsage
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $deviceUsage = (new DeviceUsageSetter($request->toArray()))->set();
        return response()->json([
            'success' => true,
            'data' => (new DeviceUsageEntity($deviceUsage))->getFrontendData(['admin'])
        ]);
    }

    /**
     * update
     * Update the specified DeviceUsage
     * @param Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $deviceUsage = (new DeviceUsageSetter($requestArray))->set();
        return response()->json([
            'success' => true,
            'data' => (new DeviceUsageEntity($deviceUsage))->getFrontendData(['admin'])
        ]);
    }

    /**
     * destroy
     * Remove the specified DeviceUsage
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $deviceUsage = DeviceUsage::findOrFail($id);
        return response()->json([
            'success' => (bool)$deviceUsage->delete(),
            'data' => (new DeviceUsageEntity(DeviceUsage::withTrashed()->findOrFail($id)))->getFrontendData(['admin'])
        ]);
    }

}
