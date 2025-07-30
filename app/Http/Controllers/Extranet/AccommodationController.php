<?php

namespace App\Http\Controllers\Extranet;

use App\Accommodation;
use App\Entities\AccommodationEntity;
use App\Http\Controllers\ResourceController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * @resource Gated/OrganizationController
 */
class AccommodationController extends ResourceController
{

    private $entityAdditions = ['availability_mode', 'galleries', 'availability_update'];

    /**
     * index
     * Display a listing of Organizations
     * @return JsonResponse
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function index(): JsonResponse
    {
        $user = $this->getAuthUser();
        if ($user->hasRole('admin') || $user->hasRole('advisor')) {
            $organizations = Accommodation::all();
        } else {
            $organizations = $user->accommodations;
        }

        return response()->json([
            'success' => true,
            'data' => AccommodationEntity::getCollection($organizations, $this->entityAdditions),
        ]);
    }

    /**
     * show
     * Display the specified Accommodation
     * @param  int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show($id): JsonResponse
    {
        if (!$this->gateAllows('access-organization', (int)$id)) {
            throw new AuthorizationException('Permission denied.');
        }
        $accommodation = Accommodation::findOrFail($id);

        $channelManagerService = app('channel_manager')->fetch($accommodation);
        if($channelManagerService && $channelManagerService->isValid) {
            $channelManagerService->update();
        }

        return response()->json([
            'success' => true,
            'data' => (new AccommodationEntity($accommodation))->getFrontendData($this->entityAdditions),
        ]);
    }
}
