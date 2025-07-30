<?php

namespace Modules\Stylerstaxonomy\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylerstaxonomy\Entities\Description;
use Nwidart\Modules\Routing\Controller;

/**
 * @resource Stylerstaxonomy/DescriptionController
 */
class DescriptionController extends Controller
{

    /**
     * Store a new Description
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $description = new Description($request->all());
        $success = $description->save();
        return response()->json(['success' => $success, 'data' => $description->attributesToArray()]);
    }

    /**
     * Update a specified Description
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $description = Description::findOrFail($id);
        $description->fill($request->except(['id']));
        $success = $description->save();
        return response()->json(['success' => $success, 'data' => $description->attributesToArray()]);
    }

    /**
     * Display a specified Description
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $description = Description::findOrFail($id);
        return response()->json(['success' => true, 'data' => $description->attributesToArray()]);
    }

    /**
     * Remove a specified Description
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $count = Description::destroy($id);
        return response()->json(['success' => (bool)$count, 'data' => Description::withTrashed()->findOrFail($id)]);
    }

    /**
     * List all Descriptions
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Description::all()]);
    }

}
