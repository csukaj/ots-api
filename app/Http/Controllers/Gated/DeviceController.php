<?php

namespace App\Http\Controllers\Gated;

use App\CruiseDevice;
use App\Device;
use App\Entities\DeviceEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\DeviceSetter;
use App\OrganizationGroup;
use App\ShipGroup;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Gated/DeviceController
 */
class DeviceController extends ResourceController
{

    protected $additions = ['amount', 'descriptions', 'usages', 'margin', 'deviceable', 'galleries'];
    protected $noAccessAdditions = ['amount'];

    /**
     * index
     * Display a listing of devices
     * @param Request|null $request
     * @return JsonResponse
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
        return response()->json(['success' => true, 'data' => DeviceEntity::getCollection($devices, $additions)]);
    }

    /**
     * store
     * Store a newly created Device
     * @param  Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        if (
            !$this->gateAllows('access-devices',
                [$request->input('deviceable_type'), $request->input('deviceable_id')]) ||
            !$this->gateAllows('create-device')
        ) {
            throw new AuthorizationException('Permission denied.');
        }

        $device = (new DeviceSetter($request->all()))->set();
        if ($request->input('deviceable_type') == OrganizationGroup::class) {
            //create default cruisedevices for cruise
            foreach (ShipGroup::findOrFail($request->input('deviceable_id'))->cruises as $cruise) {
                CruiseDevice::createOrRestore([
                    'cruise_id' => $cruise->id,
                    'device_id' => $device->id
                ]);
            }
        }

        $additions = $this->gateAllows('access-device-attributes',
            $request->input('organization_id')) ? $this->additions : $this->noAccessAdditions;
        return response()->json([
            'success' => true,
            'data' => (new DeviceEntity($device))->getFrontendData($additions)
        ]);
    }

    /**
     * show
     * Display the specified Device
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show($id): JsonResponse
    {
        $device = Device::findOrFail($id);

        if (!$this->gateAllows('access-devices', [$device->deviceable_type, $device->deviceable_id])) {
            throw new AuthorizationException('Permission denied.');
        }

        $additions = $this->gateAllows('access-device-attributes', [$device->deviceable_type, $device->deviceable_id]) ?
            $this->additions : $this->noAccessAdditions;

        return response()->json([
            'success' => true,
            'data' => (new DeviceEntity($device))->getFrontendData($additions)
        ]);
    }

    /**
     *
     */
    public function getDeviceNames()
    {
        $devices = Device::orderBy('id')->with(['name', 'type', 'deviceable'])->get();

        return response()->json([
            'success' => true,
            'data' => DeviceEntity::getCollection($devices, ['deviceable'])
        ]);
    }

    /**
     * update
     * Update the specified Device
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    public function update(Request $request, $id): JsonResponse
    {
        $device = Device::findOrFail($id);

        if (!$this->gateAllows('access-devices', [$device->deviceable_type, $device->deviceable_id])) {
            throw new AuthorizationException('Permission denied.');
        }

        if ($this->gateAllows('access-device-attributes', [$device->deviceable_type, $device->deviceable_id])) {
            $attributes = $request->all();
            $attributes['id'] = $id;
            $device = (new DeviceSetter($attributes))->set();
        } else {
            $device->amount = $request->amount;
            $device->saveOrFail();
        }
        //availability setting is done @ Device::updated()


        $additions = $this->gateAllows('access-device-attributes',
            $device->organization_id) ? $this->additions : $this->noAccessAdditions;
        return response()->json([
            'success' => true,
            'data' => (new DeviceEntity($device))->getFrontendData($additions)
        ]);
    }

    /**
     * destroy
     * Remove the specified Device
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy($id): JsonResponse
    {
        $device = Device::findOrFail($id);

        if (!$this->gateAllows('access-devices', [$device->deviceable_type, $device->deviceable_id]) ||
            !$this->gateAllows('delete-device')
        ) {
            throw new AuthorizationException('Permission denied.');
        }

        $additions = $this->gateAllows('access-device-attributes',
            [$device->deviceable_type, $device->deviceable_id]) ? $this->additions : $this->noAccessAdditions;

        CruiseDevice::where('device_id', $id)->delete();

        return response()->json([
            'success' => (bool)$device->delete(),
            'data' => (new DeviceEntity(Device::withTrashed()->findOrFail($id)))->getFrontendData($additions)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \App\Exceptions\UserException
     * @throws \Artisaninweb\SoapWrapper\Exceptions\ServiceAlreadyExists
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function getChannelManagerIds(Request $request): JsonResponse
    {
        if (!$this->gateAllows('access-devices', [$request->input('deviceable_type'), $request->input('deviceable_id')])) {
            throw new AuthorizationException('Permission denied.');
        }

        $data = [];

        $accommodation = $request->input('deviceable_type')::findOrFail($request->input('deviceable_id'));
        $idList = app('channel_manager')->list($accommodation);
        if(!$idList){
            return response()->json(['success' => true, 'data' => []]);
        }
        if (count($idList)) {
            $existing = array_flip(Device::getDevicesChannelManagerId($accommodation->id));
        }

        foreach ($idList as $id => $name) {
            $device = isset($existing[$id]) ? (new DeviceEntity(Device::findOrFail($existing[$id])))->getFrontendData() : null;
            $data[] = ['channel_manager_name' => ['en' => $name], 'channel_manager_id' => $id, 'device' => $device];
        }
        return response()->json(['success' => true, 'data' => $data]);
    }
}
