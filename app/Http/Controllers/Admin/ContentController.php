<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\Entities\ContentEntity;
use App\Http\Controllers\ResourceController;
use App\Manipulators\ContentSetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * @resource Admin/ContentController
 */
class ContentController extends ResourceController
{

    /**
     * index
     * List all contents
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->modelList(Content::all());
    }

    /**
     * pages
     * List all pages
     * @return JsonResponse
     */
    public function pages(): JsonResponse
    {
        return $this->modelList(Content::page()->get());
    }

    /**
     * posts
     * List all posts
     * @return JsonResponse
     */
    public function posts(): JsonResponse
    {
        return $this->modelList(Content::post()->get());
    }

    /**
     * postsOfCategory
     * List all posts of a category
     * @param $id
     * @return JsonResponse
     */
    public function postsOfCategory($id): JsonResponse
    {
        return $this->modelList(Content::post()->ofCategory($id)->get());
    }

    /**
     * modelList
     * Generate response structure
     * @param Content $models
     * @return JsonResponse
     */
    private function modelList(Collection $models): JsonResponse
    {
        $categories = Taxonomy::find(Config::get('taxonomies.content_category'))->getChildren();
        return response()->json([
            'success' => true,
            'data' => ContentEntity::getCollection($models),
            'categories' => $categories->pluck('name')//TaxonomyEntity::getCollection($categories)
        ]);
    }

    /**
     * store
     * Store a newly created content
     * @param  Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function store(Request $request): JsonResponse
    {
        $author = Auth::user()->name;
        $requestArray = $request->toArray();
        $requestArray['author'] = $author;
        $content = (new ContentSetter($requestArray))->set();
        return response()->json(['success' => true, 'data' => (new ContentEntity($content))->getFrontendData()]);
    }

    /**
     * show
     * Display the content by ID
     * @param  int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new ContentEntity(Content::findOrFail($id)))->getFrontendData()
        ]);
    }

    /**
     * update
     * Update the specified content
     * @param  Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $author = Auth::user()->name;
        $requestArray = $request->toArray();
        $requestArray['id'] = $id;
        $requestArray['author'] = $author;
        $content = (new ContentSetter($requestArray))->set();
        return response()->json(['success' => true, 'data' => (new ContentEntity($content))->getFrontendData()]);
    }

    /**
     * destroy
     * Remove the specified content
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $content = Content::findOrFail($id);
        return response()->json(['success' => $content->delete(), 'data' => []]);
    }

}
