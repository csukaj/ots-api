<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * This trait is used for validate model data before saving
 * @required protected $rules validation array
 * @required protected $errorMessages for errors
 */
trait ModelValidatorTrait {

    /**
     * Save method - with before save logic
     * 
     * @param array $options
     * @return bool
     */
    public function save(array $options = []) {
        if (property_exists(get_class($this), 'rules')) {
            if ($this->validateRules()) {
                return parent::save($options);
            }
            return false;
        }

        return parent::save($options);
    }

    /**
     * Validation by rules
     * 
     * @return bool
     */
    public function validateRules(): bool {
        $this->errorMessages = null;
        $validator = ValidatorFacade::make($this->attributes, $this->rules);
            
        if (method_exists($this, 'afterValidation')) {
            $validator->after(function($validator) {
                $this->afterValidation($validator);
            });
        }
        
        if ($validator->fails()) {
            $this->errorMessages = $validator->messages();
            return false;
        }
        return true;
    }

    /**
     * Return model errors
     */
    public function getErrorMessages() {
        return $this->errorMessages;
    }

}
