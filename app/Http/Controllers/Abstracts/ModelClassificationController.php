<?php

namespace App\Http\Controllers\Abstracts;

use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @resource Admin/ModelClassificationController
 */
abstract class ModelClassificationController extends ResourceController
{

    protected $classificationClass;
    protected $classificationEntityClass;
    protected $propertySetterClass;
    protected $foreignKey;

    /**
     * index
     * Display a listing of ModelClassifications
     * @param Request|null $request
     * @return JsonResponse
     */
    public function index(Request $request = null): JsonResponse
    {
        $modelCls = $this->classificationClass
            ::where($this->foreignKey, $request->input($this->foreignKey))
            ->forParent()->orderBy('priority')->get();

        return response()->json([
            'success' => true,
            'data' => $this->classificationEntityClass::getCollection($modelCls, ['admin']),
            'options' => $this->classificationEntityClass::getOptions()
        ]);
    }

    /**
     * store
     * Store a newly created ModelClassification
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $classificationClass = $this->classificationClass;
        $classificationEntityClass = $this->classificationEntityClass;
        $propertySetterClass = $this->propertySetterClass;
        $modelCl = (new $propertySetterClass())->setClassification(new $classificationClass(), $request->toArray());

        return response()->json([
            'success' => true,
            'data' => (new $classificationEntityClass($modelCl))->getFrontendData(['admin']),
            'options' => $this->classificationEntityClass::getOptions()
        ]);
    }

    /**
     * show
     * Display the specified ModelClassification
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $classificationEntityClass = $this->classificationEntityClass;
        $modelCl = $this->classificationClass::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => (new $classificationEntityClass($modelCl))->getFrontendData(['admin']),
            'options' => $this->classificationEntityClass::getOptions()
        ]);
    }

    /**
     * update
     * Update the specified ModelClassification
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $classificationClass = $this->classificationClass;
        $classificationEntityClass = $this->classificationEntityClass;
        $propertySetterClass = $this->propertySetterClass;
        $modelCl = (new $propertySetterClass())->setClassification(
            $classificationClass::findOrFail($id),
            $request->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => (new $classificationEntityClass($modelCl))->getFrontendData(['admin']),
            'options' => $this->classificationEntityClass::getOptions()
        ]);
    }

    /**
     * destroy
     * Remove the specified ModelClassification
     * @param  int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $classificationClass = $this->classificationClass;
        $classificationEntityClass = $this->classificationEntityClass;

        return response()->json([
            'success' => (bool)$classificationClass::findOrFail($id)->delete(),
            'data' => (new $classificationEntityClass(
                $classificationClass::withTrashed()->findOrFail($id)
            ))->getFrontendData(['admin']),
            'options' => $this->classificationEntityClass::getOptions()
        ]);
    }

}
