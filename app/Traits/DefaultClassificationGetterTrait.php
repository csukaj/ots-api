<?php
namespace App\Traits;

use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

trait DefaultClassificationGetterTrait
{
    
    private $defaultClassifications;
    private $categoryTxPath;

    protected function getTaxonomiesForDefaults(): array
    {
        $defaults = [];
        foreach ($this->defaultClassifications as $categoryName => $itemNames) {
            $categoryId = Config::getOrFail($this->categoryTxPath)[$categoryName]['id'];
            $categoryData = (new TaxonomyEntity(Taxonomy::findOrFail($categoryId)))->getFrontendData();
            $categoryData['descendants'] = [];
            foreach ($itemNames as $itemName) {
                $itemId = Config::getOrFail($this->categoryTxPath)[$categoryName]['items'][$itemName]['id'];
                $categoryData['descendants'][] = (new TaxonomyEntity(Taxonomy::findOrFail($itemId)))->getFrontendData(['descendants']);
            }
            $defaults[] = $categoryData;
        }
        return $defaults;
    }
}
