<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * @resource Admin/OrganizationPricesController
 */
class PricesController extends ResourceController
{

    protected $entityAdditions = [];
    protected $modelClass = '';
    protected $entityClass = '';

    protected function setClasses(string $modelClass, string $entityClass)
    {
        $this->modelClass = $modelClass;
        $this->entityClass = $entityClass;
    }

    /**
     * show
     * Display the price settings & OrganizationPrices & price names of a specified Organization
     * @param  int $id
     * @param  Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function show($id, Request $request = null): JsonResponse
    {

        $productType = $request->get('product-type', 'accommodation');
        if (!Taxonomy::taxonomyExists($productType, Config::getOrFail('taxonomies.product_type'))) {
            $productType = 'accommodation';
        }
        $entity = new $this->entityClass($this->modelClass::findOrFail($id), null, null, $productType);
        return response()->json([
            'success' => true,
            'data' => $entity->getFrontendData($this->entityAdditions),
            'price_names' => TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.names.price_name'))->getChildren(),
                ['translations'])
        ]);
    }

    /**
     * update
     * Update OrganizationPrices & price settings of the specified Organization
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $model = $this->modelClass::findOrFail($id);
        $model->fill($request->toArray());
        $model->pricing_logic_taxonomy_id = Taxonomy::getTaxonomy($request->pricing_logic,
            Config::get('taxonomies.pricing_logic'))->id;
        $model->margin_type_taxonomy_id = Taxonomy::getTaxonomy($request->margin_type,
            Config::get('taxonomies.margin_type'))->id;
        $model->saveOrFail();
        return response()->json([
            'success' => true,
            'data' => (new $this->entityClass($model))->getFrontendData($this->entityAdditions),
            'price_names' => TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.names.price_name'))->getChildren(),
                ['translations'])
        ]);
    }
}
