<?php

namespace App\Entities;

use App\ShipCompany;

class ShipCompanyEntity extends OrganizationEntity
{

    public function __construct(ShipCompany $shipCompany, string $fromDate = null, string $toDate = null, string $productType = null)
    {
        parent::__construct($shipCompany, $fromDate, $toDate, $productType);
    }

    /**
     *
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = parent::getFrontendData($additions);


        foreach ($additions as $addition) {
            switch ($addition) {

                case 'galleries':
                    $return['galleries'] = $this->getGalleries();
                    break;
            }
        }

        return $return;
    }

}
