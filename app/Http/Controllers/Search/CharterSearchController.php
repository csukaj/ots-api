<?php

namespace App\Http\Controllers\Search;

use App\Caches\CharterSearchOptionsCache;
use App\Entities\Search\CharterSearchEntity;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource CharterSearchController
 */
class CharterSearchController extends Controller
{

    /**
     * index
     * Run a charter search by request
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): JsonResponse
    {
        $charterSearchEn = new CharterSearchEntity();
        $charterSearchEn->setParameters($request->all());
        return response()->json([
            'success' => true,
            'data' => $charterSearchEn->getFrontendData(['frontend']),
            'request' => $request->toArray()
        ]);
    }

    /**
     * searchOptions
     * List all filter options for charter search
     * @return JsonResponse
     */
    public function searchOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new CharterSearchOptionsCache())->getValues()
        ]);
    }

}
