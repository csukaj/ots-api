<?php

namespace App\Http\Controllers\Extranet;

use App\Entities\Search\AccommodationSearchEntity;
use App\Http\Controllers\Search\AccommodationSearchController as SearchController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource AccommodationSearchController
 */
class AccommodationSearchController extends SearchController
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
        $accommodationSearchEntity->setShowInactive($request->get('show_inactive', false));
        return response()->json([
            'success' => true,
            'data' => $accommodationSearchEntity->getFrontendData(['frontend']),
            'request' => $request->toArray()
        ]);
    }

}
