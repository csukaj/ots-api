<?php

namespace App\Http\Controllers;

use App\Entities\CartElementEntity;
use App\Entities\CartEntity;
use App\Facades\Config;
use App\PriceModifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource CartController
 */
class CartController extends Controller
{

    /**
     * update
     * Update cart elements by re-running accommodation searches and updating price & Price Modifier calculations
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function update(Request $request): JsonResponse
    {
        $requestArray = $request->toArray();
        $entityProperties = [
            'elements' => CartElementEntity::hydrate($requestArray['elements']),
        ];
        if (isset($requestArray['familyComboSelections'])) {
            $entityProperties['familyComboSelections'] = $requestArray['familyComboSelections'];
        }

        $cart = new CartEntity($entityProperties);
        return response()->json([
            'success' => true,
            'data' => $cart->update()->elements,
            'familyComboDiscountableIds' => $this->getOrganizationIdsWithFamilyComboDiscount()
        ]);
    }

    private function getOrganizationIdsWithFamilyComboDiscount(): array
    {
        $organizationIds = [];
        $familyComboTxId = Config::getOrFail('taxonomies.price_modifier_application_levels.cart.price_modifier_condition_types.family_room_combo.id');
        $comboPriceModifiers = PriceModifier::where('condition_taxonomy_id', $familyComboTxId)->get();
        foreach ($comboPriceModifiers as $comboPriceModifier) {
            $organization = $comboPriceModifier->getPricemodifiableModel();
            if ($organization) {
                $organizationIds[] = $organization->id;
            }
        }
        return $organizationIds;
    }
}
