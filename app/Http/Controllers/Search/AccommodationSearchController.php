<?php

namespace App\Http\Controllers\Search;

use App\Caches\AccommodationSearchableTextsCache;
use App\Caches\AccommodationSearchOptionsCache;
use App\Entities\Search\AccommodationSearchEntity;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource AccommodationSearchController
 */
class AccommodationSearchController extends Controller
{

    /**
     * index
     * Run a accommodation search by request
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $accommodationSearchEntity->setParameters($request->all());
        return response()->json([
            'success' => true,
            'data' => $accommodationSearchEntity->getFrontendData(['frontend']),
            'request' => $request->toArray()
        ]);
    }

    /**
     * searchableTexts
     * List all searchable options for accommodation name text search
     * @return JsonResponse
     */
    public function searchableTexts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new AccommodationSearchableTextsCache())->getValues()
        ]);
    }

    /**
     * searchOptions
     * List all filter options for accommodation search
     * @return JsonResponse
     */
    public function searchOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new AccommodationSearchOptionsCache())->getValues()
        ]);
    }

}
