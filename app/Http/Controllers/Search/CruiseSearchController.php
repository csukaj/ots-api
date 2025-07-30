<?php

namespace App\Http\Controllers\Search;

use App\Caches\CruiseSearchOptionsCache;
use App\Entities\Search\CruiseSearchEntity;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource CruiseSearchController
 */
class CruiseSearchController extends Controller
{

    /**
     * index
     * Run a cruise search by request
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function index(Request $request): JsonResponse
    {
        $cruiseSearchEn = new CruiseSearchEntity();
        $cruiseSearchEn->setParameters($request->all());
        return response()->json([
            'success' => true,
            'data' => $cruiseSearchEn->getFrontendData(['frontend']),
            'request' => $request->toArray()
        ]);
    }

    /**
     * searchOptions
     * List all filter options for cruise search
     * @return JsonResponse
     */
    public function searchOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new CruiseSearchOptionsCache())->getValues()
        ]);
    }

}
