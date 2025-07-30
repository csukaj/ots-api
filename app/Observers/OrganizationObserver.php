<?php

namespace App\Observers;

use App\Organization;
use App\Entities\Search\AccommodationSearchEntity;
use App\Facades\Config;

class OrganizationObserver
{
    /**
     * Listen to the Accommodation created event.
     *
     * @param Organization $organization
     * @return void
     * @throws \Exception
     */
    public function created(Organization $organization)
    {
        $this->refreshInCache($organization);
    }

    /**
     * Listen to the Accommodation updated event.
     *
     * @param Organization $organization
     * @return void
     * @throws \Exception
     */
    public function updated(Organization $organization)
    {
        $this->refreshInCache($organization);
    }

    /**
     * Listen to the Accommodation saved event.
     *
     * @param Organization $organization
     * @return void
     * @throws \Exception
     */
    public function saved(Organization $organization)
    {
        $this->refreshInCache($organization);
    }

    /**
     * @param Organization $organization
     * @throws \Exception
     */
    private function refreshInCache(Organization $organization)
    {
        if($organization->type_taxonomy_id == Config::getOrFail('taxonomies.organization_types.accommodation.id')) {
            (new AccommodationSearchEntity())->deleteCache($organization->id);
        }
    }
}
