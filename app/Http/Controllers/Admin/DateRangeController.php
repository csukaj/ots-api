<?php

namespace App\Http\Controllers\Admin;

use App\DateRange;
use App\Entities\DateRangeEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\DateRangeSetter;
use App\ModelMealPlan;
use App\PriceElement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @resource Admin/DateRangeController
 */
class DateRangeController extends ResourceController
{

    /**
     * index
     * Display a listing of DateRanges
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $dateRangeFilter = DateRange::forDateRangeable($request->input('date_rangeable_type'),
            $request->input('date_rangeable_id'));
        $types = Config::get('taxonomies.date_range_types');
        if ($request->has('type') && isset($types[$request->input('type')])) {
            $dateRangeFilter->where('type_taxonomy_id', $types[$request->input('type')]);
        }
        $dateRanges = $dateRangeFilter->orderBy('type_taxonomy_id')->orderBy('from_time')->get();
        return response()->json(['success' => true, 'data' => DateRangeEntity::getCollection($dateRanges)]);
    }

    /**
     * store
     * Store a newly created DateRange
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $dateRange = (new DateRangeSetter($request->all()))->set();
        return response()->json(['success' => true, 'data' => (new DateRangeEntity($dateRange))->getFrontendData()]);
    }

    /**
     * updateCollection
     * Bulk update multiple DateRanges
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function updateCollection(Request $request): JsonResponse
    {
        $dateRangesUpdated = [];
        foreach ($request->data as $requestItem) {
            $dateRangesUpdated[] = (new DateRangeSetter($requestItem))->set();
        }
        return response()->json(['success' => true, 'data' => DateRangeEntity::getCollection($dateRangesUpdated)]);
    }

    /**
     * show
     * Display the specified DateRange
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $dateRange = DateRange::findOrFail($id);
        return response()->json(['success' => true, 'data' => (new DateRangeEntity($dateRange))->getFrontendData()]);
    }

    /**
     * destroy
     * Remove the specified DateRange
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $dateRange = DateRange::findOrFail($id);
        ModelMealPlan::where('date_range_id', $dateRange->id)->delete();
        PriceElement::where('date_range_id', $dateRange->id)->delete();
        return response()->json([
            'success' => $dateRange->delete(),
            'data' => (new DateRangeEntity(DateRange::withTrashed()->findOrFail($id)))->getFrontendData()
        ]);
    }
}
