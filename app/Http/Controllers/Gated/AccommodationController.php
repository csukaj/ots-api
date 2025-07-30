<?php

namespace App\Http\Controllers\Gated;

use App\Accommodation;
use App\Entities\AccommodationEntity;
use App\Entities\HotelChainEntity;
use App\Entities\SupplierEntity;
use App\HotelChain;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationSetter;
use App\Supplier;
use App\Traits\DefaultClassificationGetterTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Gated/OrganizationController
 */
class AccommodationController extends ResourceController
{

    use DefaultClassificationGetterTrait;

    protected $hotelChains = [];
    protected $suppliers = [];
    private $entityAdditions = ['descriptions', 'location', 'availability_mode', 'admin_properties', 'galleries', 'supplier'];

    public function __construct()
    {
        $this->hotelChains = HotelChainEntity::getCollection(HotelChain::all());
        $this->suppliers = SupplierEntity::getCollection(Supplier::all());
        $this->defaultClassifications = [
            'general' => ['accommodation_category'],
            'settings' => [
                'availability_mode',
                'stars',
                'price_level',
                'discount_calculations_base',
                'merged_free_nights'
            ]
        ];
        $this->categoryTxPath = 'taxonomies.organization_properties.categories';
    }

    /**
     * index
     * Display a listing of Organizations
     * @return JsonResponse
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function index(): JsonResponse
    {
        if ($this->getAuthUser()->hasRole('admin')) {
            $organizations = Accommodation::all();
        } else {
            $organizations = $this->getAuthUser()->accommodations;
        }

        return response()->json([
            'success' => true,
            'data' => AccommodationEntity::getCollection($organizations, $this->entityAdditions),
            'hotelChains' => $this->hotelChains,
            'suppliers' => $this->suppliers,
            'defaults' => $this->getTaxonomiesForDefaults()
        ]);
    }

    /**
     * store
     * Store a newly created Accommodation
     * @param  Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \App\Exceptions\UserException
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        if (!$this->gateAllows('create-organization')) {
            throw new AuthorizationException('Permission denied.');
        }

        $organization = (new OrganizationSetter($request->all()))->set();
        $organizationEn = new AccommodationEntity($organization);

        return response()->json([
            'success' => true,
            'data' => $organizationEn->getFrontendData($this->entityAdditions),
            'hotelChains' => $this->hotelChains,
            'suppliers' => $this->suppliers
        ]);
    }

    /**
     * show
     * Display the specified Accommodation
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function show($id): JsonResponse
    {
        if (!$this->gateAllows('access-organization', (int)$id)) {
            throw new AuthorizationException('Permission denied.');
        }

        $accommodationEntity = new AccommodationEntity(Accommodation::findOrFail($id));
        return response()->json([
            'success' => true,
            'data' => $accommodationEntity->getFrontendData($this->entityAdditions),
            'hotelChains' => $this->hotelChains,
            'suppliers' => $this->suppliers
        ]);
    }

    /**
     * destroy
     * Remove the specified Accommodation
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        if (!$this->gateAllows('access-organization', $id)) {
            throw new AuthorizationException('Permission denied.');
        }

        $accommodation = Accommodation::findOrFail($id);
        $accommodation->flushEventListeners();
        return response()->json([
            'success' => $accommodation->delete(),
            'data' => [],
            'hotelChains' => $this->hotelChains,
            'suppliers' => $this->suppliers
        ]);
    }

    /**
     * show
     * Display the specified Accommodation
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function overview($id): JsonResponse
    {
        if (!$this->gateAllows('access-organization', (int)$id)) {
            throw new AuthorizationException('Permission denied.');
        }

        $additions = ['properties', 'supplier', 'devices', 'date_ranges', 'device_amount', 'prices', 'device_margin', 'pricing'];

        $accommodationEntity = new AccommodationEntity(Accommodation::findOrFail($id));
        return response()->json([
            'success' => true,
            'data' => $accommodationEntity->getFrontendData($additions),
            'hotelChains' => $this->hotelChains,
            'suppliers' => $this->suppliers
        ]);
    }
}
