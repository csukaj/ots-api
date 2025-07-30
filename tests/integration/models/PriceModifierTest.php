<?php
namespace Tests\Integration\Models;

use App\DateRange;
use App\PriceModifier;
use App\Facades\Config;
use App\Organization;
use Tests\TestCase;

class PriceModifierTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_NEVER;

    /**
     * @test
     * @todo
     */
    public function it_can_getPricemodifiableModel()
    {
        $priceModifier = PriceModifier::forModel(Organization::class, 1)->first();
        $priceModifierableModel = $priceModifier->getPricemodifiableModel();
        $this->assertEquals(1, $priceModifierableModel->id);
        $this->assertEquals(Organization::class, get_class($priceModifierableModel));
    }

    /**
     * @test
     * @todo
     */
    public function it_can_findSiblingsInOrder()
    {
        $priceModifiers = PriceModifier::forModel(Organization::class, 1)->get()->sort(function (PriceModifier $a, PriceModifier $b) {
            $offerPriority = [
                Config::get('taxonomies.price_modifier_offers.price_row.id') => 0,
                Config::get('taxonomies.price_modifier_offers.free_nights.id') => 1
            ];
            $priority = intval($a->priority) - intval($b->priority);
            if (!isset($offerPriority[$a->offer_taxonomy_id]) && !isset($offerPriority[$b->offer_taxonomy_id])) {
                return $priority;
            } elseif (!isset($offerPriority[$a->offer_taxonomy_id])) {
                return 1;
            } elseif (!isset($offerPriority[$b->offer_taxonomy_id])) {
                return -1;
            }
            return ($offerPriority[$a->offer_taxonomy_id] - $offerPriority[$b->offer_taxonomy_id]) ?: $priority;
        });
        $actual = $priceModifiers[0]->findSiblingsInOrder(true)->pluck('id')->all();
        $expected = $priceModifiers->pluck('id')->values()->all();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @todo
     */
    public function it_can_sortbyPriority()
    {
        $priceModifierIds = PriceModifier::getModelPriceModifierIds(Organization::class, 1);
        $priceModifiers = PriceModifier::find($priceModifierIds);
        $sorted = PriceModifier::sortbyPriority($priceModifiers);
        $oldPriority = -1;
        $hasError = false;
        foreach ($sorted as $priceModifier) {
            if ($priceModifier->priority < $oldPriority) {
                $hasError = true;
            }
            $oldPriority = $priceModifier->priority;
        }
        $this->assertFalse($hasError);
    }

    /**
     * @test
     * @todo
     */
    public function it_can_getModelDiscountIds()
    {
        $organizationId = 1;
        $dateRanges = DateRange::priceModifier()->forDateRangeable(Organization::class, $organizationId)->get();
        $priceModifierIds = [];
        foreach ($dateRanges as $dateRange) {
            foreach ($dateRange->priceModifierPeriods as $period) {
                $priceModifierIds[] = $period->price_modifier_id;
            }
        }
        $expected = array_merge(array_unique($priceModifierIds));
        $actual = PriceModifier::getModelPriceModifierIds(Organization::class, $organizationId);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_findByName()
    {
        $found = 0;
        $name = 'Wedding Anniversary';
        $organizationId = 1;

        $orgDiscountIds = PriceModifier::getModelPriceModifierIds(Organization::class, $organizationId);

        $matches = PriceModifier
            ::select('price_modifiers.*')
            ->join('descriptions', 'price_modifiers.name_description_id', '=', 'descriptions.id')
            ->whereIn('price_modifiers.id', $orgDiscountIds)
            ->where('descriptions.description', $name)
            ->get()->pluck('id');
        foreach ($matches as $match) {
            if (in_array($match, $orgDiscountIds)) {
                $found++;
                $expectedId = $match;
            }
        }
        $this->assertGreaterThan(0, count($matches));
        $this->assertGreaterThan(0, $found);
        $actual = PriceModifier::findByName($name, Organization::class, $organizationId);
        $this->assertEquals($expectedId, $actual->id);
    }
}
