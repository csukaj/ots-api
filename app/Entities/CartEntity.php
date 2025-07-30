<?php

namespace App\Entities;

use App\Accommodation;
use App\Cruise;
use App\Entities\Search\AccommodationSearchEntity;
use App\Entities\Search\CharterSearchEntity;
use App\Entities\Search\CruiseSearchEntity;
use App\Exceptions\UserException;
use App\ShipGroup;

class CartEntity
{

    public $elements;
    public $familyComboSelections;
    private $pivotElementToSearch = [];

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @param bool $calculateMargins
     * @return CartEntity
     * @throws UserException
     */
    public function update($calculateMargins = false): CartEntity
    {
        $searchRequests = $this->getSearchRequests();

        $searchResults = [];
        $requestValidities = [];
        foreach ($searchRequests as $searchRequest) {
            switch ($searchRequest['productable_type']) {
                case Accommodation::class:
                    $searchEntity = new AccommodationSearchEntity();
                    break;
                case Cruise::class:
                    $searchEntity = new CruiseSearchEntity();
                    break;
                case ShipGroup::class:
                    $searchEntity = new CharterSearchEntity();
                    break;
                default:
                    throw new UserException('Unsupported productable type!');
            }

            $isValid = $searchEntity->isRequestParametersValid($searchRequest);
            $requestValidities[] = $isValid;
            if($calculateMargins){
                $searchEntity->calculateMargins();
            }
            if ($isValid) {
                $searchEntity->setParameters($searchRequest);
                $searchResults[] = $searchEntity->getFrontendData(['availability', 'frontend']);
            } else {
                $searchResults[] = null;
            }
        }

        foreach ($this->pivotElementToSearch as $cartElementKey => $searchResultKey) {
            if ($requestValidities[$searchResultKey]) {
                $this->elements[$cartElementKey]->update($searchResults[$searchResultKey]);
            } else {
                unset($this->elements[$cartElementKey]);
            }
        }

        return $this;
    }

    private function getSearchRequests(): array
    {
        $cartSummary = $this->getCartSummary();
        $this->pivotElementToSearch = [];

        $searchRequests = [];
        foreach ($this->elements as $cartElementKey => $cartElement) {
            $found = false;
            foreach ($searchRequests as $searchRequestKey => $searchRequest) {
                if ($searchRequest == $cartElement->searchRequest) {
                    $found = true;
                    $this->pivotElementToSearch[$cartElementKey] = $searchRequestKey;
                }
            }
            if (!$found) {
                $searchRequest = $cartElement->searchRequest;
                $searchRequest['cart_summary'] = $cartSummary;
                $searchRequest['productable_type'] = $cartElement->productableType;
                $searchRequests[] = $searchRequest;
                $this->pivotElementToSearch[$cartElementKey] = count($searchRequests) - 1;
            }
        }

        return $searchRequests;
    }

    private function getCartSummary()
    {
        $cartSummaryElements = [];
        foreach ($this->elements as $cartElement) {
            $cartSummaryElements[] = [
                'id' => $cartElement->id,
                'productable_type' => $cartElement->productableType,
                'productable_model_id' => $cartElement->productableModel['id'],
                'discountable_type' => $cartElement->productableType,
                'discountable_id' => $cartElement->productableModel['id'],
                'order_itemable_type' => $cartElement->orderItemableType,
                'order_itemable_id' => $cartElement->orderItemableId,
                'order_itemable_name' => $cartElement->orderItemable['name']['en'],
                'meal_plan' => $cartElement->mealPlan,
                'interval' => $cartElement->interval,
                'amount' => $cartElement->amount,
                'order_itemable_index' => $cartElement->orderItemableIndex,
                'usage_request' => $cartElement->searchRequest['usages'][$cartElement->orderItemableIndex]['usage']
            ];
        }
        return (object)[
            'elements' => $cartSummaryElements,
            'familyComboSelections' => $this->familyComboSelections
        ];
    }

}
