<?php

namespace App\Http\Controllers;

use App\Content;
use App\Entities\ContentEntity;
use App\Exceptions\UserException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * @resource ContentController
 */
class ContentController extends ResourceController
{

    /**
     * pages
     * List all published pages
     * @return Response
     */
    public function pages(): JsonResponse
    {
        return $this->modelList(Content::published()->page()->get());
    }

    /**
     * posts
     * List all published posts
     * @return Response
     */
    public function posts(): JsonResponse
    {
        return $this->modelList(Content::published()->post()->get());
    }

    /**
     * postsOfCategory
     * List all published posts of a category
     * @return Response
     */
    public function postsOfCategory($id): JsonResponse
    {
        return $this->modelList(Content::published()->post()->ofCategory($id)->get());
    }

    /**
     * show
     * Display the specified resource by ID
     * @param  int $id
     * @return Response
     * @throws UserException
     */
    public function show($id): JsonResponse
    {
        $content = Content::find($id);
        if (empty($content)) {
            throw new UserException("contentNotFound");
        }
        if ($content->status->name != 'published') {
            throw new ModelNotFoundException("Can't find published content!");
        }
        return response()->json([
            'success' => true,
            'data' => (new ContentEntity($content))->getFrontendData(['frontend'])
        ]);
    }

    /**
     * showByUrl
     * Display the specified resource by URL
     * @param Request $request
     * @return JsonResponse
     * @throws UserException
     */
    public function showByUrl(Request $request): JsonResponse
    {
        $url = $request->get('url');
        $contentId = -1;
        if (!empty($url)) {
            $found = Content::findByDescription('url_description_id', [$url])->first();
            if ($found) {
                $contentId = $found->id;
            }
        }
        return $this->show($contentId);
    }

    /**
     * modelList
     * Generate response structure
     * @param type $models
     * @return type
     */
    private function modelList($models): JsonResponse
    {
        $categories = Taxonomy::find(Config::get('taxonomies.content_category'))->getChildren();
        return response()->json([
            'success' => true,
            'data' => ContentEntity::getCollection($models, ['frontend']),
            'categories' => $categories->keyBy('id')->map(function ($item) {
                return $item->name;
            })
        ]);
    }

}
