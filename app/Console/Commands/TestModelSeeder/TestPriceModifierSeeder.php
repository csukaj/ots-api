<?php
namespace App\Console\Commands\TestModelSeeder;

use App\DateRange;
use App\Device;
use App\Facades\Config;
use App\MealPlan;
use App\OfferClassification;
use App\OfferMeta;
use App\PriceModifier;
use App\PriceModifierClassification;
use App\PriceModifierCombination;
use App\PriceModifierMeta;
use App\PriceModifierPeriod;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Seeds price modifiers to database
 */
class TestPriceModifierSeeder
{

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function seed(string $modelType, int $modelId, array $modelData, array $dateRangeRelativeIds)
    {
        if(empty($modelData['discounts']))
            return;
        
        $PriceModifierIds = [];
        foreach ($modelData['discounts'] as $priceModifierData) {
            $PriceModifierIds[$priceModifierData['relative_id']] = $this->setPriceModifier($modelType, $modelId, $priceModifierData, $dateRangeRelativeIds)->id;
        }

        foreach ($modelData['discount_combinations'] as $data) {
            $this->setCombinations($data, $PriceModifierIds);
        }

        // refresh price modifier order by predefined priority
        $this->updateModelPricemodifierPriorities($modelType, $modelId);
    }

    /**
     * Sets a price modifier with all associated data
     * 
     * @param int $modelId
     * @param array $priceModifierData
     * @param array $dateRangeRelativeIds
     * @return PriceModifier
     */
    private function setPriceModifier(string $modelType, int $modelId, array $priceModifierData, array $dateRangeRelativeIds): PriceModifier
    {
        if (isset($priceModifierData['name'])) {
            $nameDescription = (new DescriptionSetter($priceModifierData['name']))->set();
            $priceModifierData['name_description_id'] = $nameDescription->id;
        }

        if (isset($priceModifierData['description'])) {
            $descriptionDescription = (new DescriptionSetter($priceModifierData['description']))->set();
            $priceModifierData['description_description_id'] = $descriptionDescription->id;
        }


        $typeName = Config::getOrFail('taxonomies.price_modifier_types.'.$priceModifierData['type'].'.name');

        $typeTx = Taxonomy::getTaxonomy($typeName, Config::getOrFail('taxonomies.price_modifier_type'));
        $conditionTx = Taxonomy::getTaxonomyGrandChild($priceModifierData['condition'], Config::getOrFail('taxonomies.price_modifier_application_level'));
        $offerTx = Taxonomy::getTaxonomy($priceModifierData['offer']['name'], Config::getOrFail('taxonomies.price_modifier_offer'));

        $priceModifier = new PriceModifier($priceModifierData);
        $priceModifier->modifier_type_taxonomy_id = $typeTx->id;
        $priceModifier->condition_taxonomy_id = $conditionTx->id;
        $priceModifier->offer_taxonomy_id = $offerTx->id;
        $priceModifier->promo_code = (!empty($priceModifierData['promo_code'])) ? $priceModifierData['promo_code'] : null;
        $priceModifier->is_active = (!empty($priceModifierData['is_active']));
        $priceModifier->is_annual = (!empty($priceModifierData['is_annual']));
        $priceModifier->saveOrFail();

        $this->setClassification($priceModifier, OfferClassification::class, $offerTx, $priceModifierData['offer']['classifications']);
        $this->setMeta($priceModifier, OfferMeta::class, $offerTx, $priceModifierData['offer']['metas']);

        $this->setClassification($priceModifier, PriceModifierClassification::class, $conditionTx, $priceModifierData['classifications']);
        $this->setMeta($priceModifier, PriceModifierMeta::class, $conditionTx, $priceModifierData['metas']);

        $dateRangeIds = [];
        if (!empty($priceModifierData['date_ranges'])) {
            foreach ($priceModifierData['date_ranges'] as $dateRangeData) {
                $dateRangeIds[] = DateRange::setByData($modelType, $modelId, $dateRangeData)->id;
            }
        } elseif (!empty($priceModifierData['date_range_relative_ids'])) {
            foreach ($priceModifierData['date_range_relative_ids'] as $dateRangeRelativeId) {
                $dateRangeIds[] = $dateRangeRelativeIds[$dateRangeRelativeId];
            }
        }
        foreach ($dateRangeIds as $dateRangeId) {
            $this->setPriceModifierPeriod($priceModifier->id, $dateRangeId);
        }

        return $priceModifier;
    }

