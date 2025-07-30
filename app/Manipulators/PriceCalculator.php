<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use Illuminate\Support\Facades\Config;

/**
 * Manipulator to calculate prices
 * instance after the supplied data passes validation
 */
class PriceCalculator {
    protected $netPrice;
    protected $rackPrice;
    protected $marginPercentage;
    protected $marginValue;
    
    public function __construct() {
        //
    }
    
    public function reset() {
        foreach (array_keys(get_object_vars($this)) as $key) {
            $this->$key = null;
        }
        return $this;
    }

    public function initWithNetPrice($netPrice, $margin, $marginMode) {
        $this->reset();
        $this->netPrice = $netPrice;
        $this->setMargin($margin, $marginMode);
        return $this;
    }
    
    public function initWithRackPrice($rackPrice, $margin, $marginMode) {
        $this->reset();
        $this->rackPrice = $rackPrice;
        $this->setMargin($margin, $marginMode);
        return $this;
    }
    
    public function getNetPrice() {
        if (!is_null($this->netPrice)) return $this->netPrice;
        
        if (!is_null($this->marginPercentage)) {
            return $this->rackPrice / $this->getMultiplier();
        } else if (!is_null($this->marginValue)) {
            return $this->rackPrice - $this->marginValue;
        }
        
        throw new UserException('Invalid or empty price calculator.');
    }
    
    public function getRackPrice() {
        if (!is_null($this->rackPrice)) return $this->rackPrice;
        
        if (!is_null($this->marginPercentage)) {
            return $this->netPrice * $this->getMultiplier();
        } else if (!is_null($this->marginValue)) {
            return $this->netPrice + $this->marginValue;
        }
        
        throw new UserException('Invalid or empty price calculator.');
    }
    
    public function getRoundedRackPrice($amount = 1) {
        return round($amount * $this->getRackPrice());
    }
    
    public function getMarginPercentage() {
        if (!is_null($this->marginPercentage)) return $this->marginPercentage;
        
        return round((100 * $this->getRackPrice() / $this->getNetPrice()) - 100, 2);
    }
    
    public function getMarginValue() {
        if (!is_null($this->marginValue)) return $this->marginValue;
        
        return $this->getRackPrice() - $this->getNetPrice();
    }
    
    protected function getMultiplier() {
        if (is_null($this->marginPercentage)) throw new UserException('Multiplier can be calculated from percentage only.');
        
        return (100 + $this->marginPercentage) / 100;
    }

    protected function setMargin($margin, $marginMode) {
        switch ($marginMode) {
            case Config::get('taxonomies.margin_types.percentage'):
                if($margin <= -100) {
                    throw new UserException('Invalid percentage value');
                }
                
                $this->marginPercentage = $margin;
                break;
            case Config::get('taxonomies.margin_types.value'):
                $this->marginValue = $margin;
                break;
            default:
                throw new UserException('Invalid margin mode.');
        }
    }
}