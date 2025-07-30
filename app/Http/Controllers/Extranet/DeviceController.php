<?php

namespace App\Http\Controllers\Extranet;

use App\Device;
use App\Entities\DeviceEntity;
use App\Http\Controllers\ResourceController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Gated/DeviceController
 */
class DeviceController extends ResourceController
{

    protected $additions = ['amount', 'deviceable', 'availability', 'availability_update'];
    protected $noAccessAdditions = ['amount'];

    /**
     * index
     * Display a listing of devices
     * @param Request|null $request
     * @return array
     * @throws AuthorizationException
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function index(Request $request = null): JsonResponse
    {
        if (!$this->gateAllows(
            'access-devices',
            [$request->input('deviceable_type'), $request->input('deviceable_id')]
        )) {
            throw new AuthorizationException('Permission denied.');
        }

        $devices = Device
            ::forDeviceable($request->input('deviceable_type'), $request->input('deviceable_id'))
            ->orderBy('id')
            ->get();

        $additions = $this->gateAllows(
            'access-device-attributes',
            [$request->input('deviceable_type'), $request->input('deviceable_id')]
        ) ? $this->additions : $this->noAccessAdditions;

        return response()->json([
            'success' => true,
            'data' => DeviceEntity::getCollectionWithParams($devices, $additions, $request->input('from_date'),
                $request->input('to_date'))
        ]);
    }
}
