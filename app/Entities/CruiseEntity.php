<?php

namespace App\Entities;

use App\Cruise;
use App\CruiseClassification;
use App\CruiseMeta;
use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class CruiseEntity extends Entity
{

    const MODEL_TYPE = 'cruise';
    const CONNECTION_COLUMN = 'cruise_id';

    protected $model;

    public function __construct(Cruise $cruise)
    {
        parent::__construct($cruise);
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
            'name' => (new DescriptionEntity($this->model->name))->getFrontendData(),
            'is_active' => $this->model->is_active,
            'location' => (new LocationEntity($this->model->location))->getFrontendData(['admin']),
            'descriptions' => $this->getEntityDescriptionsData($this->model->id,
                Config::get('taxonomies.cruise_description')),
            'ship_company_id' => $this->model->ship_company_id,
            'ship_group_id' => $this->model->ship_group_id,
            'itinerary_id' => $this->model->itinerary_id,
            'pricing_logic' => ($this->model->pricingLogic) ? $this->model->pricingLogic->name : null,
            'margin_type' => ($this->model->marginType) ? $this->model->marginType->name : null
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'info':
                    $return['properties'] = $this->getProperties();
                    $return['cabin_count'] = $this->model->shipGroup->getCabinCount();
                    $return['age_ranges'] = AgeRangeEntity::getCollection($this->model->ageRanges()->with('name')->get(),
                        ['frontend']);
                    $return['itinerary'] = (new ProgramEntity($this->model->itinerary))->getFrontendData([
                        'frontend',
                        'activities',
                        'galleries'
                    ]);
                    break;

                case 'ship_company':
                    $return['ship_company'] = $this->model->ship_company_id ?
                        (new ShipCompanyEntity($this->model->shipCompany))->getFrontendData([
                            'descriptions',
                            'galleries'
                        ]) :
                        null;
                    break;

                case 'ship_group':
                    $ship_group_additions = in_array('admin_ship_group_devices', $additions) ? [
                        'devices',
                        'admin_properties'
                    ] : ['devices'];
                    $return['ship_group'] = $this->model->ship_group_id ?
                        (new ShipGroupEntity($this->model->shipGroup))->getFrontendData($ship_group_additions) :
                        null;
                    break;

                case 'itinerary':
                    $return['itinerary'] = $this->model->itinerary_id ?
                        (new ProgramEntity($this->model->itinerary))->getFrontendData() :
                        null;
                    break;

                case 'devices':
                    $devices = [];
                    foreach ($this->model->cruiseDevices()->get() as $cruiseDevice) {
                        $device = (new DeviceEntity($cruiseDevice->device))->getFrontendData(['margin', 'amount']);
                        if (in_array('prices', $additions)) {
                            $products = $cruiseDevice->products()->with(['name', 'type', 'prices'])->get();
                            $device['products'] = ProductEntity::getCollection($products, ['prices']);
                        }
                        $devices[] = $device;
                    }
                    $return['devices'] = ['cabin' => $devices];
                    break;

                case 'date_ranges':
                    $return['date_ranges'] = [
                        'open' => DateRangeEntity::getCollection($this->model->dateRanges()->open()->with([
                            'name',
                            'type',
                            'marginType'
                        ])->orderBy('from_time')->get()),
                        'closed' => DateRangeEntity::getCollection($this->model->dateRanges()->closed()->with([
                            'name',
                            'type',
                            'marginType'
                        ])->orderBy('from_time')->get()),
                        'price_modifier' => DateRangeEntity::getCollection($this->model->dateRanges()->priceModifier()->with([
                            'name',
                            'type',
                            'marginType'
                        ])->orderBy('from_time')->get())
                    ];
                    break;

                case 'supplier':
                    $return['supplier'] = $this->model->supplier ? (new SupplierEntity($this->model->supplier))->getFrontendData() : null;
                    break;

            }
        }

        return $return;
    }

    protected function getProperties(): array
    {
        return array_merge(
            CruiseMeta::getListableMetaEntitiesForModel(self::CONNECTION_COLUMN, $this->model->id),
            $this->getClassifications()
        );
    }

    protected function getClassifications(): array
    {
        $models = CruiseClassification
            ::where(self::CONNECTION_COLUMN, $this->model->id)
            ->listable()
            ->forParent(null)
            ->with('additionalDescription')
            ->orderBy('priority')
            ->get();

        return CruiseClassificationEntity::getCollection($models, ['frontend']);
    }
}
