<?php

namespace App\Http\Controllers\Admin;

use App\DeviceMinimumNights;
use App\Entities\DateRangeEntity;
use App\Entities\DeviceEntity;
use App\Entities\DeviceMinimumNightsEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\DeviceMinimumNightsSetter;
use App\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/DeviceMinimumNightsController
 */
class DeviceMinimumNightsController extends ResourceController
{

    /**
     * index
     * Display a listing of DeviceMinimumNights by Organization
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $organization = Organization::findOrFail($request->input('organization_id'));
        $devices = $organization->devices()->orderBy('id')->get();
        $minNights = DeviceMinimumNights::whereIn('device_id', $devices->pluck('id'))->get();
        $dateRanges = $organization->dateRanges()->open()->orderBy('from_time')->get();

        return response()->json([
            'success' => true,
            'data' => DeviceMinimumNightsEntity::getCollection($minNights),
            'devices' => DeviceEntity::getCollection($devices),
            'date_ranges' => DateRangeEntity::getCollection($dateRanges)
        ]);
    }

    /**
     * store
     * Store a newly created DeviceMinimumNights
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $organization = Organization::findOrFail($request->input('organization_id'));
        $devices = $organization->devices()->orderBy('id')->get();
        $existingMinNights = DeviceMinimumNights::whereIn('device_id', $devices->pluck('id'))->get();
        $dateRanges = $organization->dateRanges()->open()->orderBy('from_time')->get();

        foreach ($existingMinNights as $existingMN) {
            $found = false;
            foreach ($request->input('minimum_nights') as $inputMinNights) {
                if ($existingMN->device_id == $inputMinNights['device_id'] && $existingMN->date_range_id == $inputMinNights['date_range_id']) {
                    $found = true;
                }
            }

            if (!$found) {
                $existingMN->delete();
            }
        }

        foreach ($request->input('minimum_nights') as $inputMinNights) {
            (new DeviceMinimumNightsSetter($inputMinNights))->set();
        }

        $minNights = DeviceMinimumNights::whereIn('device_id', $devices->pluck('id'))->get();

        return response()->json([
            'success' => true,
            'data' => DeviceMinimumNightsEntity::getCollection($minNights),
            'devices' => DeviceEntity::getCollection($devices),
            'date_ranges' => DateRangeEntity::getCollection($dateRanges)
        ]);
    }

}
