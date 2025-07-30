<?php

namespace App\Http\Controllers\Admin;

use App\DateRange;
use App\Entities\DateRangeEntity;
use App\Entities\PriceModifierEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\PriceModifierSetter;
use App\PriceModifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * @resource Admin/PriceModifierController
 */
class PriceModifierController extends ResourceController
{

    /**
     * index
     * Display a listing of price modifiers, Types, Offers & DateRanges
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $priceModifiable = $request->input('price_modifiable_type')::findOrFail($request->input('price_modifiable_id'));
        $priceModifiableType = get_class($priceModifiable);
        $application_levels = array_keys(Config::get('taxonomies.price_modifier_application_levels'));
        $typeTaxonomy = Taxonomy::findOrFail(Config::get('taxonomies.price_modifier_type'));
        $conditionTypeTaxonomies = new Collection();
        foreach ($application_levels as $level) {
            $conditionTxs = Taxonomy::findOrFail(Config::get('taxonomies.price_modifier_application_levels.' . $level . '.id'))->getChildren();
            $conditionTypeTaxonomies = $conditionTypeTaxonomies->merge($conditionTxs);
        }
        $priceModifierOfferTaxonomies = Taxonomy::findOrFail(Config::get('taxonomies.price_modifier_offer'))->getChildren();
        $priceModifierDateRanges = DateRange::priceModifier()->forDateRangeable($priceModifiableType,
            $priceModifiable->id)->orderBy('from_time', 'asc')->get();

        $priceModifierIds = PriceModifier::getModelPriceModifierIds($priceModifiableType, $priceModifiable->id);
        return response()->json([
            'success' => true,
            'data' => PriceModifierEntity::getCollection(PriceModifier::orderBy('priority')->find($priceModifierIds),
                ['properties']),
            'modifier_types' => (new TaxonomyEntity($typeTaxonomy))->getFrontendData(['descendants'])['descendants'],
            'conditions' => TaxonomyEntity::getCollection($conditionTypeTaxonomies, ['descendants', 'relation'],
                [$priceModifiable]),
            'offers' => TaxonomyEntity::getCollection($priceModifierOfferTaxonomies, ['descendants', 'relation'],
                [$priceModifiable]),
            'date_ranges' => DateRangeEntity::getCollection($priceModifierDateRanges)
        ]);
    }

    /**
     * store
     * Store a newly created price modifier
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $priceModifier = (new PriceModifierSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new PriceModifierEntity($priceModifier))->getFrontendData(['properties'])
        ]);
    }

    /**
     * show
     * Display the specified price modifier
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new PriceModifierEntity(PriceModifier::findOrFail($id)))->getFrontendData(['properties'])
        ]);
    }

    /**
     * destroy
     * Remove the specified price modifier
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return response()->json(['success' => (bool)PriceModifier::destroy($id), 'data' => []]);
    }
}
