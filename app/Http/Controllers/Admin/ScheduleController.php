<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ScheduleEntity;
use App\Facades\Config;
use App\Http\Controllers\ResourceController;
use App\Manipulators\ScheduleSetter;
use App\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ScheduleController extends ResourceController
{

    private $entityAdditions = [];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $schedules = Schedule::forCruise($request->input('cruise_id'))->get();
        return response()->json([
            'success' => true,
            'data' => ScheduleEntity::getCollection($schedules, $this->entityAdditions),
            'frequencies' => $this->getFrequencyTaxonomies()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $schedule = (new ScheduleSetter($request->all()))->set();

        return response()->json([
            'success' => true,
            'data' => (new ScheduleEntity($schedule))->getFrontendData($this->entityAdditions),
            'frequencies' => $this->getFrequencyTaxonomies()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new ScheduleEntity(Schedule::findOrFail($id)))->getFrontendData($this->entityAdditions),
            'frequencies' => $this->getFrequencyTaxonomies()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $requestArray = $request->all();
        $requestArray['id'] = $id;

        $schedule = (new ScheduleSetter($requestArray))->set();

        return response()->json([
            'success' => true,
            'data' => (new ScheduleEntity($schedule))->getFrontendData($this->entityAdditions),
            'frequencies' => $this->getFrequencyTaxonomies()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $Schedule = Schedule::findOrFail($id);
        return response()->json([
            'success' => $Schedule->delete(),
            'data' => (new ScheduleEntity(Schedule::onlyTrashed()->findOrFail($id)))->getFrontendData($this->entityAdditions),
            'frequencies' => $this->getFrequencyTaxonomies()
        ]);
    }

    /**
     * @return array
     */
    private function getFrequencyTaxonomies()
    {
        return TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.schedule_frequency'))->getChildren(),
            ['translations_with_plurals']);
    }
}
