<?php

namespace App\Http\Controllers\Admin;

use App\AgeRange;
use App\Entities\AgeRangeEntity;
use App\Entities\ProductEntity;
use App\Facades\Config;
use App\Fee;
use App\Http\Controllers\ResourceController;
use App\Manipulators\FeeSetter;
use App\Manipulators\ProductSetter;
use App\Product;
use App\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

class ProgramFeeController extends ResourceController
{

    private $typeOptions = [];

    public function __construct()
    {
        //parent::__construct();
        $this->typeOptions[] = ['name' => 'Group Fee', 'value' => 'group_fee'];
        $this->typeOptions[] = ['name' => 'Personal Fee', 'value' => 'personal_fee'];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $product = $this->setProduct($request->all());
        return response()->json([
            'success' => true,
            'data' => $this->serializeProduct($product->id),
            'options' => $this->serializeOptions($product->id)
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function show($id): JsonResponse
    {
        $program = Program::findOrFail($id);
        if ($program->product) {
            $product = $program->product;
        } else {
            $attributes = [
                'productable_type' => Program::class,
                'productable_id' => $id,
                'type_taxonomy_id' => Config::getOrFail('taxonomies.product_types.personal_fee')
            ];
            $product = $this->setProduct($attributes);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeProduct($product->id),
            'options' => $this->serializeOptions($product->id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $attributes = $request->all();
        $attributes['id'] = $id;
        $product = $this->setProduct($attributes);
        return response()->json([
            'success' => true,
            'data' => $this->serializeProduct($product->id),
            'options' => $this->serializeOptions($product->id)
        ]);
    }

    private function serializeProduct(int $productId): array
    {
        if (!$productId) {
            return [];
        }
        $product = Product::findOrFail($productId);
        return (new ProductEntity($product))->getFrontendData(['fees']);
    }

    private function serializeOptions(int $productId): array
    {
        return [
            'types' => $this->typeOptions,
            'ageRanges' => $this->getAgeRanges($productId)
        ];
    }

    private function setProduct(array $attributes): Product
    {
        if (!empty($attributes['name_description'])) {
            $description = (new DescriptionSetter($attributes['name_description']))->set();
            $attributes['name_description_id'] = $description->id;
        }

        if (!empty($attributes['program_id'])) {
            $attributes['productable_type'] = Program::class;
            $attributes['productable_id'] = $attributes['program_id'];
        }

        $product = (new ProductSetter($attributes))->set();

        Fee::where('product_id', $product->id)->delete();
        if (!empty($attributes['fees'])) {
            foreach ($attributes['fees'] as $feeData) {
                $feeData['product_id'] = $product->id;
                (new FeeSetter($feeData))->set();
            }
        }

        return $product;
    }

    private function getAgeRanges(int $productId): array
    {
        $models = AgeRange::forAgeRangeable(Product::class, $productId)->orderBy('from_age')->get();
        return AgeRangeEntity::getCollection($models);
    }
}
