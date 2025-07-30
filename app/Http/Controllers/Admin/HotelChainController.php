<?php

namespace App\Http\Controllers\Admin;

use App\Entities\HotelChainEntity;
use App\Exceptions\UserException;
use App\HotelChain;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationSetter;
use App\Traits\DefaultClassificationGetterTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/HotelChainController
 */
class HotelChainController extends ResourceController
{

    use DefaultClassificationGetterTrait;

    public function __construct()
    {
        $this->defaultClassifications = [
            'settings' => ['discount_calculations_base', 'merged_free_nights']
        ];
        $this->categoryTxPath = 'taxonomies.organization_properties.categories';
    }

    /**
     * index
     * Display a listing of HotelChain
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => HotelChainEntity::getCollection(HotelChain::all()),
            'defaults' => $this->getTaxonomiesForDefaults()
        ]);
    }

    /**
     * store
     * Store a newly created HotelChain
     * @param  Request $request
     * @return JsonResponse
     * @throws UserException
     */
    public function store(Request $request): JsonResponse
    {
        $hotelChain = (new OrganizationSetter($request->all()))->set();
        return response()->json(['success' => true, 'data' => (new HotelChainEntity($hotelChain))->getFrontendData()]);
    }

    /**
     * show
     * Display the specified HotelChain
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new HotelChainEntity(HotelChain::findOrFail($id)))->getFrontendData()
        ]);
    }

    /**
     * update
     * Update the specified HotelChain
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $hotelChain = (new OrganizationSetter($request->all()))->set();
        return response()->json(['success' => true, 'data' => (new HotelChainEntity($hotelChain))->getFrontendData()]);
    }

    /**
     * destroy
     * Remove the specified HotelChain
     * @param  int $id
     * @return JsonResponse
     * @throws UserException
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $hotelChain = HotelChain::findOrFail($id);
        if (count($hotelChain->children)) {
            throw new UserException('You can not delete a parent organization with child organizations.');
        }
        return response()->json([
            'success' => $hotelChain->delete(),
            'data' => []
        ]);
    }
}
