<?php

namespace App\Http\Controllers\Admin;

use App\AgeRange;
use App\Cruise;
use App\CruiseDevice;
use App\Device;
use App\Entities\PriceEntity;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\PriceElementSetter;
use App\Manipulators\PriceSetter;
use App\Price;
use App\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;

/**
 * @resource Admin/PriceController
 */
class PriceController extends ResourceController
{

    /**
     * store
     * Store a newly created Price
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $price = $this->savePrice($request->toArray());
        return response()->json(['success' => true, 'data' => (new PriceEntity($price))->getFrontendData(['admin'])]);
    }

    /**
     * show
     * Display the specified Price
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
     * Update the specified Price
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $price = $this->savePrice($request->toArray(), $id);
        return response()->json(['success' => true, 'data' => (new PriceEntity($price))->getFrontendData(['admin'])]);
    }

    /**
     * destroy
     * Remove the specified Price
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $price = Price::findOrFail($id);
        return response()->json([
            'success' => $price->delete(),
            'data' => (new PriceEntity($price))->getFrontendData(['admin'])
        ]);
    }

    /**
     * savePrice
     * @param array $priceData
     * @param int $id
     * @return Price
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    private function savePrice(array $priceData, int $id = null): Price
    {
        $nameTaxonomy = (new TaxonomySetter(
            array_merge(['en' => $priceData['name']['name']], $priceData['name']['translations']),
            isset($priceData['name']['id']) ? $priceData['name']['id'] : null,
            Config::get('taxonomies.names.price_name')
        ))->set();
        $priceData['name_taxonomy_id'] = $nameTaxonomy->id;

        $productable = Product::findOrFail($priceData['product_id'])->productable;
        if (is_a($productable, Device::class)) {
            $ageRangeableType = $productable->deviceable_type;
            $ageRangeableId = $productable->deviceable_id;
        } elseif (is_a($productable, CruiseDevice::class)) {
            $ageRangeableType = Cruise::class;
            $ageRangeableId = $productable->cruise_id;
        } else {
            $ageRangeableType = get_class($productable);
            $ageRangeableId = $productable->id;
        }
        $ageRange = AgeRange::findByNameOrFail($priceData['age_range'], $ageRangeableType, $ageRangeableId);
        $priceData['age_range_id'] = $ageRange->id;
        $priceData['id'] = $id;

        $price = (new PriceSetter($priceData))->set();

        if (!empty($priceData['elements'])) {
            foreach ($priceData['elements'] as $elementData) {
                $attributes = [
                    'id' => isset($elementData['id']) ? $elementData['id'] : null,
                    'price_id' => $price->id,
                    'date_range_id' => $elementData['date_range_id'],
                    'meal_plan' => $elementData['meal_plan'],
                    'net_price' => $elementData['net_price'],
                    'rack_price' => $elementData['rack_price'],
                    'margin_type' => $elementData['margin_type'],
                    'margin_value' => $elementData['margin_value']
                ];

                $priceElement = (new PriceElementSetter($attributes))->set();

                if (!$elementData['enabled']) {
                    $priceElement->delete();
                }
            }
        }

        return $price;
    }

}
