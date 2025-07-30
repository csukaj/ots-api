<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ShipGroupEntity;
use App\ShipGroup;

/**
 * @resource Admin/OrganizationPricesController
 */
class OrganizationGroupPricesController extends PricesController
{

    protected $entityAdditions = ['date_ranges', 'devices', 'prices', 'pricing', 'parent'];

    public function __construct()
    {
        $this->setClasses(ShipGroup::class, ShipGroupEntity::class);
    }

}
