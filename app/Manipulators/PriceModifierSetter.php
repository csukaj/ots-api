<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\OfferClassification;
use App\OfferMeta;
use App\PriceModifier;
use App\PriceModifierClassification;
use App\PriceModifierMeta;
use App\PriceModifierPeriod;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new PriceModifier
 * instance after the supplied data passes validation
 */
class PriceModifierSetter extends BaseSetter
{

    const CONNECTION_COLUMN = 'price_modifier_id';

    private $priceModifier = null;
    private $nameDescriptions;
    private $descriptionDescriptions;
    private $condition_properties;
    private $offer_properties;

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'date_ranges' => null,
        'modifier_type_taxonomy_id' => null,
        'condition_taxonomy_id' => null,
        'offer_taxonomy_id' => null,
        'is_active' => null,
        'is_annual' => null,
        'name_description_id' => null,
        'promo_code' => null
    ];
    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'id' => 'integer|nullable',
        'date_ranges' => 'required',
        'modifier_type' => 'required',
        'condition' => 'required',
        'offer' => 'required',
        'condition_properties' => 'required',
        'offer_properties' => 'required'
    ];
    protected $typeTaxonomy;
    protected $conditionTaxonomy;
    protected $offerTaxonomy;
    private $siblings = null;

    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        $this->typeTaxonomy = Taxonomy::getTaxonomy($attributes['modifier_type'],
            Config::getOrFail('taxonomies.price_modifier_type'));
        $this->attributes['modifier_type_taxonomy_id'] = $this->typeTaxonomy->id;

        $this->conditionTaxonomy = Taxonomy::getTaxonomyGrandChild($attributes['condition'],
            Config::getOrFail('taxonomies.price_modifier_application_level'));
        $this->attributes['condition_taxonomy_id'] = $this->conditionTaxonomy->id;

        $this->offerTaxonomy = Taxonomy::getTaxonomy($attributes['offer'],
            Config::get('taxonomies.price_modifier_offer'));
        $this->attributes['offer_taxonomy_id'] = $this->offerTaxonomy->id;


        $this->nameDescriptions = $attributes['name'];
        $this->descriptionDescriptions = $attributes['description'];
        $this->condition_properties = $attributes['condition_properties'];
        $this->offer_properties = ($this->attributes['id']) ? $attributes['offer_properties'] : $this->addDefaultOfferPropertiesTo($attributes['offer_properties']);
        $this->attributes['is_annual'] = !empty($attributes['is_annual']);
    }

    /**
     * @return PriceModifier
     * @throws \Exception
     */
    public function set(): PriceModifier
    {
        $nameDescriptionId = null;
        if ($this->attributes['id']) {
            $this->priceModifier = PriceModifier::find($this->attributes['id']);
            $nameDescriptionId = $this->priceModifier->name_description_id;
        }

        if (is_null($this->priceModifier)) {
            $this->priceModifier = new PriceModifier($this->attributes);
        } else {
            $this->priceModifier->modifier_type_taxonomy_id = $this->typeTaxonomy->id;
            if ($this->priceModifier->condition_taxonomy_id != $this->conditionTaxonomy->id) {
                $this->clearConditionClassifications();
                $this->clearConditionMetas();
                $this->priceModifier->condition_taxonomy_id = $this->conditionTaxonomy->id;
            }
            if ($this->priceModifier->offer_taxonomy_id != $this->offerTaxonomy->id) {
                $this->clearOfferClassifications();
                $this->clearOfferMetas();
                $this->priceModifier->offer_taxonomy_id = $this->offerTaxonomy->id;
            }
        }

        $nameDescription = (new DescriptionSetter($this->nameDescriptions,
            $this->priceModifier->name_description_id))->set();
        $this->priceModifier->name_description_id = $nameDescription->id;

        $descriptionDescription = (new DescriptionSetter($this->descriptionDescriptions,
            $this->priceModifier->description_description_id))->set();
        $this->priceModifier->description_description_id = $descriptionDescription->id;
        $this->priceModifier->promo_code = (!empty($this->attributes['promo_code'])) ? $this->attributes['promo_code'] : null;
        $this->priceModifier->is_active = !empty($this->attributes['is_active']);
        $this->priceModifier->is_annual = !empty($this->attributes['is_annual']);

        $this->priceModifier->save();

        $this->setDateRanges();

        $this->setConditionProperties();
        $this->setOfferProperties();

        if (!$this->priceModifier->priority) {
            $this->siblings = $this->priceModifier->findSiblingsInOrder(true);
            $this->setPriority($this->siblings->count(), false);
        }
        $this->updateModelPriceModifierPriorities();

        $this->priceModifier->touchModel();
        return $this->priceModifier;
    }

    public function setPriority(int $priority, $standalone = true): PriceModifier
    {
        if ($standalone) {
            $this->priceModifier = PriceModifier::findOrFail($this->attributes['id']);
        }
        $this->priceModifier->priority = $priority;
        $this->priceModifier->save();
        return $this->priceModifier;
    }

    protected function setConditionProperties()
    {
        $this->clearConditionClassifications();
        if (!empty($this->condition_properties['classifications'])) {
            $this->setConditionClassifications($this->condition_properties['classifications']);
        }
        $this->clearConditionMetas();
        if (!empty($this->condition_properties['metas'])) {
            $this->setConditionMetas($this->condition_properties['metas']);
        }
    }

    protected function setOfferProperties()
    {
        $this->clearOfferClassifications();
        if (!empty($this->offer_properties['classifications'])) {
            $this->setOfferClassifications($this->offer_properties['classifications']);
        }
        $this->clearOfferMetas();
        if (!empty($this->offer_properties['metas'])) {
            $this->setOfferMetas($this->offer_properties['metas']);
        }
    }

    protected function setConditionClassifications(array $classifications)
    {
        $parentTxName = $this->conditionTaxonomy->parent->name;
        $txConfig = Config::get('taxonomies.price_modifier_application_levels.' . $parentTxName . '.price_modifier_condition_types.' . $this->conditionTaxonomy->name . '.classification');
        (new PriceModifierClassification())->setClassifications(self::CONNECTION_COLUMN, $this->priceModifier->id,
            $txConfig, $classifications);
    }

    protected function setOfferClassifications(array $classifications)
    {
        (new OfferClassification())->setClassifications(
            self::CONNECTION_COLUMN, $this->priceModifier->id,
            Config::get('taxonomies.price_modifier_offers.' . $this->offerTaxonomy->name . '.classification'),
            $classifications
        );
    }

    protected function setConditionMetas(array $metas)
    {
        $parentTxName = $this->conditionTaxonomy->parent->name;
        $txConfig = Config::get('taxonomies.price_modifier_application_levels.' . $parentTxName . '.price_modifier_condition_types.' . $this->conditionTaxonomy->name . '.meta');
        (new PriceModifierMeta())->setMetas(self::CONNECTION_COLUMN, $this->priceModifier->id, $txConfig, $metas);
    }

    protected function setOfferMetas(array $metas)
    {
        (new OfferMeta())->setMetas(
            self::CONNECTION_COLUMN, $this->priceModifier->id,
            Config::get('taxonomies.price_modifier_offers.' . $this->offerTaxonomy->name . '.meta'), $metas
        );
    }

    protected function clearConditionClassifications()
    {
        (new PriceModifierClassification())->clearClassifications(self::CONNECTION_COLUMN, $this->priceModifier->id);
    }

    protected function clearOfferClassifications()
    {
        (new OfferClassification())->clearClassifications(self::CONNECTION_COLUMN, $this->priceModifier->id);
    }

    protected function clearConditionMetas()
    {
        (new PriceModifierMeta())->clearMetas(self::CONNECTION_COLUMN, $this->priceModifier->id);
    }

    protected function clearOfferMetas()
    {
        (new OfferMeta())->clearMetas(self::CONNECTION_COLUMN, $this->priceModifier->id);
    }

    /**
     * @throws \Throwable
     */
    protected function setDateRanges()
    {
        PriceModifierPeriod::withTrashed()
            ->where('price_modifier_id', $this->priceModifier->id)
            ->delete();

        foreach ($this->attributes['date_ranges'] as $usedDetaRange) {
            PriceModifierPeriod::createOrRestore([
                'price_modifier_id' => $this->priceModifier->id,
                'date_range_id' => $usedDetaRange['id']
            ]);
        }
    }

    public function updateModelPriceModifierPriorities()
    {

        if (is_null($this->siblings)) {
            $this->siblings = $this->priceModifier->findSiblingsInOrder(true);
        }

        foreach (PriceModifier::sortbyPriority($this->siblings) as $idx => $siblingPriceModifier) {
            $siblingPriceModifier->priority = $idx + 1;
            $siblingPriceModifier->save();
        }
    }

    private function addDefaultOfferPropertiesTo(array $attributes): array
    {
        $defaults = [
            'free_nights' => [
                'classifications' => [
                    ['isset' => true, 'name' => "classification", 'value' => "use_last_consecutive_night"]
                ]
            ]
        ];
        if (isset($defaults[$this->offerTaxonomy->name])) {
            $attributes = array_merge_recursive($attributes, $defaults[$this->offerTaxonomy->name]);
        }
        return $attributes;
    }
}
