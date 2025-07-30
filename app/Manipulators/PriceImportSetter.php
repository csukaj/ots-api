<?php

namespace App\Manipulators;

use App\Exceptions\UserException;

class PriceImportSetter
{

    /**
     * @param array $priceList
     * @return bool
     * @throws UserException
     */
    public static function savePriceElements(array $priceList = []): bool
    {
        if (empty($priceList)) {
            throw new UserException('Price list is empty.');
        }
        foreach ($priceList as $importedPriceElement) {
            $priceElementSetter = new PriceElementImportSetter($importedPriceElement);
            try {
                if (!$priceElementSetter->set()) {
                    throw new UserException('Price element saving is failed.');
                }
            } catch (\Throwable $e) {
                throw new UserException('Price element saving is failed._'. $e->getMessage());
            }
        }
        return true;
    }
}
