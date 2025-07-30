<?php
namespace App\Console\Commands\TestModelSeeder;

use App\AgeRange;
use App\DeviceDescription;
use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylersmedia\Entities\GalleryItem;
use Modules\Stylersmedia\Manipulators\FileSetter;
use Modules\Stylersmedia\Manipulators\GallerySetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * Class to seed a accommodation test data
 */
class TestModelSeeder
{

    /**
     * Sets a gallery item
     *
     * @param array $item Item data
     * @param Gallery $gallery Gallery object to link to
     */
    public static function setGalleryItem(array $item, Gallery $gallery)
    {
        $symfonyFile = new SymfonyFile('docs/sample_images/' . $item['source_url']);
        $file = (new FileSetter($item))->setBySymfonyFile($symfonyFile);

        $galleryItem = new GalleryItem();
        $galleryItem->gallery_id = $gallery->id;
        $galleryItem->file_id = $file->id;
        $galleryItem->is_highlighted = !empty($item['highlighted']);
        $galleryItem->priority = !empty($item['priority']) ? $item['priority'] : null;
        $galleryItem->saveOrFail();
    }

    /**
     * Set a gallery from provided data with items
     *
     * @param string $modelType
     * @param Model $model
     * @param array $data
     * @throws \App\Exceptions\UserException
     */
    public static function setGallery(string $modelType, Model $model, array $data)
    {
        $gallery = (new GallerySetter([
            'galleryable_type' => $modelType,
            'galleryable_id' => $model->id,
            'role_taxonomy_id' => Config::getOrFail('taxonomies.gallery_roles.frontend_gallery')
            ]))->set();

        foreach ($data as $item) {
            self::setGalleryItem($item, $gallery);
        }
    }

    /**
     * Set Organization's Age Ranges with adult age range existence check
     *
     * @param int $modelId
     * @param array $ageRanges
     * @throws Exception
     */
    static public function setAgeRanges(string $modelType, int $modelId, array $ageRanges)
    {
        if (!self::hasAdultAgeRangeData($ageRanges)) {
            throw new Exception('Adult age range missing!');
        }
        foreach ($ageRanges as $ageRangeData) {
            $ageRangeTx = Taxonomy::getOrCreateTaxonomy($ageRangeData['name_taxonomy'], Config::getOrFail('taxonomies.age_range'));
            $ageRange = new AgeRange([
                'from_age' => $ageRangeData['from_age'],
                'to_age' => $ageRangeData['to_age'],
                'age_rangeable_type' => $modelType,
                'age_rangeable_id' => $modelId,
                'name_taxonomy_id' => $ageRangeTx->id,
                'banned' => !empty($ageRangeData['banned']),
                'free' => !empty($ageRangeData['free'])
            ]);
            $ageRange->save();
        }
    }

    /**
     * Checks if found adult age range in ranges
     *
     * @param array $ageRanges
     * @return bool
     */
    static public function hasAdultAgeRangeData(array $ageRanges): bool
    {
        foreach ($ageRanges as $ageRangeItem) {
            if ($ageRangeItem['name_taxonomy'] && $ageRangeItem['name_taxonomy'] == 'adult') {
                return true;
            }
        }
        return false;
    }

    /**
     * set Device Descriptions
     *
     * @param int $deviceId
     * @param array $deviceData
     */
    static public function setModelDescriptions(string $descriptionType, int $modelId, string $columnName, string $taxonomyName, array $data)
    {
        if (!empty($data['short_description'])) {
            (new $descriptionType())->setDescription(
                $columnName, $modelId, Config::getOrFail("taxonomies.{$taxonomyName}.short_description"), $data['short_description']
            );
        }
        if (!empty($data['long_description'])) {
            (new $descriptionType())->setDescription(
                $columnName, $modelId, Config::getOrFail("taxonomies.{$taxonomyName}.long_description"), $data['long_description']
            );
        }
    }
}
