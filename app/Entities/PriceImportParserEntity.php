<?php

namespace App\Entities;

use App\MealPlan;
use App\DateRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\PriceElement;

/**
 * Class PriceImportParserEntity
 * @package App\Entities
 */
class PriceImportParserEntity extends Entity
{
    /**
     * @var string
     */
    public $originalFileContent;
    /**
     * @var array
     */
    public $originalArray = [];
    /**
     * @var array
     */
    public $importedArray = [];
    /**
     * @var array
     */
    public $dateRanges = [];
    /**
     * @var array
     */
    public $mealPlans = [];
    /**
     * @var array
     */
    public $priceIds = [];
    /**
     * @var array
     */
    public $priceElements = [];
    /**
     * @var array
     */
    public $priceList = [];
    /**
     * @var
     */
    public $isFromNetPrice;
    /**
     * @var
     */
    public $marginTypeTaxonomyId;

    public function __construct(string $originalFileContent = '') {
        parent::__construct();

        if ($originalFileContent) {
            /** @var string $originalFileContent */
            $this->init($originalFileContent);
        }
    }

    /**
     * @param $originalFileContent
     * @throws UserException
     */
    public function init($originalFileContent) {
        $this->originalFileContent = $originalFileContent;
        $this->originalArray = self::parseCsvToArray($this->originalFileContent);
        $this->importedArray = self::clearArray($this->originalArray);

        if ( empty($this->importedArray) ) {
            throw new UserException('The csv parsed is failed or the file is empty.');
        }
    }

    public static function parseCsvToArray(string $file): array {
        return $array = array_map(function($csv) {
            return str_getcsv($csv, ";");
        }, file($file));
    }

