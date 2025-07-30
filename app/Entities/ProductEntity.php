<?php

namespace App\Entities;

use App\Product;

class ProductEntity extends Entity
{
    protected $model;

    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    public function getFrontendData(array $additions = []): array
    {
        $productableKeyAndId = $this->productableKeyAndId();
        $return = [
            'id' => $this->model->id,
            'name' => $this->model->name ? ['en' => $this->model->productable->name->name . ': ' . $this->model->name->description] : null,
            'name_description' => $this->model->name ? $this->getDescriptionWithTranslationsData($this->model->name) : null,
            'type' => $this->model->type->name,
            $productableKeyAndId['key'] => $productableKeyAndId['id']
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'prices':
                    $return['prices'] = $this->getPrices();
                    break;
                case 'fees':
                    $return['fees'] = FeeEntity::getCollection($this->model->fees, ['admin']);
                    break;
            }
        }

        return $return;
    }

    private function productableKeyAndId()
    {
        $classNameParts = explode('\\', $this->model->productable_type);
        return [
            'key' => $this->camelcaseToUnderscore(array_pop($classNameParts)) . '_id',
            'id' => $this->model->productable_id
        ];
    }

    private function camelcaseToUnderscore($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    private function getPrices(array $additions = ['admin'])
    {
        return PriceEntity::getCollection($this->model->prices()
            ->orderBy('extra', 'ASC')
            ->orderBy('age_range_id', 'DESC')
            ->orderBy('amount', 'ASC')
            ->get(), $additions);
    }

}
