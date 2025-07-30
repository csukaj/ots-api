<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ShipEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationSetter;
use App\Ship;
use App\Traits\DefaultClassificationGetterTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipController extends ResourceController
{

    use DefaultClassificationGetterTrait;

    private $entityAdditions = ['descriptions', 'galleries', 'admin_properties', 'parent'];

    public function __construct()
    {
        $this->defaultClassifications = [
            'settings' => ['discount_calculations_base', 'merged_free_nights']
        ];
        $this->categoryTxPath = 'taxonomies.organization_properties.categories';
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $ships = Ship::inShipGroup($request->input('parent_id'))->get();
        return response()->json([
            'success' => true,
            'data' => ShipEntity::getCollection($ships, $this->entityAdditions),
            'defaults' => $this->getTaxonomiesForDefaults()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $requestArray = $request->all();
        $requestArray['type'] = 'ship';

        $organization = (new OrganizationSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new ShipEntity($organization))->getFrontendData($this->entityAdditions)
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
            'data' => (new ShipEntity(Ship::findOrFail($id)))->getFrontendData($this->entityAdditions)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->all();
        $requestArray['id'] = $id;
        $requestArray['type'] = 'ship';

        $organization = (new OrganizationSetter($requestArray))->set();
        $organizationEn = new ShipEntity($organization);

        return response()->json([
            'success' => true,
            'data' => $organizationEn->getFrontendData($this->entityAdditions)
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
        $ship = Ship::findOrFail($id);
        return response()->json([
            'success' => $ship->delete(),
            'data' => [],
        ]);
    }
}
