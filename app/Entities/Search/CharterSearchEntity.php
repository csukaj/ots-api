<?php

namespace App\Entities\Search;

use App\Entities\ShipGroupEntity;
use App\Facades\Config;
use App\ShipGroup;
use Illuminate\Support\Facades\DB;

class CharterSearchEntity extends AbstractSearchEntity
{

    protected $parameters = [
        'interval' => null,
        'usages' => null,
        'booking_date' => null,
        'wedding_date' => null,
        'cart_summary' => null,
        'returning_client' => null
    ];
    protected $hasValidInterval = false;

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $shipGroups = [];
        $searchResult = $this->getShipGroupDataByParameters();
        $additions[] = 'info';
        $additions[] = 'ship_company';

        foreach ($searchResult as $searchResultItem) {
            $entity = (new ShipGroupEntity(ShipGroup::findOrFail($searchResultItem->id)))->getFrontendData($additions);
            $resultUsages = ($this->hasValidInterval) ? \json_decode($searchResultItem->result_usages, true) : [];
            $shipGroups[$searchResultItem->id] = [
                'info' => $entity,
                'results' => (isset($resultUsages['results'])) ? $resultUsages['results'] : []
            ];
            if (in_array('availability', $additions)) {
                $shipGroups[$searchResultItem->id]['availability'] =
                    (isset($resultUsages['availability'])) ? $resultUsages['availability'] : [];
            }
        }
        return $shipGroups;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function getShipGroupDataByParameters()
    {
        $parametersJSON = $this->buildParametersJSON();

        $query = DB::table('organization_groups AS og')->distinct();
        $query->join('organizations AS o', 'og.parent_id', '=', 'o.id');
        $query->where('og.type_taxonomy_id', Config::getOrFail('taxonomies.organization_group_types.ship_group.id'));
        if (!$this->showInactive) {
            $query->whereRaw('og.is_active');
        }
        $query->whereNull('og.deleted_at');
        $query->whereNull('o.deleted_at');
        $query->selectRaw("
                og.id,
                get_result_charters(og.parent_id, og.id, TEXT '{$parametersJSON}') AS result_usages
            ");

        return $query->get()->filter(function ($value, $key) {
            return !is_null($value->result_usages);
        })->values();
    }

}
