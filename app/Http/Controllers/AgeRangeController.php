<?php

namespace App\Http\Controllers;

use App\Facades\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * @resource AgeRangeController
 */
class AgeRangeController extends ResourceController
{

    /**
     * index
     * List all age range taxonomies
     * @return Response
     * @throws \Exception
     */
    public function index(): JsonResponse
    {
        $ageRangeTxs = Taxonomy::findOrFail(Config::getOrFail('taxonomies.age_range'))->getChildren();
        $ageRangesData = TaxonomyEntity::getCollection($ageRangeTxs, ['translations_with_plurals']);
        $data = [];
        foreach ($ageRangesData as $ageRangeData) {
            if (!empty($ageRangeData['translations_with_plurals'])) {
                $data[$ageRangeData['name']] = $ageRangeData['translations_with_plurals'];
            } else {
                $data[$ageRangeData['name']] = [
                    'en' => [
                        'singular' => $ageRangeData['name'],
                        'plural' => $ageRangeData['name']
                    ]
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

}
