<?php

namespace App\Http\Controllers\Admin;

use App\ContentMedia;
use App\Http\Controllers\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylersmedia\Manipulators\FileSetter;

/**
 * @resource Admin/ContentMediaController
 */
class ContentMediaController extends ResourceController
{

    /**
     * index
     * List content images
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $files = File::doesntHave('galleryItem')->get(); // filter to content images
        return response()->json(['success' => true, 'data' => FileEntity::getCollection($files)]);
    }

    /**
     * store
     * Store a newly created image
     * @param  Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $content_id = $request->get('content_id');
        $file = File::findOrFail($request->get('file_id'));
        $contentMedia = $this->setContentMedia($content_id, $file);
        return response()->json(['success' => true, 'data' => $contentMedia->attributesToArray()]);
    }

    /**
     * destroy
     * Remove the specified image
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        // @TODO check relations before delete
        $contentMedia = ContentMedia::findOrFail($id);
        return response()->json([
            'success' => (bool)$contentMedia->delete(),
            'data' => ContentMedia::withTrashed()->findOrFail($id)->attributesToArray()
        ]);
    }

    /**
     * upload
     * Upload a new image file
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    public function upload(Request $request): JsonResponse
    {
        $file = (new FileSetter($request->toArray()))->setBySymfonyFile($request->file('file'));
        $content_id = $request->get('content_id');
        if ($content_id) {
            $this->setContentMedia($content_id, $file);
        }
        return response()->json(['success' => true, 'data' => (new FileEntity($file))->getFrontendData()]);
    }

    /**
     * setContentMedia
     * Set content media
     * @param type $content_id
     * @param File $file
     * @return ContentMedia
     * @throws \Throwable
     */
    private function setContentMedia($content_id, File $file): ContentMedia
    {
        $contentMedia = ContentMedia::withTrashed()
            ->where('content_id', '=', $content_id)
            ->where('mediable_type', '=', File::class)
            ->where('mediable_id', '=', $file->id)
            ->get()->first();
        if (!$contentMedia) {
            $contentMedia = new ContentMedia();
            $contentMedia->content_id = $content_id;
            $contentMedia->mediable_type = File::class;
            $contentMedia->mediable_id = $file->id;
            $contentMedia->saveOrFail();
        } else {
            $contentMedia->restore();
        }
        return $contentMedia;
    }

}
