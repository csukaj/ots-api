<?php

namespace App\Http\Controllers\Extranet;

use App\Device;
use App\Entities\AvailabilityEntity;
use App\Http\Controllers\ResourceController;
use App\Organization;
use App\ShipGroup;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Gated/AvailabilityController
 */
class AvailabilityController extends ResourceController
{
    public function __construct()
    {
        $this->middleware('logrequest')->except(['index', 'show']);
    }

    /**
     * index
     * Display availabilities in a daily breakdown
     * @param  Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function index(Request $request = null): JsonResponse
    {
        $model = $request->availableType::findOrFail($request->availableId);

        $hasAccessOrganizationAvailability = $request->availableType == Device::class
            && $model->deviceable_type == Organization::class
            && $this->gateAllows('access-availability', $model->deviceable_id);
        $hasAccessOrganizationGroup = $request->availableType == Device::class
            && $model->deviceable_type == ShipGroup::class
            && $this->gateAllows('access-organizationgroup', $model->deviceable_id);

        if (!$hasAccessOrganizationAvailability && !$hasAccessOrganizationGroup) {
            throw new AuthorizationException('Permission denied.');
        }

        return response()->json([
            'success' => true,
            'data' => (new AvailabilityEntity(get_class($model), $model->id))->get($request->fromDate, $request->toDate)
        ]);
    }

    /**
     * store
     * Store availabilities using a request of daily breakdown
     * @param  Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $model = $request->availableType::findOrFail($request->availableId);
        $isDevice = $request->availableType == Device::class;
        if (!(
            ($isDevice
                && $model->deviceable_type == Organization::class
                && $this->gateAllows('create-availability', $model->deviceable_id))
            || ($isDevice
                && $model->deviceable_type == ShipGroup::class
                && $this->gateAllows('create-organizationgroup', $model->deviceable_id))
        )) {
            throw new AuthorizationException('Permission denied.');
        }

        return response()->json([
            'success' => (new AvailabilityEntity(get_class($model), $model->id))->set($request->availabilities)
        ]);
    }

}