    public static function clearArray(array $array): array {
        if (empty($array)) return [];
        foreach ($array as $key=>$item) {
            if ($key > 3 && (is_null($item[0]) || empty($item[0])))
                unset($array[$key]);
        }
        return $array;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function run() {
        $this->dateRanges = self::parseDateRanges($this->importedArray);
        $this->mealPlans = self::parseMealPlans($this->importedArray);
        $this->priceIds = self::parsePriceIds($this->importedArray);
        $this->priceElements = self::parsePriceElements($this->importedArray);
        $this->isFromNetPrice = self::setIsFromNetPrice(self::getDateRangeId($this->dateRanges));
        $this->marginTypeTaxonomyId = self::setMarginTypeTaxonomyId(self::getDateRangeId($this->dateRanges));

        return $this;
    }

    /**
     * @param array $arr
     * @return array
     * @throws UserException
     */
    public static function parseDateRanges(array $arr): array {
        if (empty($arr)) {
            throw new UserException('parseDateRanges() fn param is empty.');
        }
        $dateRanges = [];
        foreach ($arr[2] as $key=>$dateRange) {
            if ( !empty($dateRange)
                && preg_match("/^(\d{4}.{1}\d{2}.{1}\d{2}.{3}\d{4}.{1}\d{2}.{1}\d{2})$/", $dateRange)
            ) {
                if (preg_match("/#/", $arr[0][$key])) {
                    $key = explode("#", explode('|', $arr[0][$key])[0])[1];
                    $dateRanges[$key] = $dateRange;
                }
            }
        }
        if ( empty($dateRanges) ) {
            throw new UserException('Date ranges missing.');
        }
        return $dateRanges;
    }

    /**
     * @param array $arr
     * @return array
     * @throws UserException
     */
    public static function parseMealPlans(array $arr): array {
        if (empty($arr)) {
            throw new UserException('parseMealPlans() fn param is empty.');
        }
        $tmp = $arr[3];
        unset($tmp[0]);
        unset($tmp[1]);
        unset($tmp[2]);
        unset($tmp[3]);
        $mealPlans = [];
        $mealPlanNames = MealPlan::getMealPlanNames();
        foreach ($tmp as $key=>$mealPlan) {
            if (!empty($mealPlan)
                && preg_match("/^(\w\/\w)$/", $mealPlan)
            ) {
                if (preg_match("/#|\|/", $arr[0][$key])) {
                    $mealPlanId = explode("#", explode('|', $arr[0][$key])[1])[1];
                    if ( !in_array($mealPlan, $mealPlanNames)) {
                        throw new UserException("Meal plan is not valid.");
                    }
                    $mealPlans[$key] = ['id'=>$mealPlanId, 'name'=>$mealPlan];
                } else {
                    throw new UserException('Date range id or Meal plan id missing.');
                }
            }
        }
        if ( empty($mealPlans) ) {
            throw new UserException('Meal plans missing.');
        }
        return $mealPlans;
    }

    /**
     * @param array $arr
     * @return array
     * @throws UserException
     */
    public static function parsePriceIds(array $arr): array {
        if (empty($arr)) {
            throw new UserException('parsePriceIds() fn param is empty.');
        }
        $priceIds = [];
        foreach ($arr as $idx=>$row) {
            if ($idx>3) {
                if (!empty($row[0])) {
                    if ( isset(explode('#', $row[0])[1]) ) {
                        $priceIds[$idx] = ['id'=>explode('#', $row[0])[1], 'name'=>$row[3]];
                    } else {
                        throw new UserException('Price id is not valid.');
                    }
                } else {
                    throw new UserException('Price id missing.');
                }
            }
        }
        if ( empty($priceIds) ) {
            throw new UserException('Price ids missing.');
        }
        return $priceIds;
    }

    /**
     * @param array $arr
     * @return array
     * @throws UserException
     */
    public static function parsePriceElements(array $arr): array {
        if (empty($arr)) {
            throw new UserException('parsePriceElements() fn param is empty.');
        }
        $priceElements = [];
        foreach ($arr as $idx=>$row) {
            if ($idx>3) {
                foreach ($row as $key=>$element) {
                    if ($key>3)
                        $priceElements[$idx][$key] = $element;
                }
            }
        }
        if ( empty($priceElements) ) {
            throw new UserException('Price elements missing.');
        }
        return $priceElements;
    }

    /**
     * @param int $dateRangeId
     * @return bool
     * @throws \Exception
     */
    public static function setIsFromNetPrice(int $dateRangeId): bool {
        $fromNetPriceTaxonomyId = Config::getOrFail('taxonomies.pricing_logics.from_net_price');
        $pricingLogicTaxonomyId = DateRange::find($dateRangeId)->dateRangeable->pricing_logic_taxonomy_id;
        if ( is_numeric($fromNetPriceTaxonomyId) && is_numeric($pricingLogicTaxonomyId) ) {
            return $fromNetPriceTaxonomyId == $pricingLogicTaxonomyId;
        }
        throw new UserException('Net price or pricing logic error.');
    }

    /**
     * @param int $dateRangeId
     * @return int
     * @throws UserException
     */
    public static function setMarginTypeTaxonomyId(int $dateRangeId): int {
        $margin_type_taxonomy_id = DateRange::find($dateRangeId)->dateRangeable->margin_type_taxonomy_id;
        if ( is_numeric($margin_type_taxonomy_id) ) {
            return $margin_type_taxonomy_id;
        }
        throw new UserException('Margin type taxonomy id error.');
    }

    /**
     * @return PriceImportParserEntity
     * @throws UserException
     */
    public function createPriceList(): PriceImportParserEntity {
        if (empty($this->priceElements)) return $this;

        foreach ($this->priceElements as $idx=>$priceElementRow) {
            foreach ($priceElementRow as $key=>$item) {
                if (!empty($this->mealPlans[$key]['id'])) {
                    if (is_numeric($item) || $item == '') {
                        $this->priceList[] = [
                            'date_range_id'             => $this->mealPlans[$key]['id'],
                            'enabled'                   => is_numeric($item),
                            'price_id'                  => $this->priceIds[$idx]['id'],
                            'meal_plan'                 => $this->mealPlans[$key]['name'],
                            'model_meal_plan_id'        => PriceElement::getModelMealPlanId($this->mealPlans[$key]['id'], $this->mealPlans[$key]['name']),
                            'net_price'                 => $this->isFromNetPrice ? $item : null,
                            'rack_price'                => !$this->isFromNetPrice ? $item : null,
                            'margin_type_taxonomy_id'   => $this->marginTypeTaxonomyId
                        ];
                    } else {
                        $message = implode(" : ", [
                            $this->dateRanges[$this->mealPlans[$key]['id']],
                            $this->mealPlans[$key]['name'],
                            $this->priceIds[$idx]['name'],
                            "<strong>'$item'</strong>"
                        ]);
                        throw new UserException('Invalid price! <br/> Details: '.$this->defineError($item)."<br/>".$message);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $item
     * @return string
     */
    public function defineError($item): string {
        switch (true) {
            case preg_match('/,/', $item) > 0:
                return "The price must not contain ',' character! Use '.' character for delimiter!";
            case !is_numeric($item):
                return "The price is not numeric.";
        }
        return 'Unknown problem (still) ;)';
    }

    /**
     * @param $dateRanges
     * @return int
     */
    public static function getDateRangeId(array $dateRanges): int {
        $flipped = array_flip($dateRanges);
        return reset($flipped);
    }

    /**
     * @return string
     */
    public function getOriginalFileContent(): string
    {
        return $this->originalFileContent;
    }

    /**
     * @return array
     */
    public function getOriginalArray(): array
    {
        return $this->originalArray;
    }

    /**
     * @return array
     */
    public function getImportedArray(): array
    {
        return $this->importedArray;
    }

    /**
     * @return array
     */
    public function getDateRanges(): array
    {
        return $this->dateRanges;
    }

    /**
     * @return array
     */
    public function getMealPlans(): array
    {
        return $this->mealPlans;
    }

    /**
     * @return array
     */
    public function getPriceIds(): array
    {
        return $this->priceIds;
    }

    /**
     * @return array
     */
    public function getPriceElements(): array
    {
        return $this->priceElements;
    }

    /**
     * @return array
     */
    public function getPriceList(): array
    {
        return $this->priceList;
    }

    /**
     * @return mixed
     */
    public function getIsFromNetPrice()
    {
        return $this->isFromNetPrice;
    }

    /**
     * @return mixed
     */
    public function getMarginTypeTaxonomyId()
    {
        return $this->marginTypeTaxonomyId;
    }
}
