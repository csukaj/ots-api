<?php

namespace App\Entities;

use App\Device;
use App\DeviceClassification;
use App\DeviceMeta;
use App\Facades\Config;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylersmedia\Entities\GalleryEntity;

class DeviceEntity extends Entity
{
    const MODEL_TYPE = 'device';
    const CONNECTION_COLUMN = 'device_id';

    protected $device;
    protected $products;
    protected $fromDate;
    protected $toDate;
    protected $usageJson;
    protected $orderItemableIndexes;
    protected $productType;

    public function __construct(
        Device $device,
        $fromDate = null,
        $toDate = null,
        $usageJson = '',
        array $orderItemableIndexes = null,
        string $productType = null
    )
    {
        parent::__construct();

        $this->device = $device;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->usageJson = $usageJson;
        $this->orderItemableIndexes = $orderItemableIndexes;
        $this->productType = $productType;
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->device->id,
            'deviceable_type' => $this->device->deviceable_type,
            'deviceable_id' => $this->device->deviceable_id,
            'name' => $this->getTaxonomyTranslation($this->device->name),
            'type' => $this->device->type->name,
            'metas' => [],
            'classifications' => []
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'amount':
                    $return['amount'] = $this->device->amount;
                    break;

                case 'usages':
                    $return['usages'] = DeviceUsageEntity::getCollection($this->device->usages, ['admin']);
                    break;

                case 'prices':
                    if (!is_null($this->productType)) {
                        $products = $this->device->products()
                            ->where('type_taxonomy_id', '=',
                                Config::getOrFail("taxonomies.product_types.{$this->productType}"))
                            ->with('name')
                            ->orderBy('id')
                            ->get();
                    } else {
                        $products = $this->device->products()
                            ->with(['name', 'prices'])
                            ->orderBy('id')
                            ->get();
                    }
                    $return['products'] = ProductEntity::getCollection($products, ['prices']);
                    break;

                case 'margin':
                    $return['margin_value'] = $this->device->margin_value;
                    $return['margin_type'] = $this->device->margin_type_taxonomy_id ? $this->device->marginType->name : null;
                    break;

                case 'descriptions':
                    $return['descriptions'] = $this->getEntityDescriptionsData($this->device->id,
                        Config::get('taxonomies.device_description'));
                    break;

                case 'properties':
                    $return['metas'] = $this->getMeta(['frontend']);
                    $return['classifications'] = $this->getClassifications(['frontend']);
                    break;

                case 'admin_properties':
                    $return['metas'] = $this->getMeta(['admin']);
                    $return['classifications'] = $this->getClassifications(['admin']);
                    break;

                case 'images':
                    $return['images'] = $this->getImages();
                    break;

                case 'deviceable':
                    $nameParts = explode('\\', $this->device->deviceable_type);
                    $parentEntityClass = "{$nameParts[0]}\\Entities\\{$nameParts[1]}Entity";
                    $return['deviceable'] = (new $parentEntityClass($this->device->deviceable))->getFrontendData(['parent']);
                    break;

                case 'galleries':
                    $return['galleries'] = GalleryEntity::getCollection($this->device->galleries()->with([
                        'name',
                        'role'
                    ])->get());
                    break;

                case 'availability':
                    $return['availability'] = (!empty($this->fromDate) && !empty($this->toDate))
                        ? (new AvailabilityEntity(Device::class, $this->device->id))->get($this->fromDate,
                            $this->toDate)
                        : [];
                    break;
                case 'availability_update':
                    $return['availability_update'] = $this->device->availabilities->max('updated_at')->format('Y-m-d H:i:s');
                    break;
            }
        }

        return $return;
    }

    static public function getCollectionWithParams($models, array $additions = [], $fromDate = null, $toDate = null): array
    {
        $return = [];
        if (!empty($models)) {
            foreach ($models as $model) {
                $return[] = (new static($model, $fromDate, $toDate))->getFrontendData($additions);
            }
        }
        return $return;
    }

    protected function getMeta($additions = [])
    {
        return DeviceMeta::getListableMetaEntitiesForModel(self::CONNECTION_COLUMN, $this->device->id, $additions);
    }

    protected function getClassifications($additions = [])
    {
        $models = (new DeviceClassification())
            ->where(self::CONNECTION_COLUMN, $this->device->id)
            ->listable()
            ->forParent(null)
            ->with(['classificationTaxonomy', 'valueTaxonomy', 'additionalDescription'])
            ->orderBy('priority')
            ->get();

        return DeviceClassificationEntity::getCollection($models, $additions);
    }

    public function getImages()
    {
        $galleryIds = $this->device->galleries()->get()->pluck('id')->toArray();
        return FileEntity::getCollection(File::getFromGalleries($galleryIds));
    }
}