    /**
     * Sets combinations for price modifiers
     * 
     * @param array $data Combination data with relative price modifier ids
     * @param array $priceModifierIds list of real price modifiers
     * @return PriceModifierCombination
     */
    private function setCombinations($data, $priceModifierIds)
    {
        $insertableIds = [];
        foreach ($data as $relativePricemodifierId) {
            $insertableIds[] = $priceModifierIds[$relativePricemodifierId];
        }

        return PriceModifierCombination::set($insertableIds[0], $insertableIds[1]);
    }

    /**
     * Sets classification data to a price modifier
     * 
     * @param PriceModifier $priceModifier
     * @param string $className classification class name
     * @param Taxonomy $parentTx parent taxonomy
     * @param array $classificationData data to set
     */
    private function setClassification(PriceModifier $priceModifier, string $className, Taxonomy $parentTx, $classificationData)
    {
        $classificationTx = Taxonomy::getTaxonomy('classification', $parentTx->id);

        foreach ($classificationData as $value) {
            $valueTx = Taxonomy::getTaxonomy($value, $classificationTx->id);
            $meta = new $className();
            $meta->price_modifier_id = $priceModifier->id;
            $meta->classification_taxonomy_id = $classificationTx->id;
            $meta->value_taxonomy_id = $valueTx->id;
            $meta->saveOrFail();
        }
    }

    /**
     * Sets meta data to a price modifier
     * 
     * @param PriceModifier $priceModifier
     * @param string $className classification class name
     * @param Taxonomy $parentTx parent taxonomy
     * @param array $metaData
     */
    private function setMeta(PriceModifier $priceModifier, string $className, Taxonomy $parentTx, $metaData)
    {
        $metaTx = Taxonomy::getTaxonomy('meta', $parentTx->id);

        foreach ($metaData as $name => $value) {
            $taxonomy = Taxonomy::getTaxonomy($name, $metaTx->id);

            switch ($name) {
                case 'restricted_to_device_ids':
                    $value = $this->getRealDeviceIds($value);
                    break;
                case 'minimum_nights_checking_level':
                    $value = Config::getOrFail('taxonomies.minimum_nights_checking_levels.'.$value.'.id');
                    break;
                case 'recalculate_using_meal_plan':
                    $value = $this->getRealMealPlanIds([$value])[0];
                    break;
                case 'restricted_to_meal_plan_ids':
                    $value = implode(',', $this->getRealMealPlanIds($value));
                    break;
                case 'participating_organization_ids':
                case 'recalculate_using_products':
                    $value = implode(',', $value);
                    break;
            }

            $meta = new $className();
            $meta->price_modifier_id = $priceModifier->id;
            $meta->taxonomy_id = $taxonomy->id;
            $meta->value = $value;
            $meta->saveOrFail();
        }
    }

    /**
     * Creates a new price modifier period
     * 
     * @param int $priceModifierId
     * @param int $dateRangeId
     * @return PriceModifierPeriod
     */
    private function setPriceModifierPeriod(int $priceModifierId, int $dateRangeId)
    {
        $period = new PriceModifierPeriod([
            'price_modifier_id' => $priceModifierId,
            'date_range_id' => $dateRangeId,
        ]);
        $period->saveOrFail();
        return $period;
    }

    /**
     * Translates relative device ids to real device ids.
     * Input can be an array, an integer or a comma separated list of ids as string.
     * Return type is set according to input type.
     * 
     * @param string|int|array $value
     * @return string|int|array
     */
    private function getRealDeviceIds($value)
    {
        if (is_string($value)) {
            $values = array_map('trim', explode(',', $value));
        } elseif (is_int($value)) {
            $values = [$value];
        } else {
            $values = $value;
        }
        $deviceIds = Device::orderBy('id')->select('id')->pluck('id');
        $result = [];
        foreach ($values as $relative_id) {
            if (isset($deviceIds[$relative_id - 1])) {
                $result[] = $deviceIds[$relative_id - 1];
            }
        }
        if (is_string($value)) {
            return implode(',', $result);
        } elseif (is_int($value)) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * Translates meal plan ids to real ids
     * 
     * @param array $values
     * @return array
     */
    private function getRealMealPlanIds($values)
    {
        $mealPlans = MealPlan::getMealPlanNames();
        return array_keys(array_intersect($mealPlans, $values));
    }

    /**
     * Sort price modifiers in a model by predefined sort order
     * 
     * @param int $modelId
     */
    public function updateModelPricemodifierPriorities(string $modelType, int $modelId)
    {

        $samplePriceModifier = PriceModifier::forModel($modelType, $modelId)->first();
        $siblings = $samplePriceModifier->findSiblingsInOrder(true);

        foreach (PriceModifier::sortbyPriority($siblings) as $idx => $siblingPriceModifier) {
            $siblingPriceModifier->priority = $idx + 1;
            $siblingPriceModifier->save();
        }
    }
}
