<?php
namespace App\Http\Controllers\Admin;

use App\Entities\OrganizationEntity;
use App\Organization;

/**
 * @resource Admin/OrganizationPricesController
 */
class OrganizationPricesController extends PricesController
{

    protected $entityAdditions = ['date_ranges', 'price_modifier_date_ranges', 'devices', 'prices', 'device_margin', 'pricing', 'device_amount'];
    
    public function __construct()
    {
        $this->setClasses(Organization::class, OrganizationEntity::class);
    }

}
