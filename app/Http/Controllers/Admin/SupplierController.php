<?php

namespace App\Http\Controllers\Admin;

use App\Entities\SupplierEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\OrganizationSetter;
use App\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends ResourceController
{
    private $entityAdditons = ['contacts', 'people'];

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SupplierEntity::getCollection(Supplier::all(), $this->entityAdditons)
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
        $supplier = (new OrganizationSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new SupplierEntity($supplier))->getFrontendData($this->entityAdditons)
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
            'data' => (new SupplierEntity(Supplier::findOrFail($id)))->getFrontendData($this->entityAdditons)
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
        $supplier = (new OrganizationSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new SupplierEntity($supplier))->getFrontendData($this->entityAdditons)
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
            'success' => Supplier::findOrFail($id)->delete(),
            'data' => []
        ]);
    }
}
