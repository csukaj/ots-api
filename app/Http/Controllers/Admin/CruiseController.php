<?php

namespace App\Http\Controllers\Admin;

use App\Cruise;
use App\Entities\CruiseEntity;
use App\Entities\SupplierEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\CruiseSetter;
use App\Supplier;
use App\Traits\DefaultClassificationGetterTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CruiseController extends ResourceController
{
    use DefaultClassificationGetterTrait;
    protected $suppliers = [];

    public function __construct()
    {
        $this->defaultClassifications = [
            'settings' => ['discount_calculations_base', 'merged_free_nights']
        ];
        $this->categoryTxPath = 'taxonomies.cruise_properties.categories';
        $this->suppliers = SupplierEntity::getCollection(Supplier::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $cruises = [];
        if ($request->has('ship_company_id')) {
            $cruises = Cruise::where('ship_company_id', $request->get('ship_company_id'))->get();
        } else {
            $cruises = Cruise::all();
        }

        return response()->json([
            'success' => true,
            'data' => CruiseEntity::getCollection($cruises, ['prices', 'supplier']),
            'defaults' => $this->getTaxonomiesForDefaults(),
            'suppliers' => $this->suppliers
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
        $cruise = (new CruiseSetter($request->toArray()))->set();

        return response()->json([
            'success' => true,
            'data' => (new CruiseEntity($cruise))->getFrontendData(['prices', 'supplier']),
            'suppliers' => $this->suppliers
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
        $cruise = Cruise::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => (new CruiseEntity($cruise))->getFrontendData([
                'ship_company',
                'ship_group',
                'itinerary',
                'galleries',
                'prices',
                'supplier',
                'admin_ship_group_devices'
            ]),
            'suppliers' => $this->suppliers
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
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $cruise = (new CruiseSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new CruiseEntity($cruise))->getFrontendData([
                'ship_company',
                'ship_group',
                'itinerary',
                'galleries',
                'prices',
                'supplier'
            ]),
            'suppliers' => $this->suppliers
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
            'success' => (bool)Cruise::destroy($id),
            'suppliers' => $this->suppliers
        ]);
    }
}
