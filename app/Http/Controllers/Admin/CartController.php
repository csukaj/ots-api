<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ResourceController;
use App\Services\UniqueProduct\Service as UniqueProductService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends ResourceController
{
    /**
     * index
     * Display a listing of Cart
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $uniqueProductCart = app('unique_product')->list();
        return $this->respond($uniqueProductCart);
    }

    /**
     * store
     * Store a newly created Cart
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $uniqueProductCart = app('unique_product')
            ->save(
                $request->get('data'),
                $request->get('saveType')
            );
        return $this->respond($uniqueProductCart);
    }

    /**
     * show
     * Display the specified Cart
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $uniqueProductCart = app('unique_product')->show($id);
        return $this->respond($uniqueProductCart);
    }

    /**
     * Remove the specified Cart
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy($id): JsonResponse
    {
        $uniqueProductCart = app('unique_product')->delete($id);
        $response = $this->respond($uniqueProductCart);

        if ($uniqueProductCart->hasError()) {
            throw new AuthorizationException($uniqueProductCart->getMessage());
        }

        return $response;
    }

    private function respond(UniqueProductService $uniqueProductCart): JsonResponse
    {
        if ($uniqueProductCart->hasError()) {
            return response()->json([
                'success' => false,
                'data' => $uniqueProductCart->getErrorMessages()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $uniqueProductCart->getResult()
        ]);
    }
}
