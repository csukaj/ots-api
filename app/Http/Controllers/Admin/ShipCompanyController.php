<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ShipCompanyEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationSetter;
use App\ShipCompany;
use App\Traits\DefaultClassificationGetterTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipCompanyController extends ResourceController
{

    use DefaultClassificationGetterTrait;

    private $entityAdditions = ['descriptions', 'location'];

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
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ShipCompanyEntity::getCollection(ShipCompany::orderBy('id')->get(), $this->entityAdditions),
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
        $requestArray['type'] = 'ship_company';

        $organization = (new OrganizationSetter($requestArray))->set();
        $organizationEn = new ShipCompanyEntity($organization);

        return response()->json([
            'success' => true,
            'data' => $organizationEn->getFrontendData($this->entityAdditions)
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
            'data' => (new ShipCompanyEntity(ShipCompany::findOrFail($id)))->getFrontendData($this->entityAdditions)
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
        $requestArray['type'] = 'ship_company';

        $organization = (new OrganizationSetter($requestArray))->set();
        $organizationEn = new ShipCompanyEntity($organization);

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
        return response()->json(['success' => (bool)ShipCompany::findOrFail($id)->delete(), 'data' => []]);
    }
}
