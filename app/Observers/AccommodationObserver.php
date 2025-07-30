<?php

namespace App\Observers;

use App\Accommodation;
use App\Entities\Search\AccommodationSearchEntity;

class AccommodationObserver
{
    /**
     * Listen to the Accommodation created event.
     *
     * @param  Accommodation $accommodation
     * @return void
     */
    public function created(Accommodation $accommodation)
    {
        $this->refreshInCache($accommodation);
    }

    /**
     * Listen to the Accommodation updated event.
     *
     * @param  Accommodation $accommodation
     * @return void
     */
    public function updated(Accommodation $accommodation)
    {
        $this->refreshInCache($accommodation);
    }

    /**
     * Listen to the Accommodation saved event.
     *
     * @param  Accommodation $accommodation
     * @return void
     */
    public function saved(Accommodation $accommodation)
    {
        $this->refreshInCache($accommodation);
    }

    /**
     * @param Accommodation $accommodation
     */
    private function refreshInCache(Accommodation $accommodation)
    {
        (new AccommodationSearchEntity())->deleteCache($accommodation->id);
    }
}
