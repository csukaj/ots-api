<?php
namespace App\Console\Commands\TestModelSeeder;

use App\AgeRange;
use App\Facades\Config;
use App\Manipulators\PriceElementSetter;
use App\Manipulators\PriceSetter;
use App\Manipulators\ProductSetter;
use App\MealPlan;
use App\ModelMealPlan;
use App\PriceElement;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyTranslation;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Class to seed a accommodation test data
 */
class TestProductSeeder
{

    public function __construct()
    {
        //
    }

    /**
     * Seed a accommodation with all associated data, including price modifiers
     *
     * @param array $data
     */
    public function seed(array $params, array $dateRangeRelativeIds, array $data)
    {
        if (!empty($data['products'])) {
            foreach ($data['products'] as $productData) {
                self::setProduct($params['modelType'], $params['modelId'], $params['productableType'], $params['productableId'], $dateRangeRelativeIds, $productData);
            }
        }
    }

    /**
     * Creates a Product object with provided data. It also creates Prices
     *
     * @param string $modelType
     * @param int $modelId
     * @param string $productableType
     * @param int $productableId
     * @param array $dateRangeRelativeIds
     * @param array $productData
     * @throws \Exception
     */
    public static function setProduct(string $modelType, int $modelId, string $productableType, int $productableId, array $dateRangeRelativeIds, array $productData)
    {
        if (isset($productData['name'])) {
            $nameDescription = (new DescriptionSetter($productData['name']))->set();
        } else {
            $nameDescription = null;
        }
        $product = (new ProductSetter([
            'id' => $productData['id'],
            'productable_id' => $productableId,
            'productable_type' => $productableType,
            'type_taxonomy_id' => Config::getOrFail('taxonomies.product_types.' . $productData['type']),
            'name_description_id' => $nameDescription ? $nameDescription->id : null
            ]))->set(true);

        if (!empty($productData['prices'])) {
            foreach ($productData['prices'] as $priceData) {
                self::setPrice($modelType, $modelId, $product->id, $dateRangeRelativeIds, $priceData);
            }
        }
    }

    /**
     * Creates a Price object with provided data. It also creates PriceElements
     *
     * @param string $modelType
     * @param int $modelId
     * @param int $productId
     * @param array $dateRangeRelativeIds
     * @param array $priceData
     * @return int
     */
    public static function setPrice(string $modelType, int $modelId, int $productId, array $dateRangeRelativeIds, array $priceData)
    {
        $nameTx = self::setTaxonomyObj($priceData['name_taxonomy'], Config::getOrFail('taxonomies.names.price_name'));
        $priceSetterData = [
            'product_id' => $productId,
            'name_taxonomy_id' => $nameTx->id,
            'age_range_id' => !empty($priceData['age_range']) ? AgeRange::findByNameOrFail($priceData['age_range'], $modelType, $modelId)->id : null,
            'amount' => !empty($priceData['amount']) ? $priceData['amount'] : null,
            'margin_value' => isset($priceData['margin_value']) ? $priceData['margin_value'] : null,
            'extra' => !empty($priceData['extra']),
            'mandatory' => !empty($priceData['mandatory']),
            'discount' => !empty($priceData['discount'])
        ];
        $price = (new PriceSetter($priceSetterData))->set();

        foreach ($priceData['elements'] as $priceElementData) {
            self::setPriceElement($modelType, $modelId, $price->id, $dateRangeRelativeIds, $priceElementData);
        }
    }

    /**
     * Creates (or get) Taxonomy data and adds translations to it.
     * @param array $taxonomyData
     * @param int $parentTxId
     * @return Taxonomy
     */
    public static function setTaxonomyObj(array $taxonomyData, int $parentTxId)
    {
        $languages = Language::getLanguageCodes();

        $tx = Taxonomy::getOrCreateTaxonomy($taxonomyData['en'], $parentTxId);

        foreach ($taxonomyData as $languageCode => $name) {
            if ($languageCode == 'en') {
                continue;
            }

            $txTranslation = new TaxonomyTranslation([
                'language_id' => $languages[$languageCode],
                'taxonomy_id' => $tx->id,
                'name' => $name
            ]);
            $txTranslation->save();
        }

        return $tx;
    }

    /**
     * Creates a PriceElement object with provided data. It also creates ModelMealPlan
     *
     * @param string $modelType
     * @param int $modelId
     * @param int $priceId
     * @param array $dateRangeRelativeIds
     * @param array $priceElementData
     * @throws \App\Exceptions\UserException
     */
    public static function setPriceElement(
    string $modelType, int $modelId, int $priceId, array $dateRangeRelativeIds, array $priceElementData
    )
    {
        $mealPlan = MealPlan::findByName($priceElementData['meal_plan']);
        $modelMealPlan = ModelMealPlan::createOrRestore([
            'meal_planable_type' => $modelType,
            'meal_planable_id' => $modelId,
            'meal_plan_id' => $mealPlan->id,
            'date_range_id' => $dateRangeRelativeIds[$priceElementData['date_range_relative_id']]
        ]);

        $priceElementAttributes = [
            'price_id' => $priceId,
            'model_meal_plan_id' => $modelMealPlan->id,
            'date_range_id' => $dateRangeRelativeIds[$priceElementData['date_range_relative_id']],
            'net_price' => isset($priceElementData['net_price']) ? $priceElementData['net_price'] : null,
            'rack_price' => isset($priceElementData['rack_price']) ? $priceElementData['rack_price'] : null,
            'margin_value' => isset($priceElementData['margin_value']) ? $priceElementData['margin_value'] : null
        ];
        (new PriceElementSetter($priceElementAttributes))->set();
    }
}
