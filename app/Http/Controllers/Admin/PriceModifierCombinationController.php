<?php

namespace App\Http\Controllers\Admin;

use App\Entities\PriceModifierCombinationEntity;
use App\Entities\PriceModifierEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\PriceModifierSetter;
use App\PriceModifier;
use App\PriceModifierCombination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/PriceModifierCombinationController
 */
class PriceModifierCombinationController extends ResourceController
{

    /**
     * index
     * Display a listing of price modifier combinations & price modifiers
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {

        $priceModifierIds = PriceModifier::getModelPriceModifierIds($request->input('price_modifiable_type'),
            $request->input('price_modifiable_id'));
        $combinations = PriceModifierCombination::getForPriceModifiers($priceModifierIds);
        return response()->json([
            'success' => true,
            'data' => PriceModifierCombinationEntity::getCollection($combinations),
            'price_modifiers' => PriceModifierEntity::getCollection(PriceModifier::orderBy('priority')->find($priceModifierIds))
        ]);
    }

    /**
     * store
     * Store a newly created PriceModifierCombination
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {

        $priceModifierIds = PriceModifier::getModelPriceModifierIds($request->input('price_modifiable_type'),
            $request->input('price_modifiable_id'));
        $existingCombinations = PriceModifierCombination::getForPriceModifiers($priceModifierIds);

        $lastSetter = null;
        foreach ($request->input('priceModifiers') as $inputPriceModifier) {
            $lastSetter = (new PriceModifierSetter($inputPriceModifier));
            $lastSetter->setPriority($inputPriceModifier['priority']);
        }
        if ($lastSetter) { // if anything has changed
            $lastSetter->updateModelPriceModifierPriorities();
        }

        foreach ($existingCombinations as $cmb) {
            $found = false;
            foreach ($request->input('combinations') as $inputCombination) {
                if ($cmb->first_price_modifier_id == $inputCombination['first_price_modifier_id'] && $cmb->second_price_modifier_id == $inputCombination['second_price_modifier_id']) {
                    $found = true;
                }
            }

            if (!$found) {
                $cmb->delete();
            }
        }

        foreach ($request->input('combinations') as $inputCombination) {
            PriceModifierCombination::set($inputCombination['first_price_modifier_id'],
                $inputCombination['second_price_modifier_id']);
        }

        if (count($priceModifierIds)) {
            PriceModifier::find($priceModifierIds)->first()->touchModel();
        }

        return response()->json([
            'success' => true,
            'data' => PriceModifierCombinationEntity::getCollection(PriceModifierCombination::getForPriceModifiers($priceModifierIds)),
            'price_modifiers' => PriceModifierEntity::getCollection(PriceModifier::orderBy('priority')->find($priceModifierIds))
        ]);
    }

    /**
     * show
     * Display the specified PriceModifierCombination
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new PriceModifierCombinationEntity(PriceModifierCombination::findOrFail($id)))->getFrontendData()
        ]);
    }
}
