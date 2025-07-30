<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ShipGroupEntity;
use App\Entities\SupplierEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationGroupSetter;
use App\ShipGroup;
use App\Supplier;
use App\Traits\DefaultClassificationGetterTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipGroupController extends ResourceController
{
    use DefaultClassificationGetterTrait;

    protected $suppliers = [];
    private $entityAdditions = [
        'descriptions',
        'admin_properties',
        'parent',
        'availability_mode',
        'galleries',
        'prices',
        'supplier'
    ];

    public function __construct()
    {
        $this->defaultClassifications = [
            'general' => ['ship_group_category', 'propulsion'],
            'settings' => []
        ];
        $this->categoryTxPath = 'taxonomies.organization_group_properties.categories';
        $this->suppliers = SupplierEntity::getCollection(Supplier::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $shipGroups = [];
        if ($request->has('parent_id')) {
            $shipGroups = ShipGroup::where('parent_id', $request->get('parent_id'))->get();
        } else {
            $shipGroups = ShipGroup::all();
        }

        return response()->json([
            'success' => true,
            'data' => ShipGroupEntity::getCollection($shipGroups, $this->entityAdditions),
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
        $requestArray = $request->toArray();
        $requestArray['type'] = 'ship_group';
        $shipGroup = (new OrganizationGroupSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new ShipGroupEntity($shipGroup))->getFrontendData($this->entityAdditions),
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
        return response()->json([
            'success' => true,
            'data' => (new ShipGroupEntity(ShipGroup::findOrFail($id)))->getFrontendData($this->entityAdditions),
            'suppliers' => $this->suppliers
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        return response()->json([
            'success' => ShipGroup::findOrFail($id)->delete(),
            'data' => [],
            'suppliers' => $this->suppliers
        ]);
    }
}
