<?php
namespace App\Entities;

use App\Facades\Config;
use App\OrganizationClassification;
use App\Ship;

class ShipEntity extends OrganizationEntity
{

    public function __construct(Ship $ship, string $fromDate = null, string $toDate = null, string $productType = null)
    {
        parent::__construct($ship, $fromDate, $toDate, $productType);
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

                case 'admin_properties':
                    $return['properties'] = $this->getAdminProperties();
                    break;

                case 'parent':
                    $return['parent'] = $this->organization->shipGroup ? (new ShipGroupEntity($this->organization->shipGroup))->getFrontendData(['parent']) : null;
                    break;

                case 'galleries':
                    $return['galleries'] = $this->getGalleries();
                    break;
            }
        }

        return $return;
    }

    /**
     *
     * @return array
     * @throws \Exception
     */
    private function getAdminProperties(): array
    {
        $models = [];
        $settings = [
        ];
        foreach ($settings as $settingIdPath) {
            $model = OrganizationClassification::getClassification(
                self::CONNECTION_COLUMN, $this->organization->id, Config::getOrFail($settingIdPath)
            );
            if (!empty($model)) {
                $models[] = $model;
            }
        }
        return OrganizationClassificationEntity::getCollection($models, ['frontend']);
    }
}
