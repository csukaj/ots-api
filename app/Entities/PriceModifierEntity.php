<?php

namespace App\Entities;

use App\OfferClassification;
use App\OfferMeta;
use App\PriceModifier;
use App\PriceModifierClassification;
use App\PriceModifierMeta;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class PriceModifierEntity extends Entity
{

    const CONNECTION_COLUMN = 'price_modifier_id';

    protected $model;

    public function __construct(PriceModifier $priceModifier)
    {
        parent::__construct($priceModifier);
    }

    public function getFrontendData(array $additions = []): array
    {
        $description = $this->model->description;
        $dateRanges = $this->model->dateRanges()
            ->with(['name', 'type', 'marginType', 'modelMealPlans'])
            ->orderBy('from_time')->get();
        $dateRange = $dateRanges->first();

        $return = [
            'id' => $this->model->id,
            'price_modifiable_type' => ($dateRange) ? $dateRange->date_rangeable_type : null,
            'price_modifiable_id' => ($dateRange) ? $dateRange->date_rangeable_id : null,
            'name' => (new DescriptionEntity($this->model->name))->getFrontendData(),
            'description' => $description ? (new DescriptionEntity($description))->getFrontendData() : null,
            'modifier_type' => $this->model->modifierType->name,
            'condition' => $this->model->condition->name,
            'offer' => $this->model->offer->name,
            'is_active' => $this->model->is_active,
            'is_annual' => $this->model->is_annual,
            'priority' => $this->model->priority,
            'date_ranges' => DateRangeEntity::getCollection($dateRanges),
            'promo_code' => $this->model->promo_code
        ];


        foreach ($additions as $addition) {
            switch ($addition) {
                case 'properties':
                    $return['condition_properties']['metas'] = $this->getPriceModifierMetas();
                    $return['offer_properties']['metas'] = $this->getOfferMetas();
                    $return['condition_properties']['classifications'] = $this->getPriceModifierClassifications();
                    $return['offer_properties']['classifications'] = $this->getOfferClassifications();
                    break;
            }
        }

        return $return;
    }

    private function getPriceModifierClassifications()
    {
        $model = new PriceModifierClassification();
        return $model->getClassificationEntities(self::CONNECTION_COLUMN, $this->model->id);
    }

    private function getOfferClassifications()
    {
        $model = new OfferClassification();
        return $model->getClassificationEntities(self::CONNECTION_COLUMN, $this->model->id);
    }

    private function getPriceModifierMetas()
    {
        $model = new PriceModifierMeta();
        return $model->getMetaEntities(self::CONNECTION_COLUMN, $this->model->id);
    }

    private function getOfferMetas()
    {
        $model = new OfferMeta();
        return $model->getMetaEntities(self::CONNECTION_COLUMN, $this->model->id);
    }

}
