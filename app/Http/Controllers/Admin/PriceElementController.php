<?php

namespace App\Http\Controllers\Admin;

use App\Entities\PriceElementEntity;
use App\Entities\PriceEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\PriceElementSetter;
use App\Price;
use App\PriceElement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/PriceElementController
 */
class PriceElementController extends ResourceController
{

    /**
     * store
     * Store a newly created PriceElement
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $priceElement = $this->savePriceElement($request->toArray());
        return response()->json([
            'success' => true,
            'data' => (new PriceElementEntity($priceElement))->getFrontendData(['admin'])
        ]);
    }

    /**
     * show
     * Display the specified PriceElement
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new PriceEntity(Price::findOrFail($id)))->getFrontendData(['admin'])
        ]);
    }

    /**
     * update
     * Update the specified PriceElement
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $priceElement = $this->savePriceElement($request->toArray(), $id);
        return response()->json([
            'success' => true,
            'data' => (new PriceElementEntity($priceElement))->getFrontendData(['admin'])
        ]);
    }

    /**
     * destroy
     * Remove the specified PriceElement
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $priceElement = PriceElement::findOrFail($id);
        return response()->json([
            'success' => $priceElement->delete(),
            'data' => (new PriceElementEntity($priceElement))->getFrontendData(['admin'])
        ]);
    }

    /**
     * updateCollection
     * Bulk update multiple PriceElements
     * @param  Request $request
     * @return JsonResponse
     */
    public function updateCollection(Request $request): JsonResponse
    {
        $priceElementsUpdated = [];
        foreach ($request->data as $requestItem) {
            $priceElementsUpdated[] = $this->savePriceElement($requestItem,
                empty($requestItem['id']) ? null : $requestItem['id']);
        }
        return response()->json([
            'success' => true,
            'data' => PriceElementEntity::getCollection($priceElementsUpdated, ['admin'])
        ]);
    }

    /**
     * savePriceElement
     * @param array $elementData
     * @param int $id
     * @return PriceElement
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    private function savePriceElement(array $elementData, int $id = null): PriceElement
    {
        if ($id) {
            $elementData['id'] = $id;
        }
        $priceElement = (new PriceElementSetter($elementData))->set();
        if (!$elementData['enabled']) {
            $priceElement->delete();
        }

        return $priceElement;
    }

}
