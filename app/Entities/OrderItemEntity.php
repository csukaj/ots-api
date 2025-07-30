<?php

namespace App\Entities;

use App\Accommodation;
use App\Cruise;
use App\OrderItem;
use App\ShipCompany;
use App\ShipGroup;
use App\UniqueProduct;

class OrderItemEntity extends Entity
{

    protected $model;
    private $manufacturedData = null;

    public function __construct(OrderItem $orderItem)
    {
        parent::__construct($orderItem);
        $this->manufacturedData = self::orderItemRelatedModelsFrontendDataFactory($this->model);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = $this->model->attributesToArray();

        $return['order_itemable'] = $this->manufacturedData['orderItemable'];
        $return['organization'] = $this->manufacturedData['organization'];
        $return['guests'] = $this->getGuests();
        $return['compulsory_fee'] = $this->model->compulsoryFee();
        $return['supplier'] = $this->manufacturedData['supplier'];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'parse_json':
                    $return = array_merge($return, $this->model->getJSON());
                    break;
            }
        }

        return $return;
    }

    private function getGuests()
    {
        $guests = [];
        foreach ($this->model->guests as $guest) {
            $guests[] = array_filter($guest->attributesToArray(), function ($k) {
                return in_array($k, ['order_item_id', 'guest_index', 'first_name', 'last_name']);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $guests;
    }

    static protected function orderItemRelatedModelsFrontendDataFactory(OrderItem $orderItem): array
    {
        $orderItemableEntity = null;
        $organizationData = ['name' => ['en' => null]];
        $supplierData = null;

        $orderItemable = $orderItem->orderItemable;

        if ($orderItem->productableType() && $orderItemable) {
            switch ($orderItem->productableType()) {
                case Accommodation::class:
                    $orderItemableEntity = new DeviceEntity($orderItemable);
                    $organization = Accommodation::find($orderItemable->deviceable_id);
                    if ($organization) {
                        $organizationData = (new AccommodationEntity($organization))
                            ->getFrontendData([
                                'supplier',
                                'contacts',
                                'people',
                                'location',
                                'admin_properties'
                            ]);
                        $supplierData = $organizationData['supplier'];
                    }
                    break;
                case Cruise::class:
                    $productableId = $orderItem->productableModel()->id;
                    $orderItemableEntity = new DeviceEntity($orderItemable);
                    $organization = ($orderItemable->deviceable->parentOrganization)
                    ? ShipCompany::find($orderItemable->deviceable->parentOrganization->id) : null;
                    $productable = Cruise::find($productableId);
                    if ($organization) {
                        $organizationData = (new ShipCompanyEntity($organization))
                            ->getFrontendData([
                                'supplier',
                                'contacts',
                                'people',
                                'location',
                                'properties'
                            ]);
                    }
                    $supplierData = (new SupplierEntity($productable->supplier))->getFrontendData();
                    break;
                case ShipGroup::class:
                    $productableId = $orderItem->productableModel()->id;
                    $orderItemableEntity = new ShipGroupEntity($orderItemable);
                    $organization = ShipCompany::find($orderItemable->parentOrganization->id);
                    $productable = ShipGroup::find($productableId);
                    if ($organization) {
                        $organizationData = (new ShipCompanyEntity($organization))
                            ->getFrontendData([
                                'supplier',
                                'contacts',
                                'people',
                                'location',
                                'properties'
                            ]);
                    }
                    $supplierData = (new SupplierEntity($productable->supplier))->getFrontendData();
                    break;
                case UniqueProduct::class:
                    $orderItemableEntity = new UniqueProductEntity($orderItemable);

                    $productableId = $orderItem->productableModel()->id;
                    $productable = UniqueProduct::find($productableId);

                    $supplier = $productable->supplier;
                    $supplierData = (new SupplierEntity($supplier))->getFrontendData();

                    break;
            }
        }

        return [
            'orderItemable' => $orderItemableEntity ? $orderItemableEntity->getFrontendData() : null,
            'organization' => $organizationData,
            'supplier'=> $supplierData
        ];
    }

}
