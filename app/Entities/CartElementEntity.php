<?php

namespace App\Entities;

use App\Cruise;
use App\Device;
use App\ShipGroup;

class CartElementEntity
{

    public $id;
    public $productableType;
    public $productableModel;
    public $orderItemableId;
    public $orderItemableType;
    public $orderItemable;
    public $orderItemableIndex;
    public $mealPlan;
    public $interval;
    public $calculatedPrice;
    public $searchRequest;
    public $amount;
    public $margin;
    public $guests;
    public $isOverbooked;
    public $requestHash;
    public $optionalFees;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

    }

    public function update($searchResult)
    {
        $availability = $this->findAvailabilityInSearchResult($searchResult);
        $this->isOverbooked = $availability ? $availability['is_overbooked'] : true;
        $this->updateCalculatedPriceBySearchResult($searchResult);
    }

    private function findAvailabilityInSearchResult($searchResult)
    {
        $listIndex = $this->getResultListIndex($searchResult);
        if (isset($searchResult[$listIndex])) {
            if ($this->orderItemableType == ShipGroup::class && $searchResult[$listIndex]['availability']['id'] == $this->orderItemableId) {
                return $searchResult[$listIndex]['availability'];
            }
            foreach ($searchResult[$listIndex]['availability'] as $availabilityData) {
                if ($this->orderItemableType == Device::class && $availabilityData['device_id'] == $this->orderItemableId) {
                    return $availabilityData;
                }
            }
        }
        return null;
    }

    private function updateCalculatedPriceBySearchResult($searchResult)
    {
        $listIndex = $this->getResultListIndex($searchResult);
        if (isset($searchResult[$listIndex])) {
            $resultItem = $searchResult[$listIndex]['results'][$this->orderItemableIndex];
            if (isset($resultItem['ship_group_id'])) {
                if ($this->orderItemableType == ShipGroup::class && $resultItem['ship_group_id'] == $this->orderItemableId) {
                    if($this->setMatchedCalculatedPrice($resultItem)){
                        return;
                    }
                }
            } else {
                foreach ($resultItem as $resultItemData) {
                    if ($this->orderItemableType == Device::class && $resultItemData['device_id'] == $this->orderItemableId) {
                        if($this->setMatchedCalculatedPrice($resultItemData)){
                            return;
                        }
                    }
                }
            }
        }
    }

    private function setMatchedCalculatedPrice(array $resultItem): bool {
        foreach ($resultItem['prices'] as $priceData) {
            if ($priceData['meal_plan'] == $this->mealPlan) {
                $this->calculatedPrice = $priceData;
                return true;
            }
        }
        return false;
    }

    private function getResultListIndex($searchResult)
    {
        if ($this->productableType == Cruise::class) {
            foreach ($searchResult as $index => $resultItem) {
                if ($resultItem['info']['id'] == $this->productableModel['id']) {
                    return $index;
                }
            }
        }
        return $this->productableModel['id'];
    }

    public static function hydrate(array $elementsData)
    {
        $return = [];
        foreach ($elementsData as $elementData) {
            $return[] = new self($elementData);
        }
        return $return;
    }

}
