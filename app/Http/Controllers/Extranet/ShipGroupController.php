<?php

namespace App\Http\Controllers\Extranet;

use App\Entities\ShipGroupEntity;
use App\Http\Controllers\ResourceController;
use App\ShipGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipGroupController extends ResourceController
{

    private $entityAdditions = ['parent', 'availability_mode', 'galleries'];

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     * @return JsonResponse
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function index(Request $request = null): JsonResponse
    {
        if ($request->has('parent_id')) {
            $shipGroups = ShipGroup::where('parent_id', $request->get('parent_id'))->get();
        } else {
            $shipGroups = ShipGroup::all();
        }

        if (!$this->gateAllows('access-organizationgroup', null)) {
            $shipGroups = [];
        }

        return response()->json([
            'success' => true,
            'data' => ShipGroupEntity::getCollection($shipGroups, $this->entityAdditions)
        ]);
    }
}
