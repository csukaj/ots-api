<?php
namespace App\Http\Controllers\Admin;

use App\Cruise;
use App\Entities\CruiseEntity;

/**
 * @resource Admin/OrganizationPricesController
 */
class CruisePricesController extends PricesController
{

    protected $entityAdditions = ['date_ranges', 'devices', 'prices', 'pricing'];

    public function __construct()
    {
        $this->setClasses(Cruise::class, CruiseEntity::class);
    }
}
