<?php

namespace App\Http\Controllers\Admin;

use App\AgeRange;
use App\Entities\AgeRangeEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\AgeRangeSetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

/**
 * @resource Admin/AgeRangeController
 */
class AgeRangeController extends ResourceController
{

    private static $additions = ['taxonomy'];

    /**
     * index
     * Display a listing of AgeRange & AgeRange names
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $ageRanges = AgeRange::forAgeRangeAble($request->input('age_rangeable_type'),
            $request->input('age_rangeable_id'))->orderBy('from_age', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => AgeRangeEntity::getCollection($ageRanges, self::$additions),
            'age_range_names' => TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.age_range'))->getChildren(),
                ['translations_with_plurals'])
        ]);
    }

    /**
     * store
     * Store a newly created AgeRange
     * @param  Request $request
     * @return JsonResponse
     * @throws UserException
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $ageRange = (new AgeRangeSetter($request->all()))->set();
        return response()->json([
            'success' => true,
            'data' => (new AgeRangeEntity($ageRange))->getFrontendData(self::$additions)
        ]);
    }

    /**
     * show
     * Display the specified AgeRange
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $ageRange = AgeRange::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => (new AgeRangeEntity($ageRange))->getFrontendData(self::$additions)
        ]);
    }

    /**
     * destroy
     * Remove the specified AgeRange
     * @param  int $id
     * @return JsonResponse
     * @throws UserException
     */
    public function destroy($id): JsonResponse
    {
        if (AgeRange::findOrFail($id)->name->name == 'adult') {
            throw new UserException('You cannot delete default (adult) age range.');
        }
        $count = AgeRange::destroy($id);
        return response()->json([
            'success' => (bool)$count,
            'data' => (new AgeRangeEntity(AgeRange::withTrashed()->findOrFail($id)))->getFrontendData(self::$additions)
        ]);
    }
}
